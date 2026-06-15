<?php
// Este archivo implementa la funcionalidad para mostrar la hora seleccionada por el usuario
// en la página de confirmación y en los correos electrónicos

// Función para obtener la hora seleccionada por el usuario
function obtener_hora_seleccionada() {
    // Verificar si hay una hora seleccionada en la sesión
    if (isset($_SESSION['hora']) && !empty($_SESSION['hora'])) {
        return $_SESSION['hora'];
    }
    
    // Si no hay hora seleccionada, devolver null
    return null;
}

// Función para formatear la hora seleccionada (HH:MM)
function formatear_hora_seleccionada($hora = null) {
    if ($hora === null) {
        $hora = obtener_hora_seleccionada();
    }
    
    if ($hora) {
        // Formatear la hora en formato HH:MM
        return substr($hora, 0, 5);
    }
    
    return '';
}

// Función para obtener la información completa de horario
function obtener_info_horario($hora_seleccionada = null, $hora_inicio = null, $hora_fin = null) {
    // Obtener la hora seleccionada si no se proporciona
    if ($hora_seleccionada === null) {
        $hora_seleccionada = obtener_hora_seleccionada();
    }
    
    // Formatear la hora específica
    $horario_especifico = formatear_hora_seleccionada($hora_seleccionada);
    
    // Formatear el rango de horario del turno si se proporcionan las horas de inicio y fin
    $horario_turno = '';
    if ($hora_inicio && $hora_fin) {
        $horario_turno = substr($hora_inicio, 0, 5) . ' - ' . substr($hora_fin, 0, 5);
    }
    
    // Devolver un array con la información de horario
    return [
        'hora_seleccionada' => $hora_seleccionada,
        'horario_especifico' => $horario_especifico,
        'horario_turno' => $horario_turno,
        'horario_completo' => $horario_especifico . ($horario_turno ? ' (' . $horario_turno . ')' : '')
    ];
}

// Función para actualizar las plantillas de correo con la hora seleccionada
function actualizar_plantilla_correo($plantilla, $info_horario) {
    // Reemplazar las etiquetas de horario en la plantilla
    $plantilla = str_replace(
        ['{{HORA_SELECCIONADA}}', '{{HORARIO_TURNO}}', '{{HORARIO_COMPLETO}}'],
        [$info_horario['horario_especifico'], $info_horario['horario_turno'], $info_horario['horario_completo']],
        $plantilla
    );
    
    return $plantilla;
}

// Función para generar HTML que muestra la información de horario
function generar_html_horario($info_horario) {
    $html = '';
    
    if (!empty($info_horario['horario_especifico'])) {
        $html .= '<p><strong>Hora reservada:</strong> ' . htmlspecialchars($info_horario['horario_especifico']) . '</p>';
    }
    
    if (!empty($info_horario['horario_turno'])) {
        $html .= '<p><strong>Horario del turno:</strong> ' . htmlspecialchars($info_horario['horario_turno']) . '</p>';
    }
    
    return $html;
}
?>
