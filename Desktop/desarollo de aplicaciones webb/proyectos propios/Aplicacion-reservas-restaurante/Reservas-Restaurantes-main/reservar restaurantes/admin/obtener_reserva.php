<?php
/**
 * Script para obtener los datos de una reserva mediante AJAX
 */

// Incluir archivo de autenticación
require_once 'auth.php';

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

// Verificar que se ha proporcionado un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'No se ha proporcionado un ID de reserva']);
    exit;
}

$reserva_id = (int)$_GET['id'];

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Consultar los datos de la reserva
    $stmt = $pdo->prepare("
        SELECT r.id, r.fecha, r.hora, r.zona, r.cantidad_personas, r.estado, 
               r.tiene_alergenos, r.observaciones, r.necesidades_especiales,
               r.turno_id, c.nombre, c.email, c.telefono
        FROM reservas r
        JOIN clientes c ON r.cliente_id = c.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch();
    
    if (!$reserva) {
        echo json_encode(['error' => 'No se ha encontrado la reserva con ID ' . $reserva_id]);
        exit;
    }
    
    // Devolver los datos en formato JSON
    echo json_encode($reserva);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
}
?>
