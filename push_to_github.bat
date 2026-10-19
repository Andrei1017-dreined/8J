@echo off
title 8J IMS - Push to GitHub
cd /d "%~dp0"
echo ========================================================
echo   8J Cellphone Accessories IMS - Push to GitHub
echo ========================================================
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
    echo   Push failed or requires GitHub login.
    echo ========================================================
)
echo.
pause
