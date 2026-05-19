@echo off
:: ──────────────────────────────────────────────────────────────────────────
:: Droguerie POS — Lanceur (Impression silencieuse activée)
:: Lance Microsoft Edge avec --kiosk-printing pour imprimer sans dialogue.
:: ──────────────────────────────────────────────────────────────────────────
set EDGE="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
set APP_URL=http://127.0.0.1:8000

echo Démarrage du serveur POS Droguerie S...
:: Start artisan serve with OPcache enabled (prevents 2-minute cold-start delay).
:: OPcache caches compiled PHP bytecode across requests — without it, PHP
:: re-parses ~8,000 vendor files from disk on every cold request.
start "POS Server" cmd /k "php -c opcache-dev.ini artisan serve --host=127.0.0.1 --port=8000"

:: Give the server 3 seconds to bind before opening the browser.
timeout /t 3 /nobreak > nul

echo Ouverture du navigateur...
start "" %EDGE% ^
    --kiosk-printing ^
    --no-first-run ^
    --disable-infobars ^
    --no-default-browser-check ^
    %APP_URL%
