<?php
// Script para crear la columna fecha_creacion en la tabla reservas

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Creando columna fecha_creacion</h1>";
    
    // Ejecutar el comando SQL para crear la columna
    $pdo->exec("ALTER TABLE reservas ADD COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP");
    
    echo "<p style='color:green;font-weight:bold;'>✓ Columna fecha_creacion creada correctamente.</p>";
    
    // Actualizar todas las reservas existentes
    $stmt = $pdo->exec("UPDATE reservas SET fecha_creacion = NOW() WHERE fecha_creacion IS NULL");
    echo "<p style='color:green;'>✓ Se actualizaron todas las reservas existentes.</p>";
    
    // Verificar que la columna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'fecha_creacion'");
    if ($stmt->rowCount() > 0) {
        $columna = $stmt->fetch();
        echo "<p>Detalles de la columna:</p>";
        echo "<ul>";
        echo "<li><strong>Nombre:</strong> " . $columna['Field'] . "</li>";
        echo "<li><strong>Tipo:</strong> " . $columna['Type'] . "</li>";
        echo "<li><strong>Nulo:</strong> " . $columna['Null'] . "</li>";
        echo "<li><strong>Predeterminado:</strong> " . $columna['Default'] . "</li>";
        echo "</ul>";
    }
    
    // Probar una consulta
    $stmt = $pdo->query("SELECT id, fecha_creacion FROM reservas LIMIT 5");
    $reservas = $stmt->fetchAll();
    
    if (count($reservas) > 0) {
        echo "<h2>Primeras 5 reservas con fecha_creacion:</h2>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Fecha Creación</th></tr>";
        
        foreach ($reservas as $reserva) {
            echo "<tr>";
            echo "<td>" . $reserva['id'] . "</td>";
            echo "<td>" . $reserva['fecha_creacion'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay reservas en la base de datos.</p>";
    }
    
    echo "<p><a href='admin/reservas.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Volver al panel de administración</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Error</h2>";
    echo "<p>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Si el error es que la columna ya existe, mostrar un mensaje más amigable
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "<p>La columna fecha_creacion ya existe en la tabla reservas.</p>";
        echo "<p><a href='admin/reservas.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Volver al panel de administración</a></p>";
    }
}
?>
