<?php
// Script de diagnóstico para el envío de correos electrónicos
// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir las clases necesarias
require_once 'vendor/autoload.php';
require_once 'enviar_correo_directo.php';

// Eliminado use App\Utils\Mailer, ahora usamos enviar_correo_directo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Función para verificar la conexión a la base de datos
function verificarBaseDatos() {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "<p class='text-green-600'>✅ Conexión a la base de datos: <strong>CORRECTA</strong></p>";
        return $pdo;
    } catch (PDOException $e) {
        echo "<p class='text-red-600'>❌ Error de conexión a la base de datos: " . $e->getMessage() . "</p>";
        return null;
    }
}

// Función para verificar la configuración de correo
function verificarConfiguracionCorreo($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config) {
            echo "<p class='text-red-600'>❌ No se encontró configuración de correo en la base de datos</p>";
            return null;
        }
        
        echo "<h3 class='text-lg font-semibold mt-4'>Configuración de correo actual:</h3>";
        echo "<ul class='list-disc ml-6 mt-2'>";
        echo "<li>Email remitente: " . htmlspecialchars($config['email_remitente'] ?? 'No configurado') . "</li>";
        echo "<li>Contraseña: " . (empty($config['email_password']) ? 'No configurada' : '********') . "</li>";
        echo "<li>Host SMTP: " . htmlspecialchars($config['email_host'] ?? 'No configurado') . "</li>";
        echo "<li>Puerto SMTP: " . htmlspecialchars($config['email_puerto'] ?? 'No configurado') . "</li>";
        echo "<li>Seguridad: " . htmlspecialchars($config['email_seguridad'] ?? 'No configurado') . "</li>";
        echo "<li>Nombre remitente: " . htmlspecialchars($config['email_nombre_remitente'] ?? 'No configurado') . "</li>";
        echo "<li>Correo activo: " . ($config['email_activo'] ? 'Sí' : 'No') . "</li>";
        echo "</ul>";
        
        // Verificar si hay campos vacíos importantes
        $campos_vacios = [];
        if (empty($config['email_remitente'])) $campos_vacios[] = 'email_remitente';
        if (empty($config['email_password'])) $campos_vacios[] = 'email_password';
        if (empty($config['email_host'])) $campos_vacios[] = 'email_host';
        if (empty($config['email_puerto'])) $campos_vacios[] = 'email_puerto';
        
        if (!empty($campos_vacios)) {
            echo "<p class='text-red-600 mt-2'>❌ Faltan campos importantes en la configuración: " . implode(', ', $campos_vacios) . "</p>";
        } else {
            echo "<p class='text-green-600 mt-2'>✅ Todos los campos importantes están configurados</p>";
        }
        
        return $config;
    } catch (PDOException $e) {
        echo "<p class='text-red-600'>❌ Error al verificar la configuración de correo: " . $e->getMessage() . "</p>";
        return null;
    }
}

