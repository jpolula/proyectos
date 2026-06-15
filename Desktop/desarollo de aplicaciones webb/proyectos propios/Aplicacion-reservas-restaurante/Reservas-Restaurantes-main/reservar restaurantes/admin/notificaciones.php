<?php
/**
 * Sistema de notificaciones para el panel de administración
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para agregar una notificación
function agregar_notificacion($mensaje, $tipo = 'info') {
    if (!isset($_SESSION['notificaciones'])) {
        $_SESSION['notificaciones'] = [];
    }
    
    $_SESSION['notificaciones'][] = [
        'mensaje' => $mensaje,
        'tipo' => $tipo, // info, success, warning, error
        'fecha' => date('Y-m-d H:i:s'),
        'leida' => false
    ];
}

// Función para obtener todas las notificaciones
function obtener_notificaciones() {
    return isset($_SESSION['notificaciones']) ? $_SESSION['notificaciones'] : [];
}

// Función para marcar todas las notificaciones como leídas
function marcar_notificaciones_leidas() {
    if (isset($_SESSION['notificaciones'])) {
        foreach ($_SESSION['notificaciones'] as $key => $notificacion) {
            $_SESSION['notificaciones'][$key]['leida'] = true;
        }
    }
}

// Función para eliminar todas las notificaciones
function eliminar_notificaciones() {
    $_SESSION['notificaciones'] = [];
}

// Función para mostrar notificaciones en el panel
function mostrar_notificaciones() {
    $notificaciones = obtener_notificaciones();
    $html = '';
    
    if (!empty($notificaciones)) {
        $html .= '<div class="notificaciones-container mb-6">';
        
        foreach ($notificaciones as $key => $notificacion) {
            $clase_tipo = '';
            
            switch ($notificacion['tipo']) {
                case 'success':
                    $clase_tipo = 'bg-green-100 border-green-500 text-green-700';
                    $icono = '<i class="fas fa-check-circle text-green-500 mr-2"></i>';
                    break;
                case 'warning':
                    $clase_tipo = 'bg-yellow-100 border-yellow-500 text-yellow-700';
                    $icono = '<i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>';
                    break;
                case 'error':
                    $clase_tipo = 'bg-red-100 border-red-500 text-red-700';
                    $icono = '<i class="fas fa-times-circle text-red-500 mr-2"></i>';
                    break;
                default: // info
                    $clase_tipo = 'bg-blue-100 border-blue-500 text-blue-700';
                    $icono = '<i class="fas fa-info-circle text-blue-500 mr-2"></i>';
                    break;
            }
            
            $html .= '<div class="notificacion ' . $clase_tipo . ' border-l-4 p-4 mb-3 rounded shadow-sm relative">';
            $html .= '<div class="flex items-start">';
            $html .= $icono;
            $html .= '<div class="flex-1">';
            $html .= '<p class="font-medium">' . htmlspecialchars($notificacion['mensaje']) . '</p>';
            $html .= '<p class="text-sm opacity-75 mt-1">' . htmlspecialchars($notificacion['fecha']) . '</p>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '<div class="text-right">';
        $html .= '<button id="btn-limpiar-notificaciones" class="text-sm text-gray-600 hover:text-gray-800">';
        $html .= '<i class="fas fa-trash-alt mr-1"></i> Limpiar notificaciones';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Script para limpiar notificaciones
        $html .= '
        <script>
            document.getElementById("btn-limpiar-notificaciones").addEventListener("click", function() {
                fetch("limpiar_notificaciones.php")
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(".notificaciones-container").remove();
                        }
                    });
            });
        </script>';
        
        // Marcar como leídas
        marcar_notificaciones_leidas();
    }
    
    return $html;
}
