<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>conexion</title>
    </head>
    <body>
    <?php
        $conexion=mysqli_connect("localhost","root","","tienda") 
        //CReamos una variable en la que gusradaremos la conexión a la base de datos
        or die("No se puede conectar a la base de datos");
        echo "Conexión exitosa";
    ?>
    </body>
</html>
