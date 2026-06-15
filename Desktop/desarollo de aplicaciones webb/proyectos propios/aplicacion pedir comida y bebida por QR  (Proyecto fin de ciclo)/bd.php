<?php
    // Archivo de conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "Restaurante");
    if ($conexion->connect_error) {
        die("Error de conexión a la base de datos: " . $conexion->connect_error);
    }
    $conexion->set_charset("utf8");
?>
