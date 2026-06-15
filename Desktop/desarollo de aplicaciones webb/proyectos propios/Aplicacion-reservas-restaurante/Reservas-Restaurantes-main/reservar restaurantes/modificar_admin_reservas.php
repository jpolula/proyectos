<?php
// Script para modificar admin/reservas.php y añadir la verificación de aforo antes de confirmar una reserva
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para crear una copia de seguridad del archivo
function crearBackup($archivo) {
    $backup = $archivo . '.bak.' . date('Ymd_His');
    if (copy($archivo, $backup)) {
        return [
            'exito' => true,
            'mensaje' => "Se ha creado una copia de seguridad en $backup",
            'ruta_backup' => $backup
        ];
    } else {
        return [
            'exito' => false,
            'mensaje' => "No se pudo crear una copia de seguridad de $archivo"
        ];
    }
}

// Función para modificar el archivo admin/reservas.php
function modificarArchivoReservas($archivo) {
    if (!file_exists($archivo)) {
        return [
            'exito' => false,
            'mensaje' => "No se encontró el archivo $archivo"
        ];
    }
    
    // Crear una copia de seguridad
    $backup = crearBackup($archivo);
    if (!$backup['exito']) {
        return $backup;
    }
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($archivo);
    
    // Buscar la sección de confirmación de reservas
    $patron_confirmar = "/case\s+'confirmar':(.*?)break;/s";
    if (preg_match($patron_confirmar, $contenido, $coincidencias)) {
        $seccion_original = $coincidencias[0];
        
        // Verificar si ya incluye la verificación de aforo
        if (strpos($seccion_original, 'verificarAforoAntesConfirmar') !== false) {
            return [
                'exito' => true,
                'mensaje' => "El archivo ya incluye la verificación de aforo antes de confirmar una reserva",
                'modificado' => false
            ];
        }
        
        // Crear la nueva sección de confirmación
        $nueva_seccion = "case 'confirmar':
    // Incluir el archivo para verificar el aforo
    require_once '../verificar_aforo_antes_confirmar.php';
    
    // Verificar si hay suficiente aforo disponible
    \$verificacion = verificarAforoAntesConfirmar(\$pdo, \$reserva_id);
    
    if (\$verificacion['disponible']) {
        // Hay suficiente aforo, confirmar la reserva
        \$stmt = \$pdo->prepare(\"UPDATE reservas SET estado = 'confirmada' WHERE id = ?\");
        if (\$stmt->execute([\$reserva_id])) {
            // Obtener los datos de la reserva
            \$stmt = \$pdo->prepare(\"
                SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, 
                       DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
                       TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada
                FROM reservas r
                JOIN clientes c ON r.cliente_id = c.id
                JOIN turnos t ON r.turno_id = t.id
                WHERE r.id = ?
            \");
            \$stmt->execute([\$reserva_id]);
            \$reserva = \$stmt->fetch();
            
            if (\$reserva) {
                // Enviar correo de confirmación
                try {
                    // Usar la función de envío directo en lugar de instanciar una clase
                    
                    // Preparar el contenido del correo
                    \$asunto = \"Confirmación de reserva - Restaurante\";
                    
                    // Crear el cuerpo del correo en HTML
                    \$cuerpo = \"
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                h1 { color: #2c5282; }
                                .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                                .footer { margin-top: 30px; font-size: 0.9em; color: #666; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <h1>¡Su reserva ha sido confirmada!</h1>
                                <p>Estimado/a <strong>{\$reserva['nombre']}</strong>,</p>
                                <p>Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:</p>
                                
                                <div class='info'>
                                    <p><strong>Fecha:</strong> {\$reserva['fecha_formateada']}</p>
                                    <p><strong>Hora:</strong> {\$reserva['hora_formateada']}</p>
                                    <p><strong>Turno:</strong> \" . ucfirst(\$reserva['turno_nombre']) . \"</p>
                                    <p><strong>Zona:</strong> \" . (\$reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . \"</p>
                                    <p><strong>Número de personas:</strong> {\$reserva['cantidad_personas']}</p>
                                </div>
                                
                                <p>Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.</p>
                                
                                <p>¡Esperamos recibirle pronto en nuestro restaurante!</p>
                                
                                <div class='footer'>
                                    <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    \";
                    
                    // Texto alternativo para clientes de correo que no soportan HTML
                    \$texto_alternativo = \"
                        ¡Su reserva ha sido confirmada!
                        
                        Estimado/a {\$reserva['nombre']},
                        
                        Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:
                        
                        Fecha: {\$reserva['fecha_formateada']}
                        Hora: {\$reserva['hora_formateada']}
                        Turno: \" . ucfirst(\$reserva['turno_nombre']) . \"
                        Zona: \" . (\$reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . \"
                        Número de personas: {\$reserva['cantidad_personas']}
                        
                        Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.
                        
                        ¡Esperamos recibirle pronto en nuestro restaurante!
                        
                        Este es un correo automático, por favor no responda a este mensaje.
                    \";
                    
                    // Enviar el correo
                    \$enviado = enviar_correo_directo(\$reserva['email'], \$asunto, \$cuerpo, \$texto_alternativo ?? '');
                    
                    if (\$enviado) {
                        \$mensaje = 'Reserva confirmada correctamente y se ha enviado un correo de confirmación al cliente.';
                        error_log(\"Correo de confirmación enviado correctamente a: {\$reserva['email']}\");
                    } else {
                        \$mensaje = 'Reserva confirmada correctamente, pero no se pudo enviar el correo de confirmación.';
                        error_log(\"Error al enviar correo de confirmación a: {\$reserva['email']}\");
                        
                        // Intentar enviar el correo nuevamente con configuración alternativa
                        try {
                            // Crear una nueva instancia de Mailer con configuración fresca
                            // Usar la función de envío directo en lugar de instanciar una clase
                            \$enviado = enviar_correo_directo(\$reserva['email'], \$asunto, \$cuerpo, \$texto_alternativo ?? '');
                            
                            if (\$enviado) {
                                \$mensaje = 'Reserva confirmada correctamente y se ha enviado un correo de confirmación al cliente (segundo intento).';
                                error_log(\"Correo de confirmación enviado correctamente en segundo intento a: {\$reserva['email']}\");
                            }
                        } catch (\\Exception \$e) {
                            error_log(\"Error en segundo intento de envío de correo: \" . \$e->getMessage());
                        }
                    }
                    
                } catch (Exception \$e) {
                    \$mensaje = 'Reserva confirmada correctamente, pero hubo un error al enviar el correo: ' . \$e->getMessage();
                }
            } else {
                \$mensaje = 'Reserva confirmada correctamente, pero no se pudo obtener la información para enviar el correo.';
            }
            
            \$tipo_mensaje = 'success';
        } else {
            \$mensaje = 'Error al confirmar la reserva.';
            \$tipo_mensaje = 'error';
        }
    } else {
        // No hay suficiente aforo disponible
        \$mensaje = \"No hay suficiente aforo disponible para confirmar esta reserva. Aforo disponible: {\$verificacion['aforo_disponible']} personas. La reserva requiere: {\$verificacion['num_personas']} personas.\";
        \$tipo_mensaje = 'error';
    }
    break;";
        
        // Reemplazar la sección original con la nueva
        $contenido_modificado = str_replace($seccion_original, $nueva_seccion, $contenido);
        
        // Guardar los cambios
        if (file_put_contents($archivo, $contenido_modificado)) {
            return [
                'exito' => true,
                'mensaje' => "Se ha modificado correctamente el archivo para verificar el aforo antes de confirmar una reserva",
                'modificado' => true,
                'backup' => $backup['ruta_backup']
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => "No se pudieron guardar los cambios en $archivo"
            ];
        }
    } else {
        return [
            'exito' => false,
            'mensaje' => "No se pudo encontrar la sección de confirmación de reservas en $archivo"
        ];
    }
}

// Configuración HTML
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Modificar admin/reservas.php</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Modificar admin/reservas.php</h1>";

// Verificar si el archivo verificar_aforo_antes_confirmar.php existe
$archivo_verificacion = __DIR__ . '/verificar_aforo_antes_confirmar.php';
if (!file_exists($archivo_verificacion)) {
    echo "<div class='error'>❌ No se encontró el archivo verificar_aforo_antes_confirmar.php</div>";
    echo "<p>Este archivo es necesario para verificar el aforo antes de confirmar una reserva.</p>";
    echo "<p>Por favor, asegúrate de que el archivo existe en la ruta correcta.</p>";
} else {
    echo "<div class='success'>✅ El archivo verificar_aforo_antes_confirmar.php existe</div>";
    
    // Modificar el archivo admin/reservas.php
    $archivo_reservas = __DIR__ . '/admin/reservas.php';
    $resultado = modificarArchivoReservas($archivo_reservas);
    
    if ($resultado['exito']) {
        if (isset($resultado['modificado']) && $resultado['modificado']) {
            echo "<div class='success'>✅ {$resultado['mensaje']}</div>";
            echo "<div class='info'>ℹ️ Se ha creado una copia de seguridad en {$resultado['backup']}</div>";
        } else {
            echo "<div class='info'>ℹ️ {$resultado['mensaje']}</div>";
        }
    } else {
        echo "<div class='error'>❌ {$resultado['mensaje']}</div>";
    }
}

echo "</body>
</html>";
?>
