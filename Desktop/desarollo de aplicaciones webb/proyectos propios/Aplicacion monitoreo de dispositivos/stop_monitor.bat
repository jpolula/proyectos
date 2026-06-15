@echo off
echo Deteniendo el servicio de monitoreo...
taskkill /F /IM php.exe /FI "WINDOWTITLE eq daemon.php"
echo Servicio detenido.
pause
