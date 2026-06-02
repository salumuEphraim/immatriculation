# OCR plate microservice

Microservice FastAPI used by Laravel to read license plates with PaddleOCR, then optionally validate/correct the result with OpenAI vision.

## Setup

```powershell
cd C:\laragon\www\immatriculation\ocr_service
python -m venv .venv
.\.venv\Scripts\pip install -r requirements.txt
copy .env.example .env
```

Set `OPENAI_API_KEY` in `ocr_service\.env` if you want OpenAI refinement.

## Run

```powershell
.\.venv\Scripts\uvicorn app:app --host 127.0.0.1 --port 8010
```

Laravel calls:

- `GET http://127.0.0.1:8010/health`
- `POST http://127.0.0.1:8010/scan-plaque` with multipart field `file`

## Render deployment

On Render, deploy this folder as a separate Python web service named for example `roadshield-ocr`.
Set the Laravel service variable `OCR_PYTHON_URL` to the public URL of that service, for example:

- `https://roadshield-ocr.onrender.com/scan-plaque`
- `https://roadshield-ocr.onrender.com/health`
