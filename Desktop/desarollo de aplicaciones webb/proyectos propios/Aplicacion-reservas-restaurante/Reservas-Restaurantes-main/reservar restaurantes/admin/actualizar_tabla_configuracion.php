<?php
// Configuración de la conexión a la base de datos
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

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si los campos ya existen
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'email_remitente'");
    $emailRemitenteExists = $stmt->fetch();
    
    if (!$emailRemitenteExists) {
        // Añadir los nuevos campos a la tabla configuracion
        $pdo->exec("
            ALTER TABLE configuracion 
            ADD COLUMN email_remitente VARCHAR(100) NULL,
            ADD COLUMN email_password VARCHAR(255) NULL,
            ADD COLUMN email_host VARCHAR(100) DEFAULT 'smtp.gmail.com',
            ADD COLUMN email_puerto INT DEFAULT 587,
            ADD COLUMN email_seguridad ENUM('tls', 'ssl') DEFAULT 'tls',
            ADD COLUMN email_nombre_remitente VARCHAR(100) NULL,
            ADD COLUMN email_activo BOOLEAN DEFAULT FALSE
        ");
        
        echo "La tabla de configuración ha sido actualizada con éxito.";
    } else {
        echo "La tabla de configuración ya tiene los campos necesarios.";
    }
    
} catch (PDOException $e) {
    echo "Error al actualizar la tabla de configuración: " . $e->getMessage();
}
?>
