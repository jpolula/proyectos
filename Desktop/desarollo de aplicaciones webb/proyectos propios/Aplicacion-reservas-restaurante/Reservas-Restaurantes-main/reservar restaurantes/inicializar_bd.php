<?php
/**
 * inicializar_bd.php
 * 
 * Script para inicializar correctamente la base de datos del sistema de reservas.
 * Crea todas las tablas necesarias e inserta datos iniciales.
 */

// Configuración de la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Prevenir caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicialización del Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-center mb-6">Inicialización del Sistema de Reservas</h1>
        <div class="space-y-4">';

try {
    // Conexión a MySQL
    $dsn = "mysql:host=$host;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>✅ Conexión a MySQL establecida correctamente.</p>
        </div>';
    
    // Crear la base de datos si no existe
    $stmt = $pdo->query("CREATE DATABASE IF NOT EXISTS `$db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>✅ Base de datos creada o verificada: ' . $db . '</p>
        </div>';
    
    // Seleccionar la base de datos
    $pdo->exec("USE `$db`");
    
    // Crear tabla de turnos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `turnos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(50) NOT NULL,
            `hora_inicio` TIME NOT NULL,
            `hora_fin` TIME NOT NULL,
            UNIQUE KEY `nombre` (`nombre`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>✅ Tabla creada: turnos</p>
        </div>';
    
    // Crear tabla de días disponibles
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `dias_disponibles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `fecha` DATE NOT NULL,
            `turno_id` INT NOT NULL,
            `zona` VARCHAR(50) NOT NULL,
            `disponible` TINYINT(1) NOT NULL DEFAULT 1,
            UNIQUE KEY `fecha_turno_zona` (`fecha`, `turno_id`, `zona`),
            FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>✅ Tabla creada: dias_disponibles</p>
        </div>';
    
    // Crear tabla de reservas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `reservas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nombre` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `telefono` VARCHAR(20) NOT NULL,
            `num_personas` INT NOT NULL,
            `fecha` DATE NOT NULL,
            `turno_id` INT NOT NULL,
            `zona` VARCHAR(50) NOT NULL,
            `hora` TIME NOT NULL,
            `estado` VARCHAR(20) NOT NULL DEFAULT 'pendiente',
            `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>✅ Tabla creada: reservas</p>
        </div>';
    
    // Crear tabla de configuración
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `configuracion` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `max_personas_sin_aprobacion` INT NOT NULL DEFAULT 8,
            `capacidad_dentro_mediodia` INT NOT NULL DEFAULT 30,
            `capacidad_fuera_mediodia` INT NOT NULL DEFAULT 20,
            `capacidad_dentro_noche` INT NOT NULL DEFAULT 30,
            `capacidad_fuera_noche` INT NOT NULL DEFAULT 20
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>✅ Tabla creada: configuracion</p>
        </div>';
    
    // Crear tabla de usuarios administradores
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `administradores` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario` VARCHAR(50) NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `nombre` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `ultimo_acceso` TIMESTAMP NULL,
            UNIQUE KEY `usuario` (`usuario`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
            <p>✅ Tabla creada: administradores</p>
        </div>';
    
    // Comprobar si ya existen turnos
    $stmt = $pdo->query("SELECT COUNT(*) FROM turnos");
    $turnosExistentes = $stmt->fetchColumn();
    
    if ($turnosExistentes == 0) {
        // Insertar turnos básicos
        $pdo->exec("
            INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
            ('mediodia', '13:00:00', '16:00:00'),
            ('noche', '20:00:00', '23:00:00')
        ");
        echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                <p>✅ Turnos básicos insertados: mediodía y noche</p>
            </div>';
    } else {
        echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                <p>ℹ️ Ya existen turnos en la base de datos (encontrados: ' . $turnosExistentes . ')</p>
            </div>';
    }
    
    // Comprobar si ya existe configuración
    $stmt = $pdo->query("SELECT COUNT(*) FROM configuracion");
    $configExistente = $stmt->fetchColumn();
    
    if ($configExistente == 0) {
        // Insertar configuración por defecto
        $pdo->exec("
            INSERT INTO configuracion (
                max_personas_sin_aprobacion, 
                capacidad_dentro_mediodia,
                capacidad_fuera_mediodia,
                capacidad_dentro_noche,
                capacidad_fuera_noche
            ) VALUES (
                8, 30, 20, 30, 20
            )
        ");
        echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                <p>✅ Configuración por defecto insertada</p>
            </div>';
    } else {
        echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                <p>ℹ️ Ya existe configuración en la base de datos</p>
            </div>';
    }
    
    // Comprobar si ya existe un administrador
    $stmt = $pdo->query("SELECT COUNT(*) FROM administradores");
    $adminExistente = $stmt->fetchColumn();
    
    if ($adminExistente == 0) {
        // Crear un administrador por defecto (admin/admin123)
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT INTO administradores (usuario, password, nombre, email) VALUES 
            ('admin', '$passwordHash', 'Administrador', 'admin@example.com')
        ");
        echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                <p>✅ Administrador por defecto creado (Usuario: <strong>admin</strong> / Contraseña: <strong>admin123</strong>)</p>
            </div>';
    } else {
        echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                <p>ℹ️ Ya existen usuarios administradores en la base de datos</p>
            </div>';
    }
    
    // Verificar los días disponibles para mayo 2025
    $stmt = $pdo->query("SELECT COUNT(*) FROM dias_disponibles WHERE fecha >= '2025-05-01' AND fecha <= '2025-05-31'");
    $diasExistentes = $stmt->fetchColumn();
    
    if ($diasExistentes < 10) {
        echo '<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                <p>⚠️ Faltan días disponibles para mayo 2025. Se insertarán automáticamente...</p>
            </div>';
        
        // Obtener todos los turnos
        $stmtTurnos = $pdo->query("SELECT id FROM turnos");
        $turnos = $stmtTurnos->fetchAll(PDO::FETCH_COLUMN);
        
        // Primero, eliminar cualquier día existente de mayo 2025 para evitar duplicados
        $pdo->exec("DELETE FROM dias_disponibles WHERE fecha >= '2025-05-01' AND fecha <= '2025-05-31'");
        
        // Generar todos los días de mayo 2025
        $inicio = new DateTime('2025-05-01');
        $fin = new DateTime('2025-05-31');
        $intervalo = new DateInterval('P1D');
        $periodo = new DatePeriod($inicio, $intervalo, $fin);
        
        $diasInsertados = 0;
        
        // Insertar días para mayo 2025
        foreach ($periodo as $fecha) {
            $fechaStr = $fecha->format('Y-m-d');
            
            foreach ($turnos as $turnoId) {
                // Insertar para zona interior
                $stmt = $pdo->prepare("INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) VALUES (?, ?, 'dentro', 1)");
                $stmt->execute([$fechaStr, $turnoId]);
                
                // Insertar para terraza
                $stmt = $pdo->prepare("INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) VALUES (?, ?, 'fuera', 1)");
                $stmt->execute([$fechaStr, $turnoId]);
                
                $diasInsertados += 2;
            }
        }
        
        // Añadir el último día (31 de mayo)
        $ultimoDia = '2025-05-31';
        foreach ($turnos as $turnoId) {
            // Insertar para zona interior
            $stmt = $pdo->prepare("INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) VALUES (?, ?, 'dentro', 1)");
            $stmt->execute([$ultimoDia, $turnoId]);
            
            // Insertar para terraza
            $stmt = $pdo->prepare("INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) VALUES (?, ?, 'fuera', 1)");
            $stmt->execute([$ultimoDia, $turnoId]);
            
            $diasInsertados += 2;
        }
        
        echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4">
                <p>✅ Se han insertado ' . $diasInsertados . ' registros de días disponibles para mayo 2025</p>
            </div>';
    } else {
        echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                <p>ℹ️ Ya existen días disponibles para mayo 2025 (encontrados: ' . $diasExistentes . ')</p>
            </div>';
    }
    
    // Mensaje final de éxito
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mt-6">
            <p class="font-bold">✅ La inicialización se ha completado correctamente.</p>
            <p>La base de datos está lista para usar con el sistema de reservas.</p>
        </div>';
    
} catch (PDOException $e) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
            <p class="font-bold">❌ Error durante la inicialización:</p>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
        </div>';
}

echo '      <div class="mt-6 text-center">
                <a href="index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Volver a la página principal
                </a>
                <a href="reserva.php" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-4">
                    Ir a la página de reservas
                </a>
            </div>
        </div>
    </div>
</body>
</html>';
?>
