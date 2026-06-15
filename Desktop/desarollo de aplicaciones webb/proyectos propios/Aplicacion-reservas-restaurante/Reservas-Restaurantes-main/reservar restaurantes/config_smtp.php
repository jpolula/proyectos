<?php
// Script para configurar SMTP y probar el envío de correos
session_start();

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la configuración de la base de datos
require_once 'api/config.php';
require_once 'enviar_correo_directo.php';

// Función para mostrar mensajes
function mostrarMensaje($tipo, $mensaje) {
    $color = ($tipo == 'success') ? 'green' : (($tipo == 'warning') ? 'orange' : 'red');
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid $color; background-color: #f9f9f9;'>";
    echo "<strong style='color: $color;'>$tipo: </strong>$mensaje";
    echo "</div>";
}

// Verificar si PHPMailer está disponible
$phpmailer_disponible = false;
try {
    if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
        require_once dirname(__FILE__) . '/vendor/autoload.php';
        $phpmailer_disponible = class_exists('\\PHPMailer\\PHPMailer\\PHPMailer');
    }
} catch (Exception $e) {
    mostrarMensaje('error', "Error al cargar PHPMailer: " . $e->getMessage());
}

// Conectar a la base de datos
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si las columnas SMTP existen
    $stmt = $pdo->prepare("SHOW COLUMNS FROM administrador LIKE 'smtp_host'");
    $stmt->execute();
    $smtp_host_exists = $stmt->rowCount() > 0;
    
    // Agregar columnas SMTP si no existen
    if (!$smtp_host_exists && isset($_POST['crear_columnas'])) {
        $pdo->exec("ALTER TABLE administrador 
                    ADD COLUMN smtp_host VARCHAR(255) DEFAULT 'smtp.gmail.com' AFTER email_nombre_remitente,
                    ADD COLUMN smtp_port INT DEFAULT 587 AFTER smtp_host,
                    ADD COLUMN smtp_secure VARCHAR(10) DEFAULT 'tls' AFTER smtp_port,
                    ADD COLUMN smtp_user VARCHAR(255) DEFAULT '' AFTER smtp_secure,
                    ADD COLUMN smtp_pass VARCHAR(255) DEFAULT '' AFTER smtp_user");
        
        mostrarMensaje('success', "Columnas SMTP agregadas correctamente a la tabla administrador.");
        $smtp_host_exists = true;
    }
    
    // Obtener la configuración actual
    if ($smtp_host_exists) {
        $stmt = $pdo->prepare("SELECT * FROM administrador WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Actualizar la configuración
    if ($smtp_host_exists && isset($_POST['actualizar'])) {
        $email_remitente = $_POST['email_remitente'];
        $email_nombre_remitente = $_POST['email_nombre_remitente'];
        $smtp_host = $_POST['smtp_host'];
        $smtp_port = $_POST['smtp_port'];
        $smtp_secure = $_POST['smtp_secure'];
        $smtp_user = $_POST['smtp_user'];
        $smtp_pass = $_POST['smtp_pass'];
        
        // Preparar la consulta SQL
        $sql = "UPDATE administrador SET 
                email_remitente = :email_remitente,
                email_nombre_remitente = :email_nombre_remitente,
                smtp_host = :smtp_host,
                smtp_port = :smtp_port,
                smtp_secure = :smtp_secure,
                smtp_user = :smtp_user";
        
        // Agregar la contraseña solo si se proporcionó una nueva
        if (!empty($smtp_pass)) {
            $sql .= ", smtp_pass = :smtp_pass";
        }
        
        $sql .= " WHERE id = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email_remitente', $email_remitente);
        $stmt->bindParam(':email_nombre_remitente', $email_nombre_remitente);
        $stmt->bindParam(':smtp_host', $smtp_host);
        $stmt->bindParam(':smtp_port', $smtp_port);
        $stmt->bindParam(':smtp_secure', $smtp_secure);
        $stmt->bindParam(':smtp_user', $smtp_user);
        
        if (!empty($smtp_pass)) {
            $stmt->bindParam(':smtp_pass', $smtp_pass);
        }
        
        $stmt->execute();
        
        mostrarMensaje('success', "Configuración actualizada correctamente.");
        
        // Recargar la configuración
        $stmt = $pdo->prepare("SELECT * FROM administrador WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Enviar correo de prueba
    if (isset($_POST['enviar_prueba'])) {
        if (!$phpmailer_disponible) {
            mostrarMensaje('error', "PHPMailer no está disponible. No se puede enviar el correo de prueba.");
        } else if (!$smtp_host_exists) {
            mostrarMensaje('error', "Las columnas SMTP no existen en la tabla administrador. Primero debe crearlas.");
        } else {
            $email_destino = $_POST['email_destino'];
            
            if (empty($email_destino) || !filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
                mostrarMensaje('error', "Por favor, introduzca un email válido.");
            } else {
                try {
                    // Crear instancia de PHPMailer
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    
                    // Configuración del servidor
                    if (!empty($config['smtp_host']) && $config['smtp_host'] !== 'localhost') {
                        $mail->isSMTP();
                        $mail->Host = $config['smtp_host'];
                        $mail->Port = $config['smtp_port'];
                        
                        if (!empty($config['smtp_secure'])) {
                            $mail->SMTPSecure = $config['smtp_secure'];
                        }
                        
                        if (!empty($config['smtp_user']) && !empty($config['smtp_pass'])) {
                            $mail->SMTPAuth = true;
                            $mail->Username = $config['smtp_user'];
                            $mail->Password = $config['smtp_pass'];
                        }
                    } else {
                        $mail->isMail();
                    }
                    
                    // Configuración del remitente
                    $mail->setFrom($config['email_remitente'], $config['email_nombre_remitente']);
                    $mail->addReplyTo($config['email_remitente'], $config['email_nombre_remitente']);
                    
                    // Configuración del destinatario
                    $mail->addAddress($email_destino);
                    
                    // Configuración del contenido
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = "Prueba de correo - " . date('Y-m-d H:i:s');
                    
                    // Cuerpo del correo
                    $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
                        <h2 style='color: #2c3e50; text-align: center;'>Prueba de Correo</h2>
                        <p>Este es un correo de prueba enviado desde el sistema de reservas.</p>
                        <p>Si has recibido este correo, significa que la configuración de correo está funcionando correctamente.</p>
                        <p>Fecha y hora de envío: " . date('d/m/Y H:i:s') . "</p>
                        <p>Atentamente,<br><strong>Sistema de Reservas</strong></p>
                    </div>";
                    
                    $mail->AltBody = "Prueba de Correo\n\n" .
                                    "Este es un correo de prueba enviado desde el sistema de reservas.\n\n" .
                                    "Si has recibido este correo, significa que la configuración de correo está funcionando correctamente.\n\n" .
                                    "Fecha y hora de envío: " . date('d/m/Y H:i:s') . "\n\n" .
                                    "Atentamente,\nSistema de Reservas";
                    
                    // Enviar el correo
                    $mail->send();
                    mostrarMensaje('success', "Correo de prueba enviado correctamente a $email_destino.");
                } catch (Exception $e) {
                    mostrarMensaje('error', "Error al enviar el correo: " . $e->getMessage());
                }
            }
        }
    }
    
} catch (PDOException $e) {
    mostrarMensaje('error', "Error de base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración SMTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #2c5282;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 10px;
        }
        .card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="password"],
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        button.secondary {
            background-color: #2196F3;
        }
        button.secondary:hover {
            background-color: #0b7dda;
        }
        .nav {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .nav a {
            display: inline-block;
            margin-right: 15px;
            color: #2c5282;
            text-decoration: none;
        }
        .nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Configuración SMTP para Correos</h1>
    
    <div class="card">
        <h2>Estado del Sistema</h2>
        <ul>
            <li><strong>PHPMailer disponible:</strong> <?php echo $phpmailer_disponible ? 'Sí' : 'No'; ?></li>
            <li><strong>Columnas SMTP en la base de datos:</strong> <?php echo isset($smtp_host_exists) && $smtp_host_exists ? 'Sí' : 'No'; ?></li>
        </ul>
        
        <?php if (!$phpmailer_disponible): ?>
            <div style="color: red; margin-top: 10px;">
                <strong>Advertencia:</strong> PHPMailer no está disponible. Asegúrate de que está instalado correctamente.
            </div>
        <?php endif; ?>
        
        <?php if (isset($smtp_host_exists) && !$smtp_host_exists): ?>
            <form method="post">
                <button type="submit" name="crear_columnas">Crear columnas SMTP en la base de datos</button>
            </form>
        <?php endif; ?>
    </div>
    
    <?php if (isset($smtp_host_exists) && $smtp_host_exists && isset($config)): ?>
    <div class="card">
        <h2>Configuración Actual</h2>
        <table>
            <tr>
                <th>Parámetro</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Email remitente</td>
                <td><?php echo htmlspecialchars($config['email_remitente'] ?? 'No configurado'); ?></td>
            </tr>
            <tr>
                <td>Nombre remitente</td>
                <td><?php echo htmlspecialchars($config['email_nombre_remitente'] ?? 'No configurado'); ?></td>
            </tr>
            <tr>
                <td>Servidor SMTP</td>
                <td><?php echo htmlspecialchars($config['smtp_host'] ?? 'No configurado'); ?></td>
            </tr>
            <tr>
                <td>Puerto SMTP</td>
                <td><?php echo htmlspecialchars($config['smtp_port'] ?? 'No configurado'); ?></td>
            </tr>
            <tr>
                <td>Seguridad SMTP</td>
                <td><?php echo htmlspecialchars($config['smtp_secure'] ?? 'No configurado'); ?></td>
            </tr>
            <tr>
                <td>Usuario SMTP</td>
                <td><?php echo htmlspecialchars($config['smtp_user'] ?? 'No configurado'); ?></td>
            </tr>
            <tr>
                <td>Contraseña SMTP</td>
                <td><?php echo !empty($config['smtp_pass']) ? '********' : 'No configurado'; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="card">
        <h2>Actualizar Configuración</h2>
        <form method="post">
            <div class="form-group">
                <label for="email_remitente">Email remitente:</label>
                <input type="email" id="email_remitente" name="email_remitente" value="<?php echo htmlspecialchars($config['email_remitente'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email_nombre_remitente">Nombre remitente:</label>
                <input type="text" id="email_nombre_remitente" name="email_nombre_remitente" value="<?php echo htmlspecialchars($config['email_nombre_remitente'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="smtp_host">Servidor SMTP:</label>
                <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($config['smtp_host'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="smtp_port">Puerto SMTP:</label>
                <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($config['smtp_port'] ?? '587'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="smtp_secure">Seguridad SMTP:</label>
                <select id="smtp_secure" name="smtp_secure">
                    <option value="" <?php echo ($config['smtp_secure'] ?? '') === '' ? 'selected' : ''; ?>>Ninguna</option>
                    <option value="tls" <?php echo ($config['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo ($config['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="smtp_user">Usuario SMTP:</label>
                <input type="text" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($config['smtp_user'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="smtp_pass">Contraseña SMTP:</label>
                <input type="password" id="smtp_pass" name="smtp_pass" placeholder="Dejar en blanco para mantener la actual">
            </div>
            
            <button type="submit" name="actualizar">Guardar Configuración</button>
        </form>
    </div>
    
    <div class="card">
        <h2>Enviar Correo de Prueba</h2>
        <form method="post">
            <div class="form-group">
                <label for="email_destino">Email de destino:</label>
                <input type="email" id="email_destino" name="email_destino" required>
            </div>
            
            <button type="submit" name="enviar_prueba" class="secondary">Enviar Correo de Prueba</button>
        </form>
    </div>
    <?php endif; ?>
    
    <div class="nav">
        <a href="index.php">Volver al inicio</a>
    </div>
</body>
</html>
