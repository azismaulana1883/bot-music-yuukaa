@echo off
echo Menghentikan Discord Bot (Port 3000)...
set "FOUND="
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3000') do (
  taskkill /F /PID %%a >nul 2>&1
  set FOUND=1
)
if not defined FOUND (
  echo Bot tidak sedang berjalan (tidak ada process di Port 3000).
) else (
  echo Bot berhasil dihentikan.
)
pause
exit /b 0
