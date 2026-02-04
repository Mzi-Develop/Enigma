@echo off
title Enigma Machine Simulator
color 0A
cls

echo ============================================
echo      ENIGMA MACHINE SIMULATOR v1.0
echo ============================================
echo.

REM
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo ERROR: PHP is not installed or not in PATH!
    echo Please install PHP from https://www.php.net
    echo.
    pause
    exit /b 1
)

REM
php -r "if (version_compare(PHP_VERSION, '7.0.0') < 0) { echo 'PHP 7.0+ required'; exit(1); }"
if %ERRORLEVEL% neq 0 (
    echo ERROR: PHP 7.0 or higher is required!
    echo.
    pause
    exit /b 1
)

REM
echo Starting Enigma Machine...
echo.
php enigma.php

REM
if %ERRORLEVEL% neq 0 (
    echo.
    echo Program terminated with error code %ERRORLEVEL%
    pause
)

exit /b 0