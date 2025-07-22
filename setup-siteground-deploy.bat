@echo off
echo Setting up SiteGround Git deployment...
echo.

REM First, let's update the remote URL (in case it changed)
git remote remove siteground 2>nul
git remote add siteground ssh://u2330-snxbjnoydmhn@gvam1220.siteground.biz:18765/home/customer/www/largerthanlifecomics.com/public_html/

echo.
echo Git remote configured!
echo.
echo Now you need to:
echo 1. Make sure your SSH private key is in C:\Users\%USERNAME%\.ssh\
echo 2. Name it 'id_rsa' (or 'id_ed25519' if it's an ED25519 key)
echo 3. Then run: git push siteground master --force
echo.
echo If you have your private key file ready, you can also use:
echo git push siteground master --force
echo.
pause