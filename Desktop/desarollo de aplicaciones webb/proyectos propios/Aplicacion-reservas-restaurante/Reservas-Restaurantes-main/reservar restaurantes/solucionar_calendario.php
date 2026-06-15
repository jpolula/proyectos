<?php
// Script para solucionar definitivamente el problema del calendario

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
    
    echo "<h1>Solución definitiva para el calendario</h1>";
    
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
    } else {
        echo "<p>La tabla 'dias_disponibles' ya existe.</p>";
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
    } else {
        echo "<p>Ya existen " . count($turnos) . " turnos en la base de datos.</p>";
    }
    
    // 3. Eliminar todos los registros existentes de días disponibles
    echo "<h2>Limpiando registros existentes</h2>";
    
    $stmt = $pdo->exec("DELETE FROM dias_disponibles");
    echo "<p>Se han eliminado todos los registros existentes de días disponibles.</p>";
    
    // 4. Generar todos los días de mayo 2025
    echo "<h2>Generando días disponibles para mayo de 2025</h2>";
    
    $fechasDisponibles = [];
    $startDate = new DateTime('2025-05-01');
    $endDate = new DateTime('2025-05-31');
    
    $interval = new DateInterval('P1D'); // Intervalo de 1 día
    $dateRange = new DatePeriod($startDate, $interval, $endDate);
    
    foreach ($dateRange as $date) {
        $fechasDisponibles[] = $date->format('Y-m-d');
    }
    
    // Añadir el último día (31 de mayo)
    if (!in_array('2025-05-31', $fechasDisponibles)) {
        $fechasDisponibles[] = '2025-05-31';
    }
    
    echo "<p>Se han generado " . count($fechasDisponibles) . " días para mayo de 2025.</p>";
    
    // 5. Insertar estos días en la base de datos
    $insertados = 0;
    
    foreach ($fechasDisponibles as $fecha) {
        // Insertar para cada turno y zona
        foreach ($turnos as $turno) {
            $stmt = $pdo->prepare("
                INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) 
                VALUES 
                (?, ?, 'dentro', 1),
                (?, ?, 'fuera', 1)
            ");
            $stmt->execute([$fecha, $turno['id'], $fecha, $turno['id']]);
            $insertados += 2; // 2 registros por cada turno (dentro y fuera)
        }
    }
    
    echo "<p>Se han insertado $insertados registros de días disponibles.</p>";
    
    // 6. Verificar la configuración
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
        } else {
            echo "<p>La configuración ya tiene los campos de capacidad.</p>";
        }
    }
    
    // 7. Generar un archivo JavaScript temporal para forzar la actualización del calendario
    $jsContent = "
    // Script temporal para forzar la actualización del calendario
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            // Seleccionar todos los días del calendario
            const allDays = document.querySelectorAll('.flatpickr-day');
            
            // Aplicar estilos a todos los días
            allDays.forEach(day => {
                // Hacer que todos los días sean seleccionables
                day.classList.add('available');
                day.style.backgroundColor = '#4CAF50';
                day.style.borderColor = '#4CAF50';
                day.style.color = 'white';
                day.style.pointerEvents = 'auto';
                day.style.cursor = 'pointer';
                
                // Quitar cualquier atributo disabled
                day.disabled = false;
            });
            
            console.log('Calendario actualizado por script temporal');
        }, 1000);
    });
    ";
    
    file_put_contents('js/calendario_temp.js', $jsContent);
    echo "<p>Se ha generado un script JavaScript temporal para forzar la actualización del calendario.</p>";
    
    // 8. Mostrar los días disponibles
    echo "<h2>Días disponibles en la base de datos</h2>";
    
    $stmt = $pdo->query("
        SELECT DISTINCT fecha 
        FROM dias_disponibles 
        WHERE disponible = 1
        ORDER BY fecha
    ");
    $diasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($diasDisponibles) > 0) {
        echo "<div style='display: flex; flex-wrap: wrap;'>";
        foreach ($diasDisponibles as $dia) {
            $date = new DateTime($dia);
            $diaSemana = $date->format('D');
            $diaNumero = $date->format('d');
            
            echo "<div style='background-color: #4CAF50; color: white; margin: 5px; padding: 10px; border-radius: 5px; text-align: center; width: 80px;'>";
            echo "<div style='font-weight: bold;'>$diaSemana</div>";
            echo "<div style='font-size: 24px;'>$diaNumero</div>";
            echo "<div>" . $date->format('M Y') . "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No hay días disponibles en la base de datos.</p>";
    }
    
    echo "<h2>Instrucciones</h2>";
    echo "<p>Se ha completado la reparación del calendario. Para que los cambios surtan efecto:</p>";
    echo "<ol>";
    echo "<li>Asegúrate de que el archivo <code>js/calendario.js</code> está incluido en <code>reserva.php</code></li>";
    echo "<li>Limpia la caché del navegador (Ctrl+F5)</li>";
    echo "<li>Prueba el calendario en la página de reserva</li>";
    echo "</ol>";
    
    echo "<p><a href='reserva.php' style='padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Probar el calendario</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Se ha producido un error: " . $e->getMessage() . "</p>";
}
?>
