<?php
// Este archivo contiene funciones para generar correos de reserva con la hora seleccionada

/**
 * Genera el cuerpo HTML de un correo de confirmación de reserva
 * 
 * @param array $datos Datos de la reserva
 * @return string Cuerpo HTML del correo
 */
function generarCuerpoCorreoHTML($datos) {
    // Extraer los datos necesarios
    $nombre = $datos['nombre'] ?? '';
    $fecha = $datos['fecha'] ?? '';
    $hora_seleccionada = $datos['hora_seleccionada'] ?? '';
    $horario_turno = $datos['horario_turno'] ?? '';
    $turno_texto = $datos['turno_texto'] ?? '';
    $zona_texto = $datos['zona_texto'] ?? '';
    $num_personas = $datos['num_personas'] ?? '';
    $estado = $datos['estado'] ?? 'confirmada';
    
    // Título y mensaje según el estado
    $titulo = ($estado === 'confirmada') 
        ? '¡Su reserva ha sido confirmada!' 
        : 'Su reserva está en revisión';
    
    $mensaje = ($estado === 'confirmada')
        ? 'Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:'
        : 'Hemos recibido su solicitud de reserva con los siguientes detalles:';
    
    // Crear el cuerpo del correo en HTML
    $cuerpo = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #2c5282; }
                .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { margin-top: 30px; font-size: 0.9em; color: #666; }
                .note { background-color: #fff8e1; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>{$titulo}</h1>
                <p>Estimado/a <strong>{$nombre}</strong>,</p>
                <p>{$mensaje}</p>
                
                <div class='info'>
                    <p><strong>Fecha:</strong> {$fecha}</p>
                    <p><strong>Hora reservada:</strong> {$hora_seleccionada}</p>
                    <p><strong>Horario del turno:</strong> {$horario_turno}</p>
                    <p><strong>Turno:</strong> {$turno_texto}</p>
                    <p><strong>Zona:</strong> {$zona_texto}</p>
                    <p><strong>Número de personas:</strong> {$num_personas}</p>
                </div>
    ";
    
    // Añadir nota para reservas pendientes
    if ($estado !== 'confirmada') {
        $cuerpo .= "
                <div class='note'>
                    <p><strong>Nota importante:</strong> Debido al número de personas, su reserva requiere confirmación por parte de nuestro equipo. Nos pondremos en contacto con usted lo antes posible para confirmar la disponibilidad.</p>
                </div>
        ";
    }
    
    // Añadir mensaje de cierre según el estado
    if ($estado === 'confirmada') {
        $cuerpo .= "
                <p>Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.</p>
                <p>¡Esperamos recibirle pronto en nuestro restaurante!</p>
        ";
    } else {
        $cuerpo .= "
                <p>Si tiene alguna pregunta o necesita realizar algún cambio, por favor contáctenos.</p>
                <p>Gracias por elegir nuestro restaurante.</p>
        ";
    }
    
    // Añadir pie de página
    $cuerpo .= "
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    return $cuerpo;
}

/**
 * Genera el texto alternativo de un correo de confirmación de reserva
 * 
 * @param array $datos Datos de la reserva
 * @return string Texto alternativo del correo
 */
function generarTextoAlternativoCorreo($datos) {
    // Extraer los datos necesarios
    $nombre = $datos['nombre'] ?? '';
    $fecha = $datos['fecha'] ?? '';
    $hora_seleccionada = $datos['hora_seleccionada'] ?? '';
    $horario_turno = $datos['horario_turno'] ?? '';
    $turno_texto = $datos['turno_texto'] ?? '';
    $zona_texto = $datos['zona_texto'] ?? '';
    $num_personas = $datos['num_personas'] ?? '';
    $estado = $datos['estado'] ?? 'confirmada';
    
    // Título y mensaje según el estado
    $titulo = ($estado === 'confirmada') 
        ? '¡Su reserva ha sido confirmada!' 
        : 'Su reserva está en revisión';
    
    $mensaje = ($estado === 'confirmada')
        ? 'Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:'
        : 'Hemos recibido su solicitud de reserva con los siguientes detalles:';
    
    // Crear el texto alternativo
    $texto = "
        {$titulo}
        
        Estimado/a {$nombre},
        
        {$mensaje}
        
        Fecha: {$fecha}
        Hora reservada: {$hora_seleccionada}
        Horario del turno: {$horario_turno}
        Turno: {$turno_texto}
        Zona: {$zona_texto}
        Número de personas: {$num_personas}
    ";
    
    // Añadir nota para reservas pendientes
    if ($estado !== 'confirmada') {
        $texto .= "
        
        NOTA IMPORTANTE: Debido al número de personas, su reserva requiere confirmación por parte de nuestro equipo. Nos pondremos en contacto con usted lo antes posible para confirmar la disponibilidad.
        ";
    }
    
    // Añadir mensaje de cierre según el estado
    if ($estado === 'confirmada') {
        $texto .= "
        
        Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.
        
        ¡Esperamos recibirle pronto en nuestro restaurante!
        ";
    } else {
        $texto .= "
        
        Si tiene alguna pregunta o necesita realizar algún cambio, por favor contáctenos.
        
        Gracias por elegir nuestro restaurante.
        ";
    }
    
    // Añadir pie de página
    $texto .= "
        
        Este es un correo automático, por favor no responda a este mensaje.
    ";
    
    return $texto;
}

/**
 * Genera el cuerpo HTML de un correo para administradores
 * 
 * @param array $datos Datos de la reserva
 * @param string $admin_url URL del panel de administración
 * @return string Cuerpo HTML del correo
 */
function generarCuerpoCorreoAdminHTML($datos, $admin_url) {
    // Extraer los datos necesarios
    $nombre = $datos['nombre'] ?? '';
    $email = $datos['email'] ?? '';
    $telefono = $datos['telefono'] ?? '';
    $fecha = $datos['fecha'] ?? '';
    $hora_seleccionada = $datos['hora_seleccionada'] ?? '';
    $horario_turno = $datos['horario_turno'] ?? '';
    $turno_texto = $datos['turno_texto'] ?? '';
    $zona_texto = $datos['zona_texto'] ?? '';
    $num_personas = $datos['num_personas'] ?? '';
    $reserva_id = $datos['reserva_id'] ?? '';
    
    // Crear el cuerpo del correo en HTML
    $cuerpo = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #2c5282; }
                .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { margin-top: 30px; font-size: 0.9em; color: #666; }
                .button { display: inline-block; background-color: #3182ce; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Nueva reserva pendiente de aprobación</h1>
                <p>Se ha recibido una nueva reserva que requiere su aprobación:</p>
                
                <div class='info'>
                    <p><strong>Cliente:</strong> {$nombre}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Teléfono:</strong> {$telefono}</p>
                    <p><strong>Fecha:</strong> {$fecha}</p>
                    <p><strong>Hora reservada:</strong> {$hora_seleccionada}</p>
                    <p><strong>Horario del turno:</strong> {$horario_turno}</p>
                    <p><strong>Turno:</strong> {$turno_texto}</p>
                    <p><strong>Zona:</strong> {$zona_texto}</p>
                    <p><strong>Número de personas:</strong> {$num_personas}</p>
                </div>
                
                <p>Por favor, revise esta reserva y confirme o rechace según disponibilidad.</p>
                
                <p style='text-align: center;'>
                    <a href='{$admin_url}' class='button'>Ir al panel de administración</a>
                </p>
                
                <div class='footer'>
                    <p>Este es un correo automático del sistema de reservas.</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    return $cuerpo;
}

/**
 * Genera el texto alternativo de un correo para administradores
 * 
 * @param array $datos Datos de la reserva
 * @param string $admin_url URL del panel de administración
 * @return string Texto alternativo del correo
 */
function generarTextoAlternativoCorreoAdmin($datos, $admin_url) {
    // Extraer los datos necesarios
    $nombre = $datos['nombre'] ?? '';
    $email = $datos['email'] ?? '';
    $telefono = $datos['telefono'] ?? '';
    $fecha = $datos['fecha'] ?? '';
    $hora_seleccionada = $datos['hora_seleccionada'] ?? '';
    $horario_turno = $datos['horario_turno'] ?? '';
    $turno_texto = $datos['turno_texto'] ?? '';
    $zona_texto = $datos['zona_texto'] ?? '';
    $num_personas = $datos['num_personas'] ?? '';
    $reserva_id = $datos['reserva_id'] ?? '';
    
    // Crear el texto alternativo
    $texto = "
        Nueva reserva pendiente de aprobación
        
        Se ha recibido una nueva reserva que requiere su aprobación:
        
        Cliente: {$nombre}
        Email: {$email}
        Teléfono: {$telefono}
        Fecha: {$fecha}
        Hora reservada: {$hora_seleccionada}
        Horario del turno: {$horario_turno}
        Turno: {$turno_texto}
        Zona: {$zona_texto}
        Número de personas: {$num_personas}
        
        Por favor, revise esta reserva y confirme o rechace según disponibilidad.
        
        Puede acceder al panel de administración en: {$admin_url}
        
        Este es un correo automático del sistema de reservas.
    ";
    
    return $texto;
}
?>
