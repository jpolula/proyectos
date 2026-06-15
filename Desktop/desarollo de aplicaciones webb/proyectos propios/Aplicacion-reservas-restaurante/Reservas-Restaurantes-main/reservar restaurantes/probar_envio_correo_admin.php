<?php
// Incluir las clases necesarias
require_once 'vendor/autoload.php';
require_once 'src/Utils/EmailSender.php';

use App\Utils\EmailSender;

// Configurar registro de errores detallado
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para mostrar mensajes en la página
function mostrarMensaje($mensaje, $tipo = 'info') {
    $color = $tipo === 'error' ? 'red' : ($tipo === 'success' ? 'green' : 'blue');
    echo "<div style='color: $color; margin: 10px 0; padding: 10px; border: 1px solid $color;'>";
    echo $mensaje;
    echo "</div>";
}

// Datos de prueba para la reserva
$datos_reserva = [
    'cliente_nombre' => 'Cliente de Prueba',
    'cliente_email' => 'cliente@ejemplo.com',
    'fecha' => date('d/m/Y', strtotime('+1 day')),
    'hora' => '14:00',
    'turno' => 'Mediodía',
    'zona' => 'Interior',
    'num_personas' => 4,
    'observaciones' => 'Esta es una reserva de prueba para verificar el envío de correos.'
];

// Iniciar la página HTML
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Prueba de Envío de Correos</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1, h2 { color: #2c3e50; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        button { background-color: #3498db; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; }
        button:hover { background-color: #2980b9; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Prueba de Envío de Correos a Administradores</h1>";

// Sección de configuración actual
echo "<div class='section'>
    <h2>Configuración Actual</h2>";

try {
    // Conectar a la base de datos
    $host = 'localhost';
    $db = 'restaurante_reservas';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar la configuración de correo electrónico
    $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
    
    if ($config) {
        echo "<p><strong>Email Remitente:</strong> " . htmlspecialchars($config['email_remitente'] ?? 'No configurado') . "</p>";
        echo "<p><strong>Nombre Remitente:</strong> " . htmlspecialchars($config['email_nombre_remitente'] ?? 'No configurado') . "</p>";
        echo "<p><strong>Host:</strong> " . htmlspecialchars($config['email_host'] ?? 'No configurado') . "</p>";
        echo "<p><strong>Puerto:</strong> " . htmlspecialchars($config['email_puerto'] ?? 'No configurado') . "</p>";
        echo "<p><strong>Seguridad:</strong> " . htmlspecialchars($config['email_seguridad'] ?? 'No configurado') . "</p>";
        echo "<p><strong>Email Activo:</strong> " . ($config['email_activo'] ? 'Sí' : 'No') . "</p>";
        echo "<p><strong>Notificaciones Admin:</strong> " . htmlspecialchars($config['notificaciones_admin'] ?? 'No configurado') . "</p>";
        
        // Verificar si hay contraseña configurada (sin mostrarla por seguridad)
        echo "<p><strong>Contraseña Configurada:</strong> " . (!empty($config['email_password']) ? 'Sí' : 'No') . "</p>";
    } else {
        mostrarMensaje("No se encontró configuración de correo electrónico.", "error");
    }
    
    // Verificar administradores con correo electrónico
    $stmt = $pdo->query("SELECT id, usuario, email, activo FROM administrador WHERE activo = 1");
    $admins = $stmt->fetchAll();
    
    echo "<h3>Administradores Activos</h3>";
    
    if (!empty($admins)) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Email</th></tr>";
        
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($admin['id']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($admin['email'] ?? 'No configurado') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        mostrarMensaje("No se encontraron administradores activos.", "error");
    }
} catch (PDOException $e) {
    mostrarMensaje("Error de base de datos: " . $e->getMessage(), "error");
}

echo "</div>";

// Sección de prueba de envío
echo "<div class='section'>
    <h2>Prueba de Envío</h2>
    <p>Esta sección te permite probar el envío de correos a los administradores.</p>";

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_prueba'])) {
    echo "<h3>Resultado de la Prueba</h3>";
    
    try {
        // Activar el buffer de salida para capturar los mensajes de error
        ob_start();
        
        // Intentar enviar el correo de notificación
        $tipo_notificacion = $_POST['tipo_notificacion'] ?? 'pendiente';
        $resultado = EmailSender::enviarNotificacionAdmin($datos_reserva, $tipo_notificacion);
        
        // Obtener cualquier mensaje de error
        $output = ob_get_clean();
        
        if ($resultado) {
            mostrarMensaje("¡Correo enviado con éxito! Tipo: $tipo_notificacion", "success");
        } else {
            mostrarMensaje("Error al enviar el correo. Tipo: $tipo_notificacion", "error");
        }
        
        // Mostrar cualquier mensaje de depuración
        if (!empty($output)) {
            echo "<h4>Mensajes de Depuración:</h4>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
        
        // Mostrar los últimos mensajes del registro de errores
        if (file_exists("C:/xampp/php/logs/php_error_log")) {
            echo "<h4>Últimas Entradas del Registro de Errores:</h4>";
            echo "<pre>";
            $log_lines = array_slice(file("C:/xampp/php/logs/php_error_log"), -20);
            foreach ($log_lines as $line) {
                echo htmlspecialchars($line);
            }
            echo "</pre>";
        }
    } catch (Exception $e) {
        mostrarMensaje("Excepción: " . $e->getMessage(), "error");
    }
}

// Formulario para probar el envío
echo "<form method='post' action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>
    <p>
        <label for='tipo_notificacion'>Tipo de Notificación:</label>
        <select name='tipo_notificacion' id='tipo_notificacion'>
            <option value='pendiente'>Reserva Pendiente</option>
            <option value='confirmada'>Reserva Confirmada</option>
        </select>
    </p>
    <p>
        <button type='submit' name='enviar_prueba'>Enviar Correo de Prueba</button>
    </p>
</form>";

echo "</div>";

// Sección de solución de problemas
echo "<div class='section'>
    <h2>Solución de Problemas</h2>
    <ol>
        <li>Asegúrate de que el campo 'Email Activo' esté marcado en la configuración.</li>
        <li>Verifica que el correo remitente y la contraseña estén correctamente configurados.</li>
        <li>Para Gmail, asegúrate de usar una contraseña de aplicación, no tu contraseña normal.</li>
        <li>Comprueba que al menos un administrador tenga un correo electrónico válido y esté activo.</li>
        <li>Verifica la configuración de 'Notificaciones Admin' según tus preferencias.</li>
        <li>Revisa el archivo de registro de errores de PHP para obtener más detalles sobre posibles problemas.</li>
    </ol>
</div>";

// Finalizar la página HTML
echo "</body>
</html>";
?>
