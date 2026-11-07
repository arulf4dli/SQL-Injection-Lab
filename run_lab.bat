@echo off
title Server Lab SQL Injection
color 0A
echo ========================================================
echo        LAB PRAKTIK SQL INJECTION - TEMA CYBERPUNK
echo ========================================================
echo.
echo Menjalankan web server PHP lokal pada port 8080...
echo URL: http://localhost:8080
echo.
echo Tekan tombol Ctrl+C pada jendela terminal ini untuk menghentikan server.
echo ========================================================
echo.

start http://localhost:8080

if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S localhost:8080
) else (
    php -S localhost:8080
)

pause
