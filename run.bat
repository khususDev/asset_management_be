@echo off

echo Menjalankan Laravel Backend...

start /B cmd /C "cd /d backend && php artisan serve"

echo Menjalankan Vue Frontend...

cd /d frontend
npm run dev