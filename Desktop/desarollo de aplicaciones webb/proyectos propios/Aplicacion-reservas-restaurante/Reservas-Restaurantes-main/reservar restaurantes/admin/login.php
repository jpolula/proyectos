<?php
// Iniciar sesión
session_start();

// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirigir si ya está autenticado
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, introduce usuario y contraseña';
    } else {
        try {
            // Conectar a la base de datos
            $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Buscar el usuario
            $stmt = $pdo->prepare("SELECT * FROM administrador WHERE usuario = ?");
            $stmt->execute([$usuario]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar la contraseña
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Inicio de sesión exitoso
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                
                // Redirigir al panel de administración
                header('Location: index.php');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            $error = 'Error de conexión a la base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Panel de Administración</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Panel de Administración</h1>
            <p class="text-gray-600">Sistema de Reservas de Restaurantes</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
            <div>
                <label for="usuario" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <input type="text" id="usuario" name="usuario" 
                       class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Introduce tu usuario" required>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="password" name="password" 
                       class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Introduce tu contraseña" required>
            </div>
            
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition duration-300">
                    Iniciar Sesión
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center">
            <a href="../index.php" class="text-blue-600 hover:underline">Volver a la página principal</a>
        </div>
        
        <div class="mt-4 text-center">
            <a href="diagnostico.php" class="text-sm text-gray-500 hover:underline">¿Problemas para iniciar sesión?</a>
        </div>
    </div>
</body>
</html>
