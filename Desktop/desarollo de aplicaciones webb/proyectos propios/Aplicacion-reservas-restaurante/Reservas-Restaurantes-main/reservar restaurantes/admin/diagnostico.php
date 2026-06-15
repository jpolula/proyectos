<?php
// Script de diagnóstico para identificar problemas con la autenticación

// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';

echo "<h1>Diagnóstico de Autenticación</h1>";

try {
    // Verificar conexión a la base de datos
    echo "<h2>1. Verificando conexión a la base de datos</h2>";
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Conexión exitosa a la base de datos</p>";
    
    // Verificar si existe la tabla administrador
    echo "<h2>2. Verificando tabla administrador</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'administrador'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ La tabla administrador existe</p>";
        
        // Verificar estructura de la tabla
        echo "<h2>3. Verificando estructura de la tabla administrador</h2>";
        $stmt = $pdo->query("DESCRIBE administrador");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Columnas encontradas: " . implode(", ", $columnas) . "</p>";
        
        if (in_array('id', $columnas) && in_array('usuario', $columnas) && in_array('password_hash', $columnas)) {
            echo "<p style='color:green'>✓ La estructura de la tabla es correcta</p>";
        } else {
            echo "<p style='color:red'>✗ La estructura de la tabla no es la esperada</p>";
        }
        
        // Verificar si existen usuarios administradores
        echo "<h2>4. Verificando usuarios administradores</h2>";
        $stmt = $pdo->query("SELECT id, usuario, password_hash FROM administrador");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($admins) > 0) {
            echo "<p style='color:green'>✓ Se encontraron " . count($admins) . " usuarios administradores</p>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Usuario</th><th>Password Hash</th></tr>";
            
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . $admin['id'] . "</td>";
                echo "<td>" . $admin['usuario'] . "</td>";
                echo "<td>" . $admin['password_hash'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Probar verificación de contraseña
            echo "<h2>5. Probando verificación de contraseña</h2>";
            $testPassword = 'admin123';
            
            foreach ($admins as $admin) {
                echo "<p>Probando usuario: " . $admin['usuario'] . "</p>";
                if (password_verify($testPassword, $admin['password_hash'])) {
                    echo "<p style='color:green'>✓ La contraseña 'admin123' es válida para este usuario</p>";
                } else {
                    echo "<p style='color:red'>✗ La contraseña 'admin123' NO es válida para este usuario</p>";
                }
            }
            
            // Crear un nuevo hash para comparar
            echo "<h2>6. Generando nuevo hash para comparación</h2>";
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "<p>Nuevo hash generado: " . $newHash . "</p>";
            echo "<p>Algoritmo utilizado: " . password_get_info($newHash)['algoName'] . "</p>";
            
            // Crear un nuevo usuario administrador
            echo "<h2>7. Creando un nuevo usuario administrador</h2>";
            
            // Primero eliminar el usuario admin si existe
            $stmt = $pdo->prepare("DELETE FROM administrador WHERE usuario = ?");
            $stmt->execute(['admin_nuevo']);
            
            // Crear el nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO administrador (usuario, password_hash) VALUES (?, ?)");
            $stmt->execute(['admin_nuevo', $newHash]);
            
            echo "<p style='color:green'>✓ Nuevo usuario 'admin_nuevo' creado con contraseña 'admin123'</p>";
            echo "<p>Por favor, intenta iniciar sesión con:</p>";
            echo "<ul>";
            echo "<li>Usuario: admin_nuevo</li>";
            echo "<li>Contraseña: admin123</li>";
            echo "</ul>";
            
        } else {
            echo "<p style='color:red'>✗ No se encontraron usuarios administradores</p>";
            
            // Crear un usuario administrador
            echo "<h2>Creando un usuario administrador</h2>";
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO administrador (usuario, password_hash) VALUES (?, ?)");
            $stmt->execute(['admin', $password_hash]);
            
            echo "<p style='color:green'>✓ Usuario administrador creado con éxito</p>";
            echo "<p>Usuario: admin</p>";
            echo "<p>Contraseña: admin123</p>";
        }
        
    } else {
        echo "<p style='color:red'>✗ La tabla administrador no existe</p>";
        
        // Crear la tabla administrador
        echo "<h2>Creando tabla administrador</h2>";
        $pdo->exec("
            CREATE TABLE administrador (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL
            )
        ");
        
        echo "<p style='color:green'>✓ Tabla administrador creada con éxito</p>";
        
        // Crear un usuario administrador
        echo "<h2>Creando un usuario administrador</h2>";
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO administrador (usuario, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', $password_hash]);
        
        echo "<p style='color:green'>✓ Usuario administrador creado con éxito</p>";
        echo "<p>Usuario: admin</p>";
        echo "<p>Contraseña: admin123</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='login.php'>Volver a la página de inicio de sesión</a></p>";
?>
