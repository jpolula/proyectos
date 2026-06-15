@echo off
echo Configurando tarea programada para monitoreo del sistema...

:: Obtener la ruta completa de PHP y el script
set PHP_PATH=c:\xampp\php\php.exe
set SCRIPT_PATH=%~dp0monitor.php

:: Crear la tarea programada (se ejecutará cada hora)
schtasks /create /tn "MonitoreoSistemaRed" /tr "%PHP_PATH% %SCRIPT_PATH%" /sc HOURLY /mo 1 /ru SYSTEM /f

if %errorlevel% equ 0 (
    echo.
    echo Tarea programada creada exitosamente.
    echo La tarea "MonitoreoSistemaRed" se ejecutara cada hora.
    echo Para ver el estado de la tarea, ejecute: schtasks /query /tn "MonitoreoSistemaRed"
) else (
    echo.
    echo Error al crear la tarea programada.
    echo Por favor, ejecute este script como administrador.
)

pause
