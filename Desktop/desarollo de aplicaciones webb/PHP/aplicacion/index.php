<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>index</title>
    </head>
    <body>
        <?php
            error_reporting(0); //hacemos que en la pantalla no muestre ningun error

            include("conexion.php"); /*Nos conectamos a la base de datos. Al hacert esto guaradamos la variable*/ 

            $consulta="SELECT * FROM moviles;"; //guardamos la cadena para utilizarla en la consulta

            $resultado=mysqli_query($conexion,$consulta) or die("Consulta incorrecta"); //guardamos el resultado de la consulta

            $numFilas=mysqli_num_rows($resultado); //Guaramos el numero de filas que nos ha dado la consulta

            echo "<center>
                    <h1> 
                        Listado de moviles
                    </h1>
                 </center>
            ";

            echo "<table align=center>\n"; //creamos una tabla que este alineada al centro

            echo "
            <tr bgcolor=#ffffaa>\n  
                <th>codigoMovil</th>\n
                <th>marca</th>\n
                <th>modelo</th>\n
                <th>precio</th>\n
                <th>codigoCliente</th>\n
            </tr>\n";//Creamos una fila y 6 columnas para el encabezado de nuestra tabla.
            for($i=1; $i<=$n_filas; $i++) //ccada registro tendra una fila 
           {
                $fila = mysqli_fetch_array($resultado);
                echo "<tr>\n";
                echo "  <td>".$fila["codigoMovil"]."</td>\n";  /*nos dará un valor que guardaremos en una columna*/
                echo "  <td>".$fila["marca"]."</td>\n";
                echo "  <td>".$fila["modelo"]."</td>\n";
                echo "  <td>".$fila["precio"]."</td>\n";
                echo "  <td>".$fila["codigoCliente"]."</td>\n";
                echo "  <td><a href=modificar.php?id=".$fila['codigoMovil']."><img src=ico_modificar.png border=0></a>";
                //Creamos un enlace que se abre el archivo modificar en el que guardamos la clave primaria 
                echo "  <td><a href=eliminar.php?id=".$fila['codigoMovil']."><img src=ico_eliminar.png border=0></a>";
                //Creamos un enlace que se abre el archivo eliminar en el que guardamos la clave primaria para borrar el registro
                echo "</tr>\n";
            }
            echo "<tr>
                    <td colspan=6> <hr>";
            echo "<a href=anadir.php> anadir movil </a>";

            echo "</td></tr></table>";
        ?>

    </body>
</html>