<?php 
    require 'sesiones.php';
    require_once 'bd.php';
    comprobar_sesion();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Modificar Guardar Categoria</title>        
    </head>
    <body>
        <?php 
        require 'cabeceraAdmin.php';
        
        try {
            // Establecemos la conexión con la base de datos
            $res = leer_config(dirname(__FILE__)."/configuracion.xml", dirname(__FILE__)."/configuracion.xsd");
            $bd = new PDO($res[0], $res[1], $res[2]);

            $bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Obtengo las variables de la categoría
            $CodCat = $_REQUEST['CodCat'];
            $Nombre = $_REQUEST['Nombre'];
            $Descripcion = $_REQUEST['Descripcion'];

            $consulta = "UPDATE CATEGORIAS SET Nombre = :Nombre, Descripcion = :Descripcion WHERE CodCat = :CodCat";

            $stmt = $bd->prepare($consulta);

            $stmt->bindParam(':Nombre', $Nombre, PDO::PARAM_STR);
            $stmt->bindParam(':Descripcion', $Descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':CodCat', $CodCat, PDO::PARAM_INT);

            $resul = $stmt->execute();

            if ($resul) {
                echo('<p>Se ha actualizado correctamente la categoría.</p>');
            } else {
                echo('<p>No se ha podido guardar la modificación realizada.</p>');
            }

        } catch (PDOException $e) {
            // Si ocurre un error, lo mostramos
            echo('<p>Error al actualizar la categoría: ' . $e->getMessage() . '</p>');
        }
        ?>   
    </body>
</html>
