@echo off
cd /d C:\laragon\www\immatriculation\ocr_service

if not exist ".venv\Scripts\python.exe" (
    echo Creation de l'environnement Python...
    "C:\laragon\bin\python\python-3.10\python.exe" -m venv .venv
)

echo Installation / verification des dependances...
".venv\Scripts\python.exe" -m pip install -r requirements.txt

if not exist ".env" (
    copy ".env.example" ".env" >nul
    echo Fichier .env cree. Ajoutez OPENAI_API_KEY si necessaire.
)

echo.
echo Microservice OCR demarre sur http://127.0.0.1:8010
echo Gardez cette fenetre ouverte. Pour arreter: Ctrl+C
echo.
".venv\Scripts\python.exe" -m uvicorn app:app --host 127.0.0.1 --port 8010

