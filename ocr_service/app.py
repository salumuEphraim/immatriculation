import base64
import json
import os
import re
import tempfile
from functools import lru_cache
from pathlib import Path
from typing import Any

import cv2
import httpx
from dotenv import load_dotenv
from fastapi import FastAPI, File, HTTPException, Query, UploadFile
from fastapi.responses import JSONResponse
from PIL import Image

load_dotenv()

app = FastAPI(title="Plate OCR Service", version="1.0.0")

PLATE_PATTERN = re.compile(r"[A-Z0-9]{3,12}")


def normalize_plate(value: str) -> str:
    return re.sub(r"[^A-Z0-9]", "", value.upper())


def score_candidate(text: str, confidence: float) -> float:
    normalized = normalize_plate(text)
    length_score = 1.0 if 6 <= len(normalized) <= 10 else 0.65
    digit_letter_mix = 1.0 if re.search(r"\d", normalized) and re.search(r"[A-Z]", normalized) else 0.75
    return round(confidence * length_score * digit_letter_mix, 4)


def extract_candidates(texts: list[dict[str, Any]]) -> list[dict[str, Any]]:
    candidates: dict[str, dict[str, Any]] = {}

    for item in texts:
        raw_text = str(item.get("text") or "")
        confidence = float(item.get("confidence") or 0)
        normalized = normalize_plate(raw_text)

        values = [normalized]
        values.extend(PLATE_PATTERN.findall(normalized))

        for value in values:
            if len(value) < 3:
                continue
            current = candidates.get(value)
            item_score = score_candidate(value, confidence)
            if current is None or item_score > current["score"]:
                candidates[value] = {
                    "plate": value,
                    "confidence": round(confidence, 4),
                    "score": item_score,
                    "source_text": raw_text,
                }

    return sorted(candidates.values(), key=lambda c: c["score"], reverse=True)


def preprocess_image(source: Path) -> Path:
    image = cv2.imread(str(source))
    if image is None:
        raise HTTPException(status_code=422, detail="Image illisible.")

    height, width = image.shape[:2]
    max_side = max(height, width)
    if max_side < 1400:
        scale = 1400 / max_side
        image = cv2.resize(image, None, fx=scale, fy=scale, interpolation=cv2.INTER_CUBIC)

    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    denoised = cv2.fastNlMeansDenoising(gray, None, 12, 7, 21)
    sharpen_kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (3, 3))
    enhanced = cv2.morphologyEx(denoised, cv2.MORPH_TOPHAT, sharpen_kernel)
    enhanced = cv2.addWeighted(denoised, 1.45, enhanced, 0.55, 0)
    enhanced = cv2.equalizeHist(enhanced)

    target = source.with_name(f"{source.stem}_processed.jpg")
    cv2.imwrite(str(target), enhanced, [int(cv2.IMWRITE_JPEG_QUALITY), 95])
    return target


@lru_cache(maxsize=1)
def get_paddle_ocr():
    from paddleocr import PaddleOCR

    language = os.getenv("OCR_LANGUAGE", "en")
    use_angle_cls = os.getenv("OCR_USE_ANGLE_CLS", "true").lower() in {"1", "true", "yes", "on"}
    return PaddleOCR(use_angle_cls=use_angle_cls, lang=language, show_log=False)


def run_paddle(image_path: Path) -> dict[str, Any]:
    ocr = get_paddle_ocr()
    result = ocr.ocr(str(image_path), cls=True)
    lines: list[dict[str, Any]] = []

    for page in result or []:
        for line in page or []:
            if not line or len(line) < 2:
                continue
            payload = line[1]
            if not payload or len(payload) < 2:
                continue
            lines.append({
                "text": str(payload[0]),
                "confidence": float(payload[1] or 0),
            })

    candidates = extract_candidates(lines)
    best = candidates[0] if candidates else None

    return {
        "success": best is not None,
        "plate": best["plate"] if best else "",
        "confidence": best["confidence"] if best else 0,
        "method": "paddleocr",
        "candidates": candidates,
        "raw_text": " ".join(line["text"] for line in lines).strip(),
        "raw_lines": lines,
    }


