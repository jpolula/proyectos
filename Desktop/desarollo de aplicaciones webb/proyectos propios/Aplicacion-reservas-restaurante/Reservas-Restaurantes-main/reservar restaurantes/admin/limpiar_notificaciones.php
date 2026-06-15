<?php
/**
 * Script para limpiar notificaciones
 */

// Incluir el sistema de notificaciones
require_once 'notificaciones.php';

// Limpiar todas las notificaciones
eliminar_notificaciones();

// Devolver respuesta JSON
header('Content-Type: application/json');
echo json_encode(['success' => true]);
