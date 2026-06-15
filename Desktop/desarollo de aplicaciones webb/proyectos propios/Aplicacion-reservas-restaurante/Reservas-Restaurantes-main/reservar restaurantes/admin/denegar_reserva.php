<?php
/**
 * Script independiente para denegar reservas
 * Este script maneja la denegación de reservas de forma separada
 * para evitar problemas de sintaxis en reservas.php
 */

// Incluir archivo de autenticación
require_once 'auth.php';
require_once '../vendor/autoload.php';
require_once '../enviar_correo_directo.php';
require_once 'denegacion_handler.php';

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

// Conexión a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Procesar la denegación si hay un ID de reserva
if ($reserva_id > 0) {
    // Usar el manejador de denegación para procesar la reserva y enviar el correo
    $resultado = procesar_denegacion_reserva($pdo, $reserva_id);
    
    // Establecer mensaje y tipo de mensaje según el resultado
    $mensaje = $resultado['mensaje'];
    $tipo_mensaje = $resultado['tipo_mensaje'];
    
    // Redirigir a la página de reservas con el mensaje
    header("Location: reservas.php?mensaje=" . urlencode($mensaje) . "&tipo=" . urlencode($tipo_mensaje));
    exit;
} else {
    // Si no hay ID de reserva, redirigir a la página de reservas
    header("Location: reservas.php?mensaje=" . urlencode("No se especificó una reserva para denegar") . "&tipo=error");
    exit;
}
