<?php
// Script para arreglar definitivamente el problema de fecha_creacion
// Este script:
// 1. Crea la columna fecha_creacion si no existe
// 2. Actualiza todas las reservas existentes con la fecha actual
// 3. Muestra un mensaje de éxito

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Arreglando columna fecha_creacion</h1>";
    
    // Verificar si la columna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'fecha_creacion'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if (!$columna_existe) {
        echo "<p>La columna fecha_creacion no existe. Creándola...</p>";
        
        // Crear la columna
        $pdo->exec("ALTER TABLE reservas ADD COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "<p style='color:green'>✓ Columna creada correctamente.</p>";
    } else {
        echo "<p>La columna fecha_creacion ya existe.</p>";
    }
    
    // Actualizar todas las filas que no tienen fecha_creacion
    $pdo->exec("UPDATE reservas SET fecha_creacion = NOW() WHERE fecha_creacion IS NULL");
    echo "<p style='color:green'>✓ Registros actualizados correctamente.</p>";
    
    // Verificar que la columna tenga el valor predeterminado CURRENT_TIMESTAMP
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'fecha_creacion'");
    $columna = $stmt->fetch();
    
    if ($columna['Default'] != 'CURRENT_TIMESTAMP' && $columna['Default'] !== 'current_timestamp()') {
        echo "<p>Configurando el valor predeterminado de la columna a CURRENT_TIMESTAMP...</p>";
        $pdo->exec("ALTER TABLE reservas MODIFY COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP");
        echo "<p style='color:green'>✓ Valor predeterminado configurado correctamente.</p>";
    } else {
        echo "<p style='color:green'>✓ La columna ya tiene el valor predeterminado correcto.</p>";
    }
    
    echo "<h2 style='color:green'>✓ Problema resuelto</h2>";
    echo "<p>La columna fecha_creacion ha sido creada y configurada correctamente.</p>";
    echo "<p><a href='admin/reservas.php' style='background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;'>Volver al panel de administración</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Error</h2>";
    echo "<p>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
