@echo off
title 8J IMS - Push to GitHub
cd /d "%~dp0"

:: Ensure Git is in PATH
set "PATH=%LOCALAPPDATA%\Programs\Git\cmd;%LOCALAPPDATA%\Programs\Git\bin;C:\Program Files\Git\cmd;C:\Program Files\Git\bin;%PATH%"

echo ========================================================
echo   8J Cellphone Accessories IMS - Push to GitHub
echo ========================================================
echo.
git --version
if %ERRORLEVEL% NEQ 0 (
    echo Git is not installed or not in PATH!
    pause
    exit /b
)

echo.
git status
echo.
echo Pushing commits to https://github.com/Andrei1017-dreined/8J.git ...
git push -u origin main
echo.
if %ERRORLEVEL% EQU 0 (
    echo ========================================================
    echo   SUCCESSFULLY PUSHED TO GITHUB!
    echo ========================================================
) else (
    echo ========================================================
    echo   Push failed or GitHub authentication was cancelled.
    echo ========================================================
)
echo.
pause
