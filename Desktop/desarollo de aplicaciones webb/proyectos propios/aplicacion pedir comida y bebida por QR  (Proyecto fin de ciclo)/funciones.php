<?php
require_once "bd.php";

// Función para cargar todas las categorías
function cargarCategorias($conexion) {
    $sql = "SELECT * FROM Categorias ORDER BY Nombre ASC";
    $resultado = $conexion->query($sql);
    $categorias = [];
    while ($cat = $resultado->fetch_assoc()) {
        $categorias[] = $cat;
    }
    return $categorias;
}

// Función para cargar todos los empleados
function cargarEmpleados($conexion) {
    $sql = "SELECT * FROM Empleados ORDER BY Nombre ASC";
    $resultado = $conexion->query($sql);
    $empleados = [];
    while ($emp = $resultado->fetch_assoc()) {
        $empleados[] = $emp;
    }
    return $empleados;
}

// Función para cargar todos los productos
function cargarProductos($conexion) {
    $sql = "SELECT p.*, c.Nombre AS NombreCategoria FROM Productos p LEFT JOIN Categorias c ON p.codCategoria = c.codCategoria ORDER BY p.Nombre ASC";
    $resultado = $conexion->query($sql);
    $productos = [];
    while ($prod = $resultado->fetch_assoc()) {
        $productos[] = $prod;
    }
    return $productos;
}
