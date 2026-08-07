@echo off
rem ============================================================
rem  Reverb WebSocket Server - Inicio en dev (Windows)
rem  Doble clic para abrir una ventana dedicada con el servidor.
rem  Requisito: `php` disponible en el PATH (Herd, Laragon, XAMPP).
rem  Puerto por defecto: 8080 (REVERB_PORT en .env)
rem ============================================================
title Reverb WebSocket Server (omnivision-system)
cd /d "%~dp0.."

if not exist artisan (
    echo [ERROR] No se encontro artisan. Ejecuta el script desde scripts/ del proyecto.
    pause
    exit /b 1
)

echo Iniciando Reverb en http://localhost:8080 ...
echo Para detenerlo: cerrar esta ventana (Ctrl+C).
echo.
php artisan reverb:start
pause
