<?php
// Archivo para actualizar la estructura de la base de datos de forma simple
try {
    // Configuración de la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Desactivar el modo de emulación de consultas preparadas
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Verificar y agregar columnas si no existen
    $columnas_a_agregar = [
        'checkboxes_personalizados' => [
            'es_obligatorio' => ['tipo' => 'TINYINT(1) NOT NULL DEFAULT 0', 'despues' => 'activo'],
            'tiene_textarea' => ['tipo' => 'TINYINT(1) NOT NULL DEFAULT 0', 'despues' => 'es_obligatorio'],
            'placeholder_textarea' => ['tipo' => 'VARCHAR(255) DEFAULT NULL', 'despues' => 'tiene_textarea']
        ]
    ];
    
    $mensajes = [];
    
    foreach ($columnas_a_agregar as $tabla => $columnas) {
        foreach ($columnas as $nombre_col => $detalles) {
            try {
                // Verificar si la columna existe
                $stmt = $pdo->query("SHOW COLUMNS FROM `$tabla` LIKE '$nombre_col'");
                
                if ($stmt->rowCount() === 0) {
                    // La columna no existe, la creamos
                    $sql = "ALTER TABLE `$tabla` ADD COLUMN `$nombre_col` {$detalles['tipo']}";
                    if (isset($detalles['despues'])) {
                        $sql .= " AFTER `{$detalles['despues']}`";
                    }
                    
                    $pdo->exec($sql);
                    $mensajes[] = "✅ Columna '$nombre_col' añadida a la tabla '$tabla'.";
                } else {
                    $mensajes[] = "ℹ️ La columna '$nombre_col' ya existe en la tabla '$tabla'.";
                }
            } catch (PDOException $e) {
                $mensajes[] = "❌ Error al verificar/agregar la columna '$nombre_col' en la tabla '$tabla': " . $e->getMessage();
            }
        }
    }
    
    // Mostrar resultados
    echo "<h2>Resultado de la actualización:</h2>";
    echo "<ul>";
    foreach ($mensajes as $mensaje) {
        echo "<li>$mensaje</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='checkboxes_simple.php'>Volver a la gestión de checkboxes</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . print_r($e->getTraceAsString(), true) . "</pre>";
}
?>
