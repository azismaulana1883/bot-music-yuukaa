@echo off
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3000') do (
  taskkill /F /PID %%a >nul 2>&1
)
taskkill /F /IM node.exe >nul 2>&1
exit /b 0
