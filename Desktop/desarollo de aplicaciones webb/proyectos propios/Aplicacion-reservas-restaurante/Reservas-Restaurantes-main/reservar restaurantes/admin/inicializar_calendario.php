<?php
/**
 * inicializar_calendario.php
 * 
 * Script para inicializar correctamente el calendario de disponibilidad.
 * Este script marca automáticamente todos los días de mayo 2025 como disponibles.
 */

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

// Función para inicializar el calendario
function inicializarCalendario($pdo) {
    // Crear tabla dias_disponibles si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dias_disponibles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha DATE NOT NULL,
            turno_id INT NOT NULL,
            zona VARCHAR(50) NOT NULL,
            disponible TINYINT(1) NOT NULL DEFAULT 1,
            UNIQUE KEY fecha_turno_zona (fecha, turno_id, zona)
        )
    ");
    
    // Crear tabla turnos si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS turnos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) NOT NULL,
            hora_inicio TIME NOT NULL,
            hora_fin TIME NOT NULL
        )
    ");
    
    // Verificar si ya existen turnos
    $stmt = $pdo->query("SELECT COUNT(*) FROM turnos");
    $count = $stmt->fetchColumn();
    
    // Si no hay turnos, insertar los predeterminados
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
            ('mediodia', '13:00', '16:00'),
            ('noche', '20:00', '23:00')
        ");
    }
    
    // Obtener los IDs de los turnos
    $stmt = $pdo->query("SELECT id FROM turnos");
    $turnos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Generar todos los días de mayo 2025
    $fechas = [];
    $inicio = new DateTime('2025-05-01');
    $fin = new DateTime('2025-05-31');
    $intervalo = new DateInterval('P1D');
    $periodo = new DatePeriod($inicio, $intervalo, $fin);
    
    foreach ($periodo as $fecha) {
        $fechas[] = $fecha->format('Y-m-d');
    }
    
    // Añadir el último día (que no se incluye en el periodo)
    $fechas[] = '2025-05-31';
    
    // Eliminar datos existentes para mayo 2025 para evitar duplicados
    $pdo->exec("DELETE FROM dias_disponibles WHERE fecha >= '2025-05-01' AND fecha <= '2025-05-31'");
    
    // Contar cuántos días se van a insertar
    $total_registros = count($fechas) * count($turnos) * 2; // x2 por las dos zonas
    $registros_insertados = 0;
    
    // Insertar todos los días de mayo 2025 como disponibles
    foreach ($fechas as $fecha) {
        foreach ($turnos as $turno_id) {
            // Insertar para zona interior (dentro)
            $stmt = $pdo->prepare("
                INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                VALUES (?, ?, 'dentro', 1)
            ");
            $stmt->execute([$fecha, $turno_id]);
            $registros_insertados++;
            
            // Insertar para terraza (fuera)
            $stmt = $pdo->prepare("
                INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                VALUES (?, ?, 'fuera', 1)
            ");
            $stmt->execute([$fecha, $turno_id]);
            $registros_insertados++;
        }
    }
    
    return [
        'total' => $total_registros,
        'insertados' => $registros_insertados
    ];
}

// Mensaje de resultado
$resultado = null;
$error = null;

// Inicializar el calendario si se solicita
if (isset($_GET['inicializar']) && $_GET['inicializar'] == 1) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $resultado = inicializarCalendario($pdo);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

// Verificar el estado actual
$estado = [];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si existen las tablas
    $stmt = $pdo->query("SHOW TABLES LIKE 'dias_disponibles'");
    $estado['tabla_dias_disponibles'] = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'turnos'");
    $estado['tabla_turnos'] = $stmt->rowCount() > 0;
    
    // Contar días disponibles en mayo 2025
    if ($estado['tabla_dias_disponibles']) {
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT fecha) 
            FROM dias_disponibles 
            WHERE fecha >= '2025-05-01' 
            AND fecha <= '2025-05-31' 
            AND disponible = 1
        ");
        $estado['dias_disponibles_mayo'] = $stmt->fetchColumn();
    } else {
        $estado['dias_disponibles_mayo'] = 0;
    }
    
    // Contar turnos
    if ($estado['tabla_turnos']) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM turnos");
        $estado['num_turnos'] = $stmt->fetchColumn();
    } else {
        $estado['num_turnos'] = 0;
    }
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicializar Calendario - Administración</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Inicializar Calendario</h1>
            <p class="text-gray-600">Esta herramienta resuelve problemas con el calendario de disponibilidad</p>
        </header>
        
        <?php if ($error): ?>
            <div class="mb-8 p-4 rounded-md bg-red-100 text-red-800">
                <p class="font-semibold">Error:</p>
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if ($resultado): ?>
            <div class="mb-8 p-4 rounded-md bg-green-100 text-green-800">
                <p class="font-semibold">¡Calendario inicializado correctamente!</p>
                <p>Se han insertado <?php echo $resultado['insertados']; ?> de <?php echo $resultado['total']; ?> registros en la tabla de días disponibles.</p>
            </div>
        <?php endif; ?>
        
        <!-- Estado actual -->
        <div class="mb-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Estado actual del calendario</h2>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-sm text-gray-500">Tabla días disponibles</p>
                        <p class="font-semibold text-lg">
                            <?php if ($estado['tabla_dias_disponibles']): ?>
                                <span class="text-green-600">✓ Creada</span>
                            <?php else: ?>
                                <span class="text-red-600">✗ No existe</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-sm text-gray-500">Tabla turnos</p>
                        <p class="font-semibold text-lg">
                            <?php if ($estado['tabla_turnos']): ?>
                                <span class="text-green-600">✓ Creada</span>
                            <?php else: ?>
                                <span class="text-red-600">✗ No existe</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-sm text-gray-500">Días disponibles en Mayo 2025</p>
                        <p class="font-semibold text-lg">
                            <?php echo $estado['dias_disponibles_mayo']; ?> días
                            <?php if ($estado['dias_disponibles_mayo'] == 31): ?>
                                <span class="text-green-600 text-sm">(Correcto)</span>
                            <?php else: ?>
                                <span class="text-red-600 text-sm">(Incompleto)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-sm text-gray-500">Número de turnos</p>
                        <p class="font-semibold text-lg">
                            <?php echo $estado['num_turnos']; ?> turnos
                            <?php if ($estado['num_turnos'] >= 2): ?>
                                <span class="text-green-600 text-sm">(Correcto)</span>
                            <?php else: ?>
                                <span class="text-red-600 text-sm">(Incompleto)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Acciones disponibles</h2>
            
            <div class="space-y-4">
                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-md">
                    <p class="text-sm text-yellow-800 mb-2">
                        <strong>⚠️ Advertencia:</strong> La inicialización del calendario marcará todos los días de mayo 2025 como disponibles para ambos turnos y zonas. Si ya tienes días configurados, se sobrescribirán.
                    </p>
                </div>
                
                <div class="flex justify-between items-center">
                    <a href="inicializar_calendario.php?inicializar=1" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                        Inicializar Calendario Mayo 2025
                    </a>
                    
                    <a href="gestionar_calendario.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        Ir a Gestionar Calendario
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Enlaces rápidos -->
        <div class="mt-8 text-center">
            <a href="./" class="text-blue-600 hover:text-blue-800">Volver al Panel de Administración</a>
        </div>
    </div>
</body>
</html>
