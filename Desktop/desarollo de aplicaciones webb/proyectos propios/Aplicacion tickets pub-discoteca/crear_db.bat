@echo off
echo Creando base de datos tikets_db...
cd C:\xampp\mysql\bin
mysql -u root -e "DROP DATABASE IF EXISTS tikets_db;"
mysql -u root -e "CREATE DATABASE tikets_db;"
mysql -u root tikets_db < "c:\Users\danie\OneDrive\Documentos\NetBeansProjects\tikets\configuracion_db.sql"
echo Base de datos creada correctamente.
pause
