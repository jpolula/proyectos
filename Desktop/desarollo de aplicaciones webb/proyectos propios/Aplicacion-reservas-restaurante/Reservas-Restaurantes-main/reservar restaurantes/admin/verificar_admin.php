<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Diagnóstico de usuario administrador</h2>";

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Conexión a la base de datos: <strong style='color:green'>EXITOSA</strong></p>";
    
    // Verificar si la tabla administrador existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'administrador'");
    if ($stmt->rowCount() > 0) {
        echo "<p>Tabla 'administrador': <strong style='color:green'>EXISTE</strong></p>";
        
        // Buscar el usuario admin
        $stmt = $pdo->prepare("SELECT * FROM administrador WHERE usuario = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "<p>Usuario 'admin': <strong style='color:green'>ENCONTRADO</strong></p>";
            echo "<p>ID: " . $admin['id'] . "</p>";
            echo "<p>Email: " . $admin['email'] . "</p>";
            echo "<p>Activo: " . ($admin['activo'] ? 'Sí' : 'No') . "</p>";
            
            // Verificar la contraseña (sin mostrarla)
            $password = 'admin123';
            if (password_verify($password, $admin['password_hash'])) {
                echo "<p>Contraseña 'admin123': <strong style='color:green'>CORRECTA</strong></p>";
            } else {
                echo "<p>Contraseña 'admin123': <strong style='color:red'>INCORRECTA</strong></p>";
                echo "<p>Hash almacenado: " . $admin['password_hash'] . "</p>";
                
                // Crear un nuevo hash para la contraseña admin123
                $nuevo_hash = password_hash('admin123', PASSWORD_DEFAULT);
                echo "<p>Nuevo hash para 'admin123': " . $nuevo_hash . "</p>";
            }
        } else {
            echo "<p>Usuario 'admin': <strong style='color:red'>NO ENCONTRADO</strong></p>";
            
            // Mostrar los usuarios existentes
            $stmt = $pdo->query("SELECT id, usuario, email FROM administrador");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($usuarios) > 0) {
                echo "<h3>Usuarios existentes:</h3>";
                echo "<ul>";
                foreach ($usuarios as $usuario) {
                    echo "<li>ID: " . $usuario['id'] . ", Usuario: " . $usuario['usuario'] . ", Email: " . $usuario['email'] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No hay usuarios en la tabla administrador</p>";
            }
        }
    } else {
        echo "<p>Tabla 'administrador': <strong style='color:red'>NO EXISTE</strong></p>";
        
        // Mostrar las tablas existentes
        $stmt = $pdo->query("SHOW TABLES");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Tablas existentes:</h3>";
        echo "<ul>";
        foreach ($tablas as $tabla) {
            echo "<li>" . $tabla . "</li>";
        }
        echo "</ul>";
    }
} catch (PDOException $e) {
    echo "<p>Error de conexión a la base de datos: <strong style='color:red'>" . $e->getMessage() . "</strong></p>";
}

// Añadir un formulario para crear/actualizar el usuario admin
echo "<h3>Crear/Actualizar usuario admin</h3>";
echo "<form method='post' action='crear_admin.php'>";
echo "<button type='submit' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer;'>Crear/Actualizar usuario admin con contraseña admin123</button>";
echo "</form>";
?>