async def refine_with_openai(image_path: Path, paddle_result: dict[str, Any]) -> dict[str, Any] | None:
    api_key = os.getenv("OPENAI_API_KEY", "").strip()
    if not api_key:
        return None

    model = os.getenv("OPENAI_MODEL", "gpt-4o-mini")
    timeout = float(os.getenv("OPENAI_TIMEOUT", "25"))

    image_bytes = image_path.read_bytes()
    image_type = "image/jpeg"
    try:
        with Image.open(image_path) as image:
            if image.format and image.format.lower() == "png":
                image_type = "image/png"
    except Exception:
        pass

    data_url = f"data:{image_type};base64,{base64.b64encode(image_bytes).decode('ascii')}"
    prompt = {
        "task": "Read the license plate in the image.",
        "country_context": "Democratic Republic of Congo vehicle registration plates.",
        "paddleocr_best_guess": paddle_result.get("plate", ""),
        "paddleocr_candidates": paddle_result.get("candidates", [])[:5],
        "rules": [
            "Return only JSON.",
            "plate must contain uppercase letters and digits only, no spaces or punctuation.",
            "If the plate is not readable, set plate to an empty string and confidence below 0.5.",
            "Pay special attention to B/8, O/0, I/1, S/5 and Z/2.",
        ],
    }

    body = {
        "model": model,
        "input": [
            {
                "role": "user",
                "content": [
                    {"type": "input_text", "text": json.dumps(prompt, ensure_ascii=True)},
                    {"type": "input_image", "image_url": data_url, "detail": "high"},
                ],
            }
        ],
        "text": {
            "format": {
                "type": "json_schema",
                "name": "plate_ocr_result",
                "schema": {
                    "type": "object",
                    "additionalProperties": False,
                    "properties": {
                        "plate": {"type": "string"},
                        "confidence": {"type": "number", "minimum": 0, "maximum": 1},
                        "reason": {"type": "string"},
                    },
                    "required": ["plate", "confidence", "reason"],
                },
                "strict": True,
            }
        },
    }

    async with httpx.AsyncClient(timeout=timeout) as client:
        response = await client.post(
            "https://api.openai.com/v1/responses",
            headers={"Authorization": f"Bearer {api_key}", "Content-Type": "application/json"},
            json=body,
        )

    response.raise_for_status()
    data = response.json()
    output_text = data.get("output_text")

    if not output_text:
        parts: list[str] = []
        for item in data.get("output", []):
            for content in item.get("content", []):
                if content.get("type") in {"output_text", "text"}:
                    parts.append(str(content.get("text") or ""))
        output_text = "".join(parts)

    parsed = json.loads(output_text or "{}")
    plate = normalize_plate(str(parsed.get("plate") or ""))
    confidence = float(parsed.get("confidence") or 0)

    return {
        "success": bool(plate) and confidence >= 0.5,
        "plate": plate,
        "confidence": max(0, min(confidence, 1)),
        "method": "openai_vision",
        "reason": str(parsed.get("reason") or ""),
    }


def merge_results(paddle_result: dict[str, Any], openai_result: dict[str, Any] | None) -> dict[str, Any]:
    if not openai_result:
        return paddle_result

    paddle_plate = str(paddle_result.get("plate") or "")
    openai_plate = str(openai_result.get("plate") or "")
    paddle_confidence = float(paddle_result.get("confidence") or 0)
    openai_confidence = float(openai_result.get("confidence") or 0)

    if openai_plate and openai_plate == paddle_plate:
        return {
            **paddle_result,
            "success": True,
            "plate": openai_plate,
            "confidence": round(max(paddle_confidence, openai_confidence), 4),
            "method": "paddleocr_openai_confirmed",
            "openai": openai_result,
        }

    if openai_plate and openai_confidence >= max(0.72, paddle_confidence + 0.12):
        return {
            **paddle_result,
            "success": True,
            "plate": openai_plate,
            "confidence": round(openai_confidence, 4),
            "method": "openai_refined",
            "openai": openai_result,
        }

    return {
        **paddle_result,
        "openai": openai_result,
    }


@app.get("/health")
def health() -> dict[str, Any]:
    return {
        "status": "ok",
        "service": "ocr_service",
        "paddleocr_loaded": get_paddle_ocr.cache_info().currsize > 0,
        "openai_configured": bool(os.getenv("OPENAI_API_KEY", "").strip()),
        "openai_model": os.getenv("OPENAI_MODEL", "gpt-4o-mini"),
    }


@app.post("/warmup")
def warmup() -> dict[str, Any]:
    get_paddle_ocr()

    return {
        "status": "ok",
        "message": "PaddleOCR loaded",
        "paddleocr_loaded": True,
        "openai_configured": bool(os.getenv("OPENAI_API_KEY", "").strip()),
    }


@app.post("/scan-plaque")
async def scan_plaque(
    file: UploadFile = File(...),
    use_openai: bool = Query(True),
) -> JSONResponse:
    suffix = Path(file.filename or "plaque.jpg").suffix.lower()
    if suffix not in {".jpg", ".jpeg", ".png", ".bmp", ".webp"}:
        raise HTTPException(status_code=415, detail="Format image non supporte.")

    with tempfile.TemporaryDirectory(prefix="plate_ocr_") as tmp_dir:
        original = Path(tmp_dir) / f"upload{suffix}"
        original.write_bytes(await file.read())
        processed = preprocess_image(original)

        paddle_result = run_paddle(processed)

        openai_result = None
        openai_error = None
        if use_openai:
            try:
                openai_result = await refine_with_openai(original, paddle_result)
            except Exception as exc:
                openai_error = str(exc)

        final = merge_results(paddle_result, openai_result)
        if openai_error:
            final["openai_error"] = openai_error

        if not final.get("success"):
            return JSONResponse(
                status_code=422,
                content={
                    "status": "error",
                    "message": "Plaque non detectee.",
                    **final,
                },
            )

        plate = normalize_plate(str(final.get("plate") or ""))
        return JSONResponse(
            content={
                "status": "success",
                "plaque": plate,
                "result": plate,
                **final,
            }
        )
