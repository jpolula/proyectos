<?php
// Script para limpiar archivos de prueba y temporales

// Lista de archivos a eliminar
$archivos = [
    'test_calendario.php',
    'test_consulta.php',
    'test_db.php',
    'insertar_datos_prueba.php',
    'prueba.php',
    'temp.php',
    'ejemplo.php'
];

// Contador de archivos eliminados
$eliminados = 0;

echo "<h1>Limpieza de archivos temporales</h1>";
echo "<ul>";

// Eliminar cada archivo si existe
foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        if (unlink($archivo)) {
            echo "<li style='color: green'>✓ Archivo eliminado: $archivo</li>";
            $eliminados++;
        } else {
            echo "<li style='color: red'>✗ No se pudo eliminar: $archivo</li>";
        }
    } else {
        echo "<li style='color: gray'>- Archivo no encontrado: $archivo</li>";
    }
}

echo "</ul>";
echo "<p>Total de archivos eliminados: $eliminados</p>";
echo "<p><a href='index.php' style='padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Volver al inicio</a></p>";
?>
