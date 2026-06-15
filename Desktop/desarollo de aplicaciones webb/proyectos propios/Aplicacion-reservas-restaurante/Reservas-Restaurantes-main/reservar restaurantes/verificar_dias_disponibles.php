<?php
// Script para verificar los días disponibles en la base de datos
header('Content-Type: text/html; charset=utf-8');

// Configuración de la base de datos
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
    
    echo "<h1>Verificación de Días Disponibles - Mayo 2025</h1>";
    
    // 1. Consultar días disponibles (DISTINCT fecha)
    $stmt = $pdo->query("
        SELECT DISTINCT fecha 
        FROM dias_disponibles 
        WHERE disponible = 1 
        AND fecha >= '2025-05-01' 
        AND fecha <= '2025-05-31'
        ORDER BY fecha
    ");
    $dias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Días disponibles (DISTINCT fecha):</h2>";
    echo "<p>Total: " . count($dias) . " días</p>";
    echo "<ul>";
    foreach ($dias as $dia) {
        echo "<li>$dia</li>";
    }
    echo "</ul>";
    
    // 2. Consultar días disponibles por zona y turno
    echo "<h2>Días disponibles por zona y turno:</h2>";
    
    // Obtener turnos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
    $turnos = $stmt->fetchAll();
    
    foreach (['dentro', 'fuera'] as $zona) {
        echo "<h3>Zona: " . ($zona == 'dentro' ? 'Interior' : 'Terraza') . "</h3>";
        
        foreach ($turnos as $turno) {
            echo "<h4>Turno: " . ($turno['nombre'] == 'mediodia' ? 'Mediodía' : 'Noche') . " (ID: {$turno['id']})</h4>";
            
            $stmt = $pdo->prepare("
                SELECT fecha 
                FROM dias_disponibles 
                WHERE disponible = 1 
                AND zona = ? 
                AND turno_id = ? 
                AND fecha >= '2025-05-01' 
                AND fecha <= '2025-05-31'
                ORDER BY fecha
            ");
            $stmt->execute([$zona, $turno['id']]);
            $dias_turno_zona = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>Total: " . count($dias_turno_zona) . " días</p>";
            echo "<ul>";
            foreach ($dias_turno_zona as $dia) {
                echo "<li>$dia</li>";
            }
            echo "</ul>";
        }
    }
    
    // 3. Verificar la estructura de la tabla
    echo "<h2>Estructura de la tabla dias_disponibles:</h2>";
    $stmt = $pdo->query("DESCRIBE dias_disponibles");
    $columnas = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>";
        foreach ($columna as $key => $value) {
            echo "<td>" . ($value === null ? 'NULL' : htmlspecialchars($value)) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Verificar los índices de la tabla
    echo "<h2>Índices de la tabla dias_disponibles:</h2>";
    $stmt = $pdo->query("SHOW INDEX FROM dias_disponibles");
    $indices = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Tabla</th><th>No duplicados</th><th>Nombre clave</th><th>Secuencia</th><th>Nombre columna</th></tr>";
    foreach ($indices as $indice) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($indice['Table']) . "</td>";
        echo "<td>" . htmlspecialchars($indice['Non_unique']) . "</td>";
        echo "<td>" . htmlspecialchars($indice['Key_name']) . "</td>";
        echo "<td>" . htmlspecialchars($indice['Seq_in_index']) . "</td>";
        echo "<td>" . htmlspecialchars($indice['Column_name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Se ha producido un error: " . $e->getMessage() . "</p>";
}
?>
