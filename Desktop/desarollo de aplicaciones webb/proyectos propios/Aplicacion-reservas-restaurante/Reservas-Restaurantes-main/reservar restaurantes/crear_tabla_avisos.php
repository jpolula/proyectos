<?php
// Script para crear la tabla de avisos personalizados

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
    
    // Crear la tabla de avisos si no existe
    $sql = "CREATE TABLE IF NOT EXISTS avisos_reserva (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        texto TEXT NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        orden INT(11) NOT NULL DEFAULT 0,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    
    // Insertar algunos avisos de ejemplo
    $avisos_ejemplo = [
        "Debes llegar con al menos 10 minutos de antelación o podrías perder tu reserva.",
        "Si necesitas cancelar tu reserva, por favor hazlo con al menos 2 horas de antelación.",
        "Para grupos de más de 8 personas, se requiere un depósito del 20% que será descontado de la cuenta final."
    ];
    
    // Comprobar si ya hay avisos en la tabla
    $stmt = $pdo->query("SELECT COUNT(*) FROM avisos_reserva");
    $count = $stmt->fetchColumn();
    
    // Solo insertar ejemplos si la tabla está vacía
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO avisos_reserva (texto, orden) VALUES (?, ?)");
        
        foreach ($avisos_ejemplo as $index => $aviso) {
            $stmt->execute([$aviso, $index + 1]);
        }
        
        echo "<h2>Se han insertado avisos de ejemplo.</h2>";
    }
    
    echo "<h1>¡Tabla de avisos creada correctamente!</h1>";
    echo "<p>Ahora puedes gestionar los avisos desde el panel de administración.</p>";
    echo "<p><a href='admin/index.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Ir al panel de administración</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>No se pudo crear la tabla: " . $e->getMessage() . "</p>";
}
?>
