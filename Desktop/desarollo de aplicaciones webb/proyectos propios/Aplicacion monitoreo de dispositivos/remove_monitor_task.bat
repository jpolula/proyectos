@echo off
echo Eliminando tarea programada de monitoreo...

schtasks /delete /tn "MonitoreoSistemaRed" /f

if %errorlevel% equ 0 (
    echo.
    echo Tarea programada eliminada exitosamente.
) else (
    echo.
    echo Error al eliminar la tarea programada.
    echo Es posible que la tarea no exista o se requieran privilegios de administrador.
)

pause
