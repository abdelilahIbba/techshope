@echo off
:: ──────────────────────────────────────────────────────────────────────────
:: Droguerie POS — Lanceur (Impression silencieuse activée)
:: Lance Microsoft Edge avec --kiosk-printing pour imprimer sans dialogue.
:: ──────────────────────────────────────────────────────────────────────────
set EDGE="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
set APP_URL=http://127.0.0.1:8000

echo Démarrage du POS Droguerie S...
start "" %EDGE% ^
    --kiosk-printing ^
    --no-first-run ^
    --disable-infobars ^
    --no-default-browser-check ^
    %APP_URL%
