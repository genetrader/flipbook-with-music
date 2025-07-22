@echo off
echo Deploying to Larger Than Life Comics...
cd /d "C:\CODING\LARGER THEN LIFE MOCKUP"

REM Configure Git to handle line endings
git config core.autocrlf true

REM Auto-deploy
git add -A
git commit -m "Auto-deploy: %date% %time%"
git push siteground master 2>&1

echo.
echo Deployment complete!
echo Your website has been updated: https://largerthanlifecomics.com
pause