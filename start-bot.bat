@echo off
echo Menghentikan bot lama jika masih berjalan...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3000') do (
  taskkill /F /PID %%a >nul 2>&1
)
echo Memulai Discord Bot...
cd /d "%~dp0discord-bot"
call npm start
pause
