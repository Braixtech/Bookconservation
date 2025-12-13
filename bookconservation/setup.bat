@echo off
echo Setting up Arewa Conservation Platform...
echo.

echo 1. Starting XAMPP services...
cd C:\xampp
xampp-control.exe

echo 2. Creating database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS arewa_conservation_db;"

echo 3. Setting up project...
cd C:\xampp\htdocs\bookconservation
copy .env.example .env

echo 4. Setting permissions...
icacls storage /grant "Everyone":(OI)(CI)F
icacls bootstrap/cache /grant "Everyone":(OI)(CI)F

echo 5. Opening application...
start http://localhost/bookconservation/public/

echo Setup complete! Check browser.
pause