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
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'capacidad_dentro_mediodia'");
    $campoExiste = $stmt->fetch();
    
    if (!$campoExiste) {
        // Añadir los nuevos campos a la tabla configuracion
        $pdo->exec("
            ALTER TABLE configuracion 
            ADD COLUMN capacidad_dentro_mediodia INT NOT NULL DEFAULT 30,
            ADD COLUMN capacidad_fuera_mediodia INT NOT NULL DEFAULT 20,
            ADD COLUMN capacidad_dentro_noche INT NOT NULL DEFAULT 30,
            ADD COLUMN capacidad_fuera_noche INT NOT NULL DEFAULT 20
        ");
        
        echo "La tabla de configuración ha sido actualizada con éxito.";
    } else {
        echo "La tabla de configuración ya tiene los campos necesarios.";
    }
    
} catch (PDOException $e) {
    echo "Error al actualizar la tabla de configuración: " . $e->getMessage();
}
?>
