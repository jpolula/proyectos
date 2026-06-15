<?php
// Script para insertar días disponibles en la base de datos

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
    
    echo "<h1>Inserción de días disponibles</h1>";
    
    // 1. Verificar si la tabla dias_disponibles existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'dias_disponibles'");
    $tablaExiste = $stmt->rowCount() > 0;
    
    if (!$tablaExiste) {
        echo "<p>La tabla 'dias_disponibles' no existe. Creándola...</p>";
        
        // Crear la tabla
        $pdo->exec("
            CREATE TABLE dias_disponibles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fecha DATE NOT NULL,
                turno_id INT NOT NULL,
                zona VARCHAR(50) NOT NULL,
                disponible TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY fecha_turno_zona (fecha, turno_id, zona)
            )
        ");
        
        echo "<p>Tabla creada correctamente.</p>";
    }
    
    // 2. Verificar si existen los turnos
    $stmt = $pdo->query("SELECT * FROM turnos");
    $turnos = $stmt->fetchAll();
    
    if (count($turnos) == 0) {
        echo "<p>No hay turnos en la base de datos. Insertando turnos básicos...</p>";
        
        // Insertar turnos básicos
        $pdo->exec("
            INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
            ('mediodia', '13:00', '16:00'),
            ('noche', '20:00', '23:00')
        ");
        
        // Obtener los turnos recién insertados
        $stmt = $pdo->query("SELECT * FROM turnos");
        $turnos = $stmt->fetchAll();
        
        echo "<p>Turnos insertados correctamente.</p>";
    }
    
    // 3. Insertar días disponibles para mayo de 2025
    echo "<h2>Insertando días disponibles para mayo de 2025</h2>";
    
    // Fechas para mayo de 2025
    $fechas = [
        '2025-05-01',
        '2025-05-02',
        '2025-05-03',
        '2025-05-04',
        '2025-05-05',
        '2025-05-06',
        '2025-05-07',
        '2025-05-08',
        '2025-05-09',
        '2025-05-10',
        '2025-05-11',
        '2025-05-12',
        '2025-05-13',
        '2025-05-14',
        '2025-05-15'
    ];
    
    // Zonas disponibles
    $zonas = ['dentro', 'fuera'];
    
    // Contador de inserciones
    $insertados = 0;
    $actualizados = 0;
    
    // Para cada fecha, turno y zona
    foreach ($fechas as $fecha) {
        foreach ($turnos as $turno) {
            foreach ($zonas as $zona) {
                // Verificar si ya existe un registro para esta combinación
                $stmt = $pdo->prepare("
                    SELECT id, disponible FROM dias_disponibles 
                    WHERE fecha = ? AND turno_id = ? AND zona = ?
                ");
                $stmt->execute([$fecha, $turno['id'], $zona]);
                $registro = $stmt->fetch();
                
                if ($registro) {
                    // Si existe pero no está disponible, actualizarlo
                    if (!$registro['disponible']) {
                        $stmt = $pdo->prepare("
                            UPDATE dias_disponibles 
                            SET disponible = 1 
                            WHERE id = ?
                        ");
                        $stmt->execute([$registro['id']]);
                        $actualizados++;
                    }
                } else {
                    // Si no existe, insertarlo
                    $stmt = $pdo->prepare("
                        INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) 
                        VALUES (?, ?, ?, 1)
                    ");
                    $stmt->execute([$fecha, $turno['id'], $zona]);
                    $insertados++;
                }
            }
        }
    }
    
    echo "<p>Se han insertado $insertados nuevos registros y actualizado $actualizados registros existentes.</p>";
    
    // 4. Verificar la configuración
    $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
    
    if (!$config) {
        echo "<p>No hay configuración en la base de datos. Insertando configuración básica...</p>";
        
        // Insertar configuración básica
        $pdo->exec("
            INSERT INTO configuracion (
                id, 
                max_personas_sin_aprobacion, 
                capacidad_dentro_mediodia, 
                capacidad_fuera_mediodia, 
                capacidad_dentro_noche, 
                capacidad_fuera_noche
            ) VALUES (
                1, 
                8, 
                30, 
                20, 
                30, 
                20
            )
        ");
        
        echo "<p>Configuración insertada correctamente.</p>";
    } else {
        // Verificar si existen los campos de capacidad
        $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'capacidad_dentro_mediodia'");
        $campoExiste = $stmt->fetch();
        
        if (!$campoExiste) {
            echo "<p>Faltan campos de capacidad en la tabla configuracion. Añadiéndolos...</p>";
            
            // Añadir campos de capacidad
            $pdo->exec("
                ALTER TABLE configuracion 
                ADD COLUMN capacidad_dentro_mediodia INT NOT NULL DEFAULT 30,
                ADD COLUMN capacidad_fuera_mediodia INT NOT NULL DEFAULT 20,
                ADD COLUMN capacidad_dentro_noche INT NOT NULL DEFAULT 30,
                ADD COLUMN capacidad_fuera_noche INT NOT NULL DEFAULT 20
            ");
            
            echo "<p>Campos añadidos correctamente.</p>";
        }
    }
    
    // 5. Mostrar los días disponibles
    echo "<h2>Días disponibles en la base de datos</h2>";
    
    $stmt = $pdo->query("
        SELECT d.fecha, t.nombre as turno, d.zona, d.disponible
        FROM dias_disponibles d
        JOIN turnos t ON d.turno_id = t.id
        ORDER BY d.fecha, t.id, d.zona
    ");
    $diasDisponibles = $stmt->fetchAll();
    
    if (count($diasDisponibles) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Fecha</th><th>Turno</th><th>Zona</th><th>Disponible</th></tr>";
        
        foreach ($diasDisponibles as $dia) {
            $disponible = $dia['disponible'] ? 'Sí' : 'No';
            $colorFila = $dia['disponible'] ? 'lightgreen' : 'lightcoral';
            
            echo "<tr style='background-color: $colorFila;'>";
            echo "<td>{$dia['fecha']}</td>";
            echo "<td>{$dia['turno']}</td>";
            echo "<td>{$dia['zona']}</td>";
            echo "<td>{$disponible}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay días disponibles en la base de datos.</p>";
    }
    
    echo "<h2>Resumen</h2>";
    echo "<p>Se han configurado correctamente los días disponibles para mayo de 2025.</p>";
    echo "<p><a href='index.php' style='padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Volver al inicio</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Se ha producido un error: " . $e->getMessage() . "</p>";
}
?>
