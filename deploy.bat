@echo off
echo Deploying to Larger Than Life Comics...
cd /d "C:\CODING\LARGER THEN LIFE MOCKUP"

REM Start SSH agent and add key
echo Starting SSH agent...
FOR /f "tokens=*" %%i IN ('ssh-agent -s') DO %%i

REM Auto-deploy
git add -A
git commit -m "Auto-deploy: %date% %time%"
git push siteground master

echo.
echo Deployment complete!
pause