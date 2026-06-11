@echo off
setlocal
echo ======================================================
echo    WHITE BOX TESTING AUTOMATION - PROJECT PKL
echo ======================================================
echo.
echo [1/2] Membersihkan cache aplikasi...
php artisan config:clear --quiet

echo [2/2] Menjalankan Test Case (White Box Path Analysis)...
echo Database: db_absensi (Database Asli - Transaction Mode)
echo.

:: Menjalankan Pest dengan opsi compact
php artisan test --compact

echo.
echo ======================================================
echo TOTAL TEST CASE: 325+ Skenario (Matrix & Path Analysis)
echo ======================================================
pause
