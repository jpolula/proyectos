<?php
/**
 * Script para redirigir a la página de denegación de reservas
 * Este archivo es una solución alternativa para la funcionalidad de denegación
 */

// Verificar si se ha proporcionado un ID de reserva
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reserva_id = (int)$_GET['id'];
    
    // Redirigir a la página de denegación
    header("Location: denegar.php?id=$reserva_id");
    exit;
} else {
    // Si no hay ID, mostrar un mensaje de error
    echo "Error: No se ha especificado un ID de reserva válido.";
    echo "<p><a href='reservas.php'>Volver a la lista de reservas</a></p>";
}