// Función para probar el envío de correo directamente con PHPMailer
function probarEnvioDirecto($config, $destinatario) {
    try {
        $mail = new PHPMailer(true);
        
        // Configuración del servidor
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = $config['email_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['email_remitente'];
        $mail->Password = $config['email_password'];
        $mail->SMTPSecure = $config['email_seguridad'];
        $mail->Port = $config['email_puerto'];
        $mail->CharSet = 'UTF-8';
        
        // Destinatarios
        $mail->setFrom($config['email_remitente'], $config['email_nombre_remitente']);
        $mail->addAddress($destinatario);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Prueba de correo desde Sistema de Reservas';
        $mail->Body = '<h1>Prueba de correo</h1><p>Este es un correo de prueba enviado desde el Sistema de Reservas.</p>';
        $mail->AltBody = 'Prueba de correo. Este es un correo de prueba enviado desde el Sistema de Reservas.';
        
        // Capturar la salida de depuración
        ob_start();
        $resultado = $mail->send();
        $debug_output = ob_get_clean();
        
        if ($resultado) {
            echo "<p class='text-green-600 mt-4'>✅ Correo enviado correctamente a $destinatario</p>";
        } else {
            echo "<p class='text-red-600 mt-4'>❌ Error al enviar correo: " . $mail->ErrorInfo . "</p>";
        }
        
        echo "<h3 class='text-lg font-semibold mt-4'>Información de depuración SMTP:</h3>";
        echo "<pre class='bg-gray-100 p-4 mt-2 overflow-auto max-h-60 text-xs'>" . htmlspecialchars($debug_output) . "</pre>";
        
        return $resultado;
    } catch (Exception $e) {
        echo "<p class='text-red-600 mt-4'>❌ Excepción al enviar correo: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Función para probar el envío de correo usando la clase Mailer
function probarEnvioMailer($destinatario) {
    try {
        enviar_correo_directo(
            $destinatario,
            'Prueba de correo desde Sistema de Reservas (Clase Mailer)',
            '<h1>Prueba de correo (Clase Mailer)</h1><p>Este es un correo de prueba enviado desde el Sistema de Reservas usando la clase Mailer.</p>',
            'Prueba de correo (Clase Mailer). Este es un correo de prueba enviado desde el Sistema de Reservas usando la clase Mailer.'
        );
        
        if ($resultado) {
            echo "<p class='text-green-600 mt-4'>✅ Correo enviado correctamente usando la clase Mailer a $destinatario</p>";
        } else {
            echo "<p class='text-red-600 mt-4'>❌ Error al enviar correo usando la clase Mailer</p>";
        }
        
        return $resultado;
    } catch (Exception $e) {
        echo "<p class='text-red-600 mt-4'>❌ Excepción al enviar correo usando la clase Mailer: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Función para corregir la configuración de correo
function corregirConfiguracion($pdo) {
    try {
        // Actualizar con valores que sabemos que funcionan
        $stmt = $pdo->prepare("
            UPDATE configuracion 
            SET 
                email_remitente = ?,
                email_password = ?,
                email_host = ?,
                email_puerto = ?,
                email_seguridad = ?,
                email_nombre_remitente = ?,
                email_activo = ?
            WHERE id = 1
        ");
        
        $stmt->execute([
            'mafupets@gmail.com',
            'kkna ioni kpmw ouat',
            'smtp.gmail.com',
            587,
            'tls',
            'Reservas Restaurantes',
            1
        ]);
        
        echo "<p class='text-green-600 mt-4'>✅ Configuración de correo actualizada con valores predeterminados</p>";
        return true;
    } catch (PDOException $e) {
        echo "<p class='text-red-600 mt-4'>❌ Error al actualizar la configuración de correo: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Función para verificar la clase Mailer
function verificarClaseMailer() {
    $archivo_mailer = 'src/Utils/Mailer.php';
    if (!file_exists($archivo_mailer)) {
        echo "<p class='text-red-600'>❌ No se encontró el archivo de la clase Mailer</p>";
        return false;
    }
    
    $contenido = file_get_contents($archivo_mailer);
    
    // Verificar si está obteniendo la configuración de la tabla correcta
    if (strpos($contenido, "SELECT * FROM administrador WHERE id = 1") !== false) {
        echo "<p class='text-red-600'>❌ La clase Mailer está intentando obtener la configuración de la tabla 'administrador' en lugar de 'configuracion'</p>";
        
        // Corregir el problema
        $contenido_corregido = str_replace(
            "SELECT * FROM administrador WHERE id = 1",
            "SELECT * FROM configuracion WHERE id = 1",
            $contenido
        );
        
        file_put_contents($archivo_mailer, $contenido_corregido);
        echo "<p class='text-green-600'>✅ Se ha corregido la clase Mailer para obtener la configuración de la tabla 'configuracion'</p>";
    } else {
        echo "<p class='text-green-600'>✅ La clase Mailer está configurada para obtener datos de la tabla correcta</p>";
    }
    
    // Verificar si está accediendo a los campos correctos
    $campos_a_verificar = [
        'email_remitente',
        'email_password',
        'email_host',
        'email_puerto',
        'email_seguridad',
        'email_nombre_remitente',
        'email_activo'
    ];
    
    $campos_faltantes = [];
    foreach ($campos_a_verificar as $campo) {
        if (strpos($contenido, "\$config['$campo']") === false) {
            $campos_faltantes[] = $campo;
        }
    }
    
    if (!empty($campos_faltantes)) {
        echo "<p class='text-red-600'>❌ La clase Mailer no está accediendo a los siguientes campos: " . implode(', ', $campos_faltantes) . "</p>";
    } else {
        echo "<p class='text-green-600'>✅ La clase Mailer está accediendo a todos los campos necesarios</p>";
    }
    
    return true;
}

// Procesar el formulario si se ha enviado
$destinatario = '';
$accion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinatario = isset($_POST['destinatario']) ? trim($_POST['destinatario']) : '';
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Correo Electrónico</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-center mb-6">Diagnóstico de Correo Electrónico</h1>
        
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h2 class="text-xl font-semibold mb-2">Verificación del sistema</h2>
            
            <div class="space-y-2">
                <?php
                // Verificar la base de datos
                $pdo = verificarBaseDatos();
                
                if ($pdo) {
                    // Verificar la clase Mailer
                    verificarClaseMailer();
                    
                    // Verificar la configuración de correo
                    $config = verificarConfiguracionCorreo($pdo);
                }
                ?>
            </div>
        </div>
        
        <?php if ($accion === 'corregir' && $pdo): ?>
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg">
                <h2 class="text-xl font-semibold mb-2">Corrección de la configuración</h2>
                <?php corregirConfiguracion($pdo); ?>
                <p class="mt-4">La configuración ha sido actualizada con valores predeterminados que deberían funcionar.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($accion === 'probar_directo' && $pdo && !empty($destinatario)): ?>
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg">
                <h2 class="text-xl font-semibold mb-2">Prueba de envío directo con PHPMailer</h2>
                <?php 
                $config = verificarConfiguracionCorreo($pdo);
                if ($config) {
                    probarEnvioDirecto($config, $destinatario);
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($accion === 'probar_mailer' && $pdo && !empty($destinatario)): ?>
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg">
                <h2 class="text-xl font-semibold mb-2">Prueba de envío con la clase Mailer</h2>
                <?php probarEnvioMailer($destinatario); ?>
            </div>
        <?php endif; ?>
        
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Acciones de diagnóstico</h2>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-4">
                <div>
                    <label for="destinatario" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico para pruebas:</label>
                    <input type="email" id="destinatario" name="destinatario" 
                           value="<?php echo htmlspecialchars($destinatario); ?>" 
                           class="w-full p-2 border border-gray-300 rounded-md" 
                           placeholder="ejemplo@gmail.com" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button type="submit" name="accion" value="probar_directo" 
                            class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                        Probar envío directo
                    </button>
                    
                    <button type="submit" name="accion" value="probar_mailer" 
                            class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md">
                        Probar envío con Mailer
                    </button>
                    
                    <button type="submit" name="accion" value="corregir" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-md">
                        Corregir configuración
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-6 text-center">
            <a href="index.php" class="text-blue-600 hover:underline">Volver a la página principal</a>
        </div>
    </div>
</body>
</html>
