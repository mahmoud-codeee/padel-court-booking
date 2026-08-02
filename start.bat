@echo off
title Padel Booking - Launcher
echo Starting Padel Booking project...
echo.

REM Start MySQL (standalone instance on port 3307) if it isn't already running,
REM and give it a few seconds to accept connections before the backend starts.
echo Starting MySQL...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\start-mysql.ps1"
powershell -NoProfile -Command "Start-Sleep -Seconds 5"
echo.

REM Start Laravel backend in its own window
start "Backend (Laravel - php artisan serve)" cmd /k "cd /d %~dp0backend && php artisan serve"

REM Free port 5173 if a leftover process from a previous run is still
REM holding it, so the frontend consistently starts on 5173 instead of
REM Vite jumping to the next free port. Only kills the exact PID bound
REM to that port - never a broad node.exe sweep.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\free-frontend-port.ps1"
echo.

REM Start Vite frontend in its own window
start "Frontend (Vite - npm run dev)" cmd /k "cd /d %~dp0frontend && npm run dev"

echo Both servers are starting in separate windows.
echo Give them a few seconds, then open http://localhost:5173 in your browser.
echo (If port 5173 is busy, check the Frontend window for the actual port it used.)
echo.
pause
