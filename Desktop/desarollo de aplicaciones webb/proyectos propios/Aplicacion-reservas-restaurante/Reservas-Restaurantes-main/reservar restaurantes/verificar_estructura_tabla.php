<?php
// Script para verificar la estructura de la tabla reservas

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Mostrar la estructura de la tabla reservas
    $stmt = $pdo->query("DESCRIBE reservas");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estructura de la tabla 'reservas'</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "<td>" . $columna['Default'] . "</td>";
        echo "<td>" . $columna['Extra'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Mostrar algunos registros de ejemplo
    $stmt = $pdo->query("SELECT * FROM reservas LIMIT 5");
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($reservas) > 0) {
        echo "<h2>Ejemplos de registros en la tabla 'reservas'</h2>";
        echo "<table border='1'>";
        
        // Encabezados de la tabla
        echo "<tr>";
        foreach (array_keys($reservas[0]) as $campo) {
            echo "<th>" . $campo . "</th>";
        }
        echo "</tr>";
        
        // Datos
        foreach ($reservas as $reserva) {
            echo "<tr>";
            foreach ($reserva as $valor) {
                echo "<td>" . htmlspecialchars($valor) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay registros en la tabla 'reservas'</p>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
