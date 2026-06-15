<?php
// Script para actualizar la tabla de configuración con los campos faltantes

// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si los campos ya existen
    $stmt = $pdo->query("DESCRIBE configuracion");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $campos_a_agregar = [
        'capacidad_dentro_mediodia' => 'INT NOT NULL DEFAULT 30',
        'capacidad_fuera_mediodia' => 'INT NOT NULL DEFAULT 20',
        'capacidad_dentro_noche' => 'INT NOT NULL DEFAULT 30',
        'capacidad_fuera_noche' => 'INT NOT NULL DEFAULT 20'
    ];
    
    $campos_agregados = [];
    
    foreach ($campos_a_agregar as $campo => $definicion) {
        if (!in_array($campo, $columnas)) {
            $sql = "ALTER TABLE configuracion ADD COLUMN $campo $definicion";
            $pdo->exec($sql);
            $campos_agregados[] = $campo;
        }
    }
    
    // Actualizar los valores por defecto
    if (!empty($campos_agregados)) {
        $sql = "UPDATE configuracion SET 
                capacidad_dentro_mediodia = 30,
                capacidad_fuera_mediodia = 20,
                capacidad_dentro_noche = 30,
                capacidad_fuera_noche = 20
                WHERE id = 1";
        $pdo->exec($sql);
    }
    
    echo "<h1>¡Tabla de configuración actualizada correctamente!</h1>";
    
    if (empty($campos_agregados)) {
        echo "<p>No se han agregado nuevos campos. La tabla ya estaba actualizada.</p>";
    } else {
        echo "<p>Se han agregado los siguientes campos:</p>";
        echo "<ul>";
        foreach ($campos_agregados as $campo) {
            echo "<li>" . htmlspecialchars($campo) . "</li>";
        }
        echo "</ul>";
    }
    
    // Mostrar la estructura actual de la tabla
    $stmt = $pdo->query("DESCRIBE configuracion");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estructura actual de la tabla 'configuracion':</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th></tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($columna['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($columna['Default'] ?? '') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<p><a href='admin/configuracion.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Ir a la página de configuración</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>No se pudo actualizar la tabla de configuración: " . $e->getMessage() . "</p>";
}
?>
