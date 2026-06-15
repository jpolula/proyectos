<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>modificar</title>
    </head>
    <body>
    <?php
        error_reporting(0);
        // me conecto a la BD
        include ('conexion.php');

        // Obtengo las variables
        $codigoMovil = $_REQUEST["codigoMovil"];

        $consulta = "SELECT * FROM moviles WHERE codigoMovil='".$codigoMovil."';";
        $resultado=mysqli_query($conexion,$consulta) or die("consulta incorrecta");
        $fila = mysqli_fetch_array($resultado); //Guardo la consulta en un array asociativo
    ?>
        <form action="modificar_guardar.php">
    
        </form>
    </body>
</html>