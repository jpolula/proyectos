<?php
// Script para probar el envío de correos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la función de envío directo
require_once 'enviar_correo_directo.php';

// Datos de prueba
$destinatario = 'cliente@example.com';
$asunto = 'Prueba de reserva - ' . date('Y-m-d H:i:s');
$nombre_cliente = 'Cliente de Prueba';
$fecha_reserva = date('d/m/Y', strtotime('+2 days'));
$hora = '20:30';
$turno = 'Noche';
$zona = 'Interior';
$personas = 4;

// Generar el cuerpo del correo HTML
$cuerpo_html = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Confirmación de Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .container {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 10px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .highlight {
            font-weight: bold;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Confirmación de Reserva</h1>
        </div>
        
        <p>Estimado/a <span class='highlight'>{$nombre_cliente}</span>,</p>
        
        <p>Le confirmamos que su reserva ha sido registrada correctamente en nuestro sistema. A continuación, le mostramos los detalles:</p>
        
        <table>
            <tr>
                <th>Fecha</th>
                <td>{$fecha_reserva}</td>
            </tr>
            <tr>
                <th>Hora</th>
                <td>{$hora}</td>
            </tr>
            <tr>
                <th>Turno</th>
                <td>{$turno}</td>
            </tr>
            <tr>
                <th>Zona</th>
                <td>{$zona}</td>
            </tr>
            <tr>
                <th>Personas</th>
                <td>{$personas}</td>
            </tr>
        </table>
        
        <p>Le recomendamos llegar 10 minutos antes de la hora reservada.</p>
        
        <p>Si necesita modificar o cancelar su reserva, puede hacerlo a través de nuestra página web o contactándonos directamente por teléfono.</p>
        
        <p>¡Esperamos verle pronto!</p>
        
        <div class='footer'>
            <p>Este correo ha sido generado automáticamente, por favor no responda a este mensaje.</p>
            <p>© 2025 Restaurante - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
";

// Generar el texto alternativo
$texto_alternativo = "
CONFIRMACIÓN DE RESERVA

Estimado/a {$nombre_cliente},

Le confirmamos que su reserva ha sido registrada correctamente en nuestro sistema. A continuación, le mostramos los detalles:

Fecha: {$fecha_reserva}
Hora: {$hora}
Turno: {$turno}
Zona: {$zona}
Personas: {$personas}

Le recomendamos llegar 10 minutos antes de la hora reservada.

Si necesita modificar o cancelar su reserva, puede hacerlo a través de nuestra página web o contactándonos directamente por teléfono.

¡Esperamos verle pronto!

Este correo ha sido generado automáticamente, por favor no responda a este mensaje.
© 2025 Restaurante - Todos los derechos reservados
";

// Enviar el correo
$resultado = enviar_correo_directo($destinatario, $asunto, $cuerpo_html, $texto_alternativo);

// Mostrar el resultado
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Prueba de envío de correo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
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
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
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
    <h1>Prueba de envío de correo</h1>";

if ($resultado) {
    echo "<div class='success'>
        <h2>✅ Correo guardado correctamente</h2>
        <p>El correo ha sido guardado en la carpeta 'emails_sent'. Puede revisar el contenido abriendo el archivo HTML generado.</p>
    </div>";
    
    // Obtener la lista de archivos en la carpeta emails_sent
    $dir = __DIR__ . '/emails_sent';
    $archivos = glob($dir . '/*.html');
    
    // Ordenar por fecha de modificación (más reciente primero)
    usort($archivos, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    
    // Mostrar los últimos 5 correos
    if (!empty($archivos)) {
        echo "<div class='info'>
            <h3>Últimos correos guardados:</h3>
            <ul>";
        
        $count = 0;
        foreach ($archivos as $archivo) {
            $nombre = basename($archivo);
            $fecha = date('Y-m-d H:i:s', filemtime($archivo));
            echo "<li><a href='emails_sent/{$nombre}' target='_blank'>{$nombre}</a> - {$fecha}</li>";
            
            $count++;
            if ($count >= 5) break;
        }
        
        echo "</ul>
        </div>";
    }
} else {
    echo "<div class='error'>
        <h2>❌ Error al guardar el correo</h2>
        <p>Ha ocurrido un error al intentar guardar el correo. Por favor, revise los logs del servidor para más información.</p>
    </div>";
}

echo "<a href='index.php' class='btn'>Volver al inicio</a>
</body>
</html>";
?>
