<?php
    require "sesiones.php";
    require_once "bd.php";
    //comprobar_rol(["administrador"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta Restaurante</title>
    <link rel="stylesheet" href="css/AltaRest.css">
</head>
<body>
    <?php
        require 'CabeceraAdmin.php';
    ?>
    <form action="GuardarAltaRest.php" method="POST">
        <label for="correo">Correo:</label>
        <br>
        <input type="email" name="Correo" required>
        <br>
        <label for="clave">Contraseña:</label>
        <br>
        <input type="password" name="Clave" required>
        <br>
        <label for="pais">País:</label>
        <br>
        <input type="text" name="Pais" required>
        <br>
        <label for="cp">CP:</label>
        <br>
        <input type="number" name="CP" required>
        <br>
        <label for="ciudad">Ciudad:</label>
        <br>
        <input type="text" name="Ciudad" required>
        <br>
        
        <label for="direccion">Dirección:</label>
        <br>
        <textarea name="Direccion" rows="4" cols="50" required></textarea>
        <br>
        
        <label for="EsAdmin">¿Es Administrador?</label>
        <input type="checkbox" name="EsAdmin" value="1">
        <br>
        
        <input type="submit" value="Guardar Restaurante">
    </form>
</body>
</html>
