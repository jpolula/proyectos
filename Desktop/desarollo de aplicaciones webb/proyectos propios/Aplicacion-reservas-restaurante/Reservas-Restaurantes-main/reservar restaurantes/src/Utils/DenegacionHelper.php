<?php
/**
 * Clase auxiliar para el envío de correos de denegación de reservas
 * Esta clase proporciona métodos para enviar correos de denegación de reservas
 * utilizando múltiples métodos de envío para garantizar la entrega
 */

namespace App\Utils;

// Asegurarse de que las clases necesarias estén disponibles
require_once __DIR__ . '/../../vendor/autoload.php';

// Incluir la función de envío directo si no está incluida
if (!function_exists('enviar_correo_directo')) {
    require_once __DIR__ . '/../../enviar_correo_directo.php';
}

// Incluir la clase EmailSender si no está incluida
if (!class_exists('\\App\\Utils\\EmailSender')) {
    require_once __DIR__ . '/EmailSender.php';
}

class DenegacionHelper
{
    /**
     * Envía un correo de denegación de reserva utilizando múltiples métodos
     * 
     * @param array $reserva Datos de la reserva (debe contener email, nombre, fecha_formateada, hora_formateada, turno_nombre, zona, cantidad_personas)
     * @return array Resultado del envío con información sobre el método utilizado
     */
    public static function enviarCorreoDenegacion($reserva)
    {
        // Verificar que tenemos los datos necesarios
        if (empty($reserva['email']) || empty($reserva['nombre'])) {
            return [
                'exito' => false,
                'mensaje' => 'Faltan datos necesarios para enviar el correo',
                'metodo' => 'ninguno'
            ];
        }

        // Preparar el contenido del correo
        $asunto = "Cancelación de reserva - Restaurante";
        
        // Crear el cuerpo del correo en HTML
        $cuerpo = self::generarCuerpoHTML($reserva);
        
        // Texto alternativo para clientes de correo que no soportan HTML
        $texto_alternativo = self::generarTextoAlternativo($reserva);
        
        // Intentar enviar con el método directo primero
        try {
            $enviado_directo = enviar_correo_directo($reserva['email'], $asunto, $cuerpo, $texto_alternativo);
            
            if ($enviado_directo) {
                error_log("Correo de cancelación enviado correctamente a: {$reserva['email']}");
                return [
                    'exito' => true,
                    'mensaje' => 'Correo enviado correctamente',
                    'metodo' => 'directo'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error en método directo: " . $e->getMessage());
            // Continuar con el siguiente método
        }
        
        // Si el método directo falló, intentar con EmailSender
        try {
            $emailSender = new EmailSender();
            $enviado_clase = $emailSender->enviarCorreo($reserva['email'], $asunto, $cuerpo, $texto_alternativo);
            
            if ($enviado_clase) {
                error_log("Correo de cancelación enviado correctamente (método alternativo) a: {$reserva['email']}");
                return [
                    'exito' => true,
                    'mensaje' => 'Correo enviado correctamente (método alternativo)',
                    'metodo' => 'emailsender'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error en método EmailSender: " . $e->getMessage());
            // Continuar con el siguiente método si lo hubiera
        }
        
        // Intentar con mail() nativo como último recurso
        try {
            // Cabeceras para mail() nativo
            $cabeceras = "MIME-Version: 1.0\r\n";
            $cabeceras .= "Content-type: text/html; charset=UTF-8\r\n";
            $cabeceras .= "From: Reservas Restaurante <no-reply@example.com>\r\n";
            
            $enviado_nativo = mail($reserva['email'], $asunto, $cuerpo, $cabeceras);
            
            if ($enviado_nativo) {
                error_log("Correo de cancelación enviado correctamente (método nativo) a: {$reserva['email']}");
                return [
                    'exito' => true,
                    'mensaje' => 'Correo enviado correctamente (método nativo)',
                    'metodo' => 'nativo'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error en método nativo: " . $e->getMessage());
        }
        
        // Si todos los métodos fallaron
        return [
            'exito' => false,
            'mensaje' => 'No se pudo enviar el correo por ningún método',
            'metodo' => 'fallido'
        ];
    }
    
    /**
     * Genera el cuerpo HTML del correo de denegación
     * 
     * @param array $reserva Datos de la reserva
     * @return string Cuerpo HTML del correo
     */
    private static function generarCuerpoHTML($reserva)
    {
        // Registrar la hora original y la hora formateada para depuración
        if (isset($reserva['hora_original'])) {
            error_log("Hora original en la base de datos (DenegacionHelper): " . $reserva['hora_original']);
            error_log("Hora formateada para el correo (DenegacionHelper): " . $reserva['hora_formateada']);
            
            // Asegurarse de que la hora formateada sea correcta
            if (!empty($reserva['hora_original'])) {
                // Formatear la hora manualmente para asegurar que sea correcta
                $hora_partes = explode(':', $reserva['hora_original']);
                if (count($hora_partes) >= 2) {
                    $reserva['hora_formateada'] = $hora_partes[0] . ':' . $hora_partes[1];
                    error_log("Hora reformateada manualmente (DenegacionHelper): " . $reserva['hora_formateada']);
                }
            }
        }
        
        // Formatear la zona para mostrarla de forma amigable
        $zona_formateada = ($reserva['zona'] == 'dentro') ? 'Interior' : 'Terraza';
        
        return "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    h1 { color: #e53e3e; }
                    .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                    .footer { margin-top: 30px; font-size: 0.9em; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h1>Cancelación de su reserva</h1>
                    <p>Estimado/a <strong>{$reserva['nombre']}</strong>,</p>
                    <p>Lamentamos informarle que su reserva ha sido cancelada. Los detalles de la reserva eran los siguientes:</p>
                    
                    <div class='info'>
                        <p><strong>Fecha:</strong> {$reserva['fecha_formateada']}</p>
                        <p><strong>Hora de llegada:</strong> {$reserva['hora_formateada']} h</p>
                        <p><strong>Turno:</strong> " . ucfirst($reserva['turno_nombre']) . "</p>
                        <p><strong>Zona:</strong> " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "</p>
                        <p><strong>Número de personas:</strong> {$reserva['cantidad_personas']}</p>
                    </div>
                    
                    <p>Si desea realizar una nueva reserva, puede hacerlo a través de nuestra página web o contactándonos directamente.</p>
                    
                    <p>Disculpe las molestias ocasionadas.</p>
                    
                    <div class='footer'>
                        <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }
    
    /**
     * Genera el texto alternativo del correo de denegación
     * 
     * @param array $reserva Datos de la reserva
     * @return string Texto alternativo del correo
     */
    private static function generarTextoAlternativo($reserva)
    {
        return "
            Cancelación de su reserva
            
            Estimado/a {$reserva['nombre']},
            
            Lamentamos informarle que su reserva ha sido cancelada. Los detalles de la reserva eran los siguientes:
            
            Fecha: {$reserva['fecha_formateada']}
            Hora de llegada: {$reserva['hora_formateada']} h
            Turno: " . ucfirst($reserva['turno_nombre']) . "
            Zona: " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "
            Número de personas: {$reserva['cantidad_personas']}
            
            Si desea realizar una nueva reserva, puede hacerlo a través de nuestra página web o contactándonos directamente.
            
            Disculpe las molestias ocasionadas.
            
            Este es un correo automático, por favor no responda a este mensaje.
        ";
    }
}
