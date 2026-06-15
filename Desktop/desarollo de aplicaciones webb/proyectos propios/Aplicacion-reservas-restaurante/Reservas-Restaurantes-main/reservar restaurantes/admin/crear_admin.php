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

echo "<h1>Gestión de Usuario Administrador</h1>";

try {
    echo "<p>Conectando a la base de datos...</p>";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p>Conexión exitosa.</p>";
    
    // Verificar si existe el usuario admin
    echo "<p>Verificando si existe el usuario admin...</p>";
    $stmt = $pdo->prepare("SELECT * FROM administrador WHERE usuario = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p>El usuario admin ya existe (ID: {$admin['id']}).</p>";
        echo "<p>Contraseña hash actual: {$admin['password_hash']}</p>";
        
        // El usuario ya existe, actualizar la contraseña
        echo "<p>Actualizando la contraseña...</p>";
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE administrador SET password_hash = ? WHERE usuario = ?");
        $stmt->execute([$password_hash, 'admin']);
        echo "<p style='color: green; font-weight: bold;'>La contraseña del usuario admin ha sido actualizada correctamente.</p>";
        echo "<p>Nuevo hash: {$password_hash}</p>";
    } else {
        echo "<p>El usuario admin no existe.</p>";
        
        // Crear el usuario admin
        echo "<p>Creando el usuario admin...</p>";
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO administrador (usuario, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', $password_hash]);
        echo "<p style='color: green; font-weight: bold;'>El usuario admin ha sido creado correctamente.</p>";
        echo "<p>Hash de contraseña: {$password_hash}</p>";
    }
    
    // Verificar que el usuario existe y la contraseña funciona
    echo "<p>Verificando credenciales...</p>";
    $stmt = $pdo->prepare("SELECT * FROM administrador WHERE usuario = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin123', $admin['password_hash'])) {
        echo "<p style='color: green; font-weight: bold;'>Verificación exitosa: Las credenciales funcionan correctamente.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>Error de verificación: Las credenciales no funcionan.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='login.php'>Ir a la página de inicio de sesión</a></p>";
?>
