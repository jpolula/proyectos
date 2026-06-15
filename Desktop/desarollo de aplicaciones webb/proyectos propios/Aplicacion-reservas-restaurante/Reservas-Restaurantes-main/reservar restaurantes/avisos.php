<?php
/**
 * Archivo de configuración de avisos para el sistema de reservas
 * Contiene funciones para mostrar diferentes tipos de avisos al usuario
 */

// Función para mostrar un mensaje de éxito
function mostrarExito($mensaje) {
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">';
    echo '<p class="font-bold">¡Éxito!</p>';
    echo '<p>' . $mensaje . '</p>';
    echo '</div>';
}

// Función para mostrar un mensaje de error
function mostrarError($mensaje) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">';
    echo '<p class="font-bold">Error</p>';
    echo '<p>' . $mensaje . '</p>';
    echo '</div>';
}

// Función para mostrar un mensaje de advertencia
function mostrarAdvertencia($mensaje) {
    echo '<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">';
    echo '<p class="font-bold">Advertencia</p>';
    echo '<p>' . $mensaje . '</p>';
    echo '</div>';
}

// Función para mostrar un mensaje informativo
function mostrarInfo($mensaje) {
    echo '<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4" role="alert">';
    echo '<p class="font-bold">Información</p>';
    echo '<p>' . $mensaje . '</p>';
    echo '</div>';
}

// Función para mostrar un aviso al administrador (deshabilitada)
function mostrarAvisoAdmin($titulo, $mensaje) {
    // Esta función ha sido deshabilitada para no mostrar avisos al administrador en la parte del usuario
    return;
}

// Función para mostrar los mensajes de sesión y luego eliminarlos
function mostrarMensajesSesion() {
    if (isset($_SESSION['exito'])) {
        mostrarExito($_SESSION['exito']);
        unset($_SESSION['exito']);
    }
    
    if (isset($_SESSION['error'])) {
        mostrarError($_SESSION['error']);
        unset($_SESSION['error']);
    }
    
    if (isset($_SESSION['advertencia'])) {
        mostrarAdvertencia($_SESSION['advertencia']);
        unset($_SESSION['advertencia']);
    }
    
    if (isset($_SESSION['info'])) {
        mostrarInfo($_SESSION['info']);
        unset($_SESSION['info']);
    }
    
    // Mostrar errores de reserva específicos
    if (isset($_SESSION['error_reserva'])) {
        mostrarError($_SESSION['error_reserva']);
        unset($_SESSION['error_reserva']);
    }
    
    // Mostrar confirmación de reserva exitosa
    if (isset($_SESSION['reserva_exitosa'])) {
        mostrarExito('Su reserva ha sido registrada correctamente. ¡Gracias por confiar en nosotros!');
        unset($_SESSION['reserva_exitosa']);
    }
}
?>
