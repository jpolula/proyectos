<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>modificar_guardar</title>
</head>
<body>
    <?php
        include("conexion.php");//Nos conectamos a la base de datos
        //Guardamos los datos recogidos en el formulario
        $codigoMovil=$_REQUEST["codigoMovil"];
        $marca=$_REQUEST["marca"];
        $modelo=$_REQUEST["modelo"];
        $precio=$_REQUEST["precio"];
        $codigoCliente=$_REQUEST["codigoCliente"];
        $consulta="UPDATE movies SET marca='$marca', modelo='$modelo', precio='$precio', codigoCliente='$codigoCliente' WHERE codigoMovil='$codigoMovil';";
    ?>
</body>
</html>