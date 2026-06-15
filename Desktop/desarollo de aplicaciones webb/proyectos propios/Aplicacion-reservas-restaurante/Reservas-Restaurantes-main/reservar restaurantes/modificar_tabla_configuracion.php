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
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'notificaciones_admin'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if (!$columna_existe) {
        // Añadir la nueva columna a la tabla configuracion
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN notificaciones_admin ENUM('todas', 'pendientes', 'ninguna') NOT NULL DEFAULT 'pendientes'");
        echo "Columna 'notificaciones_admin' añadida correctamente a la tabla configuracion.";
    } else {
        echo "La columna 'notificaciones_admin' ya existe en la tabla configuracion.";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
