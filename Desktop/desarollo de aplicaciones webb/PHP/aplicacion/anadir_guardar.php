<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>anadir_guardar</title>
    </head>
    <body>
        <?php
            include("conexion.php"); //Nos conectammos a la base de datos
            //Guardamos los valores que quiere anadir el cliente
            $codigoMovil=$_REQUEST["codigoMovil"];
            $marca=$_REQUEST["marca"];
            $modelo=$_REQUEST["modelo"];
            $precio=$_REQUEST["precio"];
            $codigoCliente=$_REQUEST["codigoCliente"];
            $consulta="INSERT INTO moviles (codigoMovil,marca,modelo,precio,codigoCliente) VALUES ('$codigoMovil','$marca','$modelo','$precio','$codigoCliente');";//Guardamos la consulta para anadir un movil
            
            mysqli_query($conexion,$consulta); //Ejecutamos la consulta.

            header("Location:index.php"); //Volvemos a nuestra página principal
        ?>
    </body>
</html>