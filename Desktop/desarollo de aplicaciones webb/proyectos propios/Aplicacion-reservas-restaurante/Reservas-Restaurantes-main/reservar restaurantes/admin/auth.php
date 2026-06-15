<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el administrador está autenticado
if (!isset($_SESSION['admin_id'])) {
    // Redirigir al login
    header('Location: login.php');
    exit;
}

// Ejecutar la limpieza de días pasados automáticamente
// Solo ejecutar una vez por sesión para evitar sobrecarga
if (!isset($_SESSION['limpieza_ejecutada_hoy'])) {
    // Verificar si ya se ejecutó hoy (usar la fecha actual como identificador)
    $fecha_actual = date('Y-m-d');
    
    // Comprobar si ya se ejecutó hoy
    if (!isset($_SESSION['ultima_limpieza']) || $_SESSION['ultima_limpieza'] !== $fecha_actual) {
        // Ejecutar la limpieza
        require_once __DIR__ . '/limpiar_dias_pasados.php';
        
        // Marcar como ejecutado hoy
        $_SESSION['ultima_limpieza'] = $fecha_actual;
    }
    
    // Marcar como ejecutado en esta sesión
    $_SESSION['limpieza_ejecutada_hoy'] = true;
}
