<?php
// Script para actualizar la tabla de administrador con campos para configuración SMTP
require_once 'api/config.php';

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si las columnas ya existen
    $stmt = $pdo->prepare("SHOW COLUMNS FROM administrador LIKE 'smtp_host'");
    $stmt->execute();
    $smtp_host_exists = $stmt->rowCount() > 0;
    
    if (!$smtp_host_exists) {
        // Agregar columnas para configuración SMTP
        $pdo->exec("ALTER TABLE administrador 
                    ADD COLUMN smtp_host VARCHAR(255) DEFAULT 'smtp.gmail.com' AFTER email_nombre_remitente,
                    ADD COLUMN smtp_port INT DEFAULT 587 AFTER smtp_host,
                    ADD COLUMN smtp_secure VARCHAR(10) DEFAULT 'tls' AFTER smtp_port,
                    ADD COLUMN smtp_user VARCHAR(255) DEFAULT '' AFTER smtp_secure,
                    ADD COLUMN smtp_pass VARCHAR(255) DEFAULT '' AFTER smtp_user");
        
        echo "<p style='color:green'>Columnas para configuración SMTP agregadas correctamente.</p>";
    } else {
        echo "<p style='color:blue'>Las columnas para configuración SMTP ya existen.</p>";
    }
    
    // Mostrar la configuración actual
    $stmt = $pdo->prepare("SELECT * FROM administrador WHERE id = 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Configuración actual de correo:</h2>";
    echo "<ul>";
    echo "<li><strong>Email remitente:</strong> " . htmlspecialchars($admin['email_remitente'] ?? 'No configurado') . "</li>";
    echo "<li><strong>Nombre remitente:</strong> " . htmlspecialchars($admin['email_nombre_remitente'] ?? 'No configurado') . "</li>";
    echo "<li><strong>Servidor SMTP:</strong> " . htmlspecialchars($admin['smtp_host'] ?? 'No configurado') . "</li>";
    echo "<li><strong>Puerto SMTP:</strong> " . htmlspecialchars($admin['smtp_port'] ?? 'No configurado') . "</li>";
    echo "<li><strong>Seguridad SMTP:</strong> " . htmlspecialchars($admin['smtp_secure'] ?? 'No configurado') . "</li>";
    echo "<li><strong>Usuario SMTP:</strong> " . htmlspecialchars($admin['smtp_user'] ?? 'No configurado') . "</li>";
    echo "<li><strong>Contraseña SMTP:</strong> " . (empty($admin['smtp_pass']) ? 'No configurada' : '********') . "</li>";
    echo "</ul>";
    
    // Formulario para actualizar la configuración
    echo "<h2>Actualizar configuración de correo:</h2>";
    echo "<form method='post'>";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='email_remitente'>Email remitente:</label><br>";
    echo "<input type='email' id='email_remitente' name='email_remitente' value='" . htmlspecialchars($admin['email_remitente'] ?? '') . "' style='width: 300px;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='email_nombre_remitente'>Nombre remitente:</label><br>";
    echo "<input type='text' id='email_nombre_remitente' name='email_nombre_remitente' value='" . htmlspecialchars($admin['email_nombre_remitente'] ?? '') . "' style='width: 300px;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='smtp_host'>Servidor SMTP:</label><br>";
    echo "<input type='text' id='smtp_host' name='smtp_host' value='" . htmlspecialchars($admin['smtp_host'] ?? '') . "' style='width: 300px;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='smtp_port'>Puerto SMTP:</label><br>";
    echo "<input type='number' id='smtp_port' name='smtp_port' value='" . htmlspecialchars($admin['smtp_port'] ?? '') . "' style='width: 300px;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='smtp_secure'>Seguridad SMTP:</label><br>";
    echo "<select id='smtp_secure' name='smtp_secure' style='width: 300px;'>";
    echo "<option value=''".($admin['smtp_secure'] == '' ? ' selected' : '').">Ninguna</option>";
    echo "<option value='tls'".($admin['smtp_secure'] == 'tls' ? ' selected' : '').">TLS</option>";
    echo "<option value='ssl'".($admin['smtp_secure'] == 'ssl' ? ' selected' : '').">SSL</option>";
    echo "</select>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='smtp_user'>Usuario SMTP:</label><br>";
    echo "<input type='text' id='smtp_user' name='smtp_user' value='" . htmlspecialchars($admin['smtp_user'] ?? '') . "' style='width: 300px;'>";
    echo "</div>";
    
    echo "<div style='margin-bottom: 10px;'>";
    echo "<label for='smtp_pass'>Contraseña SMTP:</label><br>";
    echo "<input type='password' id='smtp_pass' name='smtp_pass' placeholder='Dejar en blanco para mantener la actual' style='width: 300px;'>";
    echo "</div>";
    
    echo "<button type='submit' name='actualizar' style='padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>Actualizar configuración</button>";
    echo "</form>";
    
    // Procesar el formulario
    if (isset($_POST['actualizar'])) {
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
        
        echo "<script>alert('Configuración actualizada correctamente.'); window.location.reload();</script>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// Formulario para probar el envío de correos
echo "<h2>Probar envío de correo:</h2>";
echo "<form method='post' action='test_email_system.php'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_email'>Email de destino:</label><br>";
echo "<input type='email' id='test_email' name='test_email' required style='width: 300px;'>";
echo "</div>";
echo "<button type='submit' name='test' style='padding: 10px 20px; background-color: #2196F3; color: white; border: none; cursor: pointer;'>Enviar correo de prueba</button>";
echo "</form>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
h2 {
    color: #333;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}
ul {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
}
</style>
