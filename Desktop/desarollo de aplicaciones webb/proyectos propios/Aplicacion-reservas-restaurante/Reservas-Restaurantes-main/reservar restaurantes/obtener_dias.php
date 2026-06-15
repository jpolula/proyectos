<?php
// API para obtener días disponibles desde la base de datos

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

// Obtener parámetros de la solicitud
$turno = isset($_GET['turno']) ? $_GET['turno'] : null;
$zona = isset($_GET['zona']) ? $_GET['zona'] : null;

header('Content-Type: application/json');

try {
    // Validar parámetros
    if (!$turno || !$zona) {
        throw new Exception("Faltan parámetros: turno y zona son obligatorios");
    }
    
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener el ID del turno
    $stmt = $pdo->prepare("SELECT id FROM turnos WHERE nombre = :nombre");
    $stmt->execute(['nombre' => $turno]);
    $turnoData = $stmt->fetch();
    
    if (!$turnoData) {
        throw new Exception("Turno no encontrado: $turno");
    }
    
    $turnoId = $turnoData['id'];
    
    // Consultar días disponibles para el turno y zona seleccionados
    $stmt = $pdo->prepare("
        SELECT fecha, disponible 
        FROM dias_disponibles 
        WHERE turno_id = :turno_id 
        AND zona = :zona 
        AND fecha >= CURDATE() 
        AND fecha <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ");
    
    $stmt->execute([
        'turno_id' => $turnoId,
        'zona' => $zona
    ]);
    
    $diasDisponibles = [];
    
    while ($row = $stmt->fetch()) {
        $diasDisponibles[] = [
            'fecha' => $row['fecha'],
            'disponible' => (bool)$row['disponible']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'dias' => $diasDisponibles
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
