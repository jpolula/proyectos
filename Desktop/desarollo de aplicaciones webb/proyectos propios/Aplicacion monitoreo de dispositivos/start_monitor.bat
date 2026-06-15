@echo off
echo Iniciando el servicio de monitoreo...
start /B c:\xampp\php\php.exe daemon.php > monitor.log 2>&1
echo Servicio iniciado. Los logs se guardan en monitor.log
echo Para detener el servicio, ejecuta stop_monitor.bat
pause
