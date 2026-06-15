<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>eliminar</title>
    </head>
    <body>
        <?php
            include("conexion.php");//Hacemos la conexión a la base de datos

            $codigoMovil=$_REQUEST["codigoMovil"]; //Guardamos la clave primaria de la tabla la cual ha pinchado el cliente

            $consulta="DELETE FROM moviles WHERE codigoMovil = '$codigoMovil'"; //Hacemos la consulta para borrar ese registro

            $resultado=mysqli_query($conexion,$consulta) or die("Consulta incorrecta");//Ejecutamos la consulta

            header("Location:index.php");	//Volvemos a la pagina de inicio
        ?>
    </body>
</html>
