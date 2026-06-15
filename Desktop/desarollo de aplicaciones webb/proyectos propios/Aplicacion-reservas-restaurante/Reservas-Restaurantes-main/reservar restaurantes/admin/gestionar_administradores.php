<?php
// Incluir archivo de autenticación
require_once 'auth.php';

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

$pdo = new PDO($dsn, $user, $pass, $options);

// Obtener el ID del administrador actual
$admin_id_actual = $_SESSION['admin_id'];

// Procesar formulario de creación de administrador
if (isset($_POST['crear_admin'])) {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    
    // Validar datos
    $errores = [];
    
    if (empty($usuario)) {
        $errores[] = "El nombre de usuario es obligatorio";
    }
    
    if (empty($password)) {
        $errores[] = "La contraseña es obligatoria";
    }
    
    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido";
    }
    
    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM administrador WHERE usuario = ?");
    $stmt->execute([$usuario]);
    if ($stmt->fetchColumn() > 0) {
        $errores[] = "El nombre de usuario ya está en uso";
    }
    
    // Si no hay errores, crear el administrador
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO administrador (usuario, password_hash, email, activo) VALUES (?, ?, ?, 1)");
        if ($stmt->execute([$usuario, $password_hash, $email])) {
            $mensaje_exito = "Administrador creado correctamente";
        } else {
            $errores[] = "Error al crear el administrador";
        }
    }
}

// Procesar eliminación de administrador
if (isset($_POST['eliminar_admin'])) {
    $id_admin = (int)$_POST['id_admin'];
    
    // Verificar si es el último administrador
    $stmt = $pdo->query("SELECT COUNT(*) FROM administrador");
    $total_admins = $stmt->fetchColumn();
    
    if ($total_admins <= 1) {
        $error_eliminar = "No se puede eliminar el último administrador del sistema";
    } 
    // Verificar si es el administrador actual
    elseif ($id_admin == $admin_id_actual) {
        $error_eliminar = "No puedes eliminar tu propio usuario de administrador";
    } 
    // Si pasa las validaciones, eliminar
    else {
        $stmt = $pdo->prepare("DELETE FROM administrador WHERE id = ?");
        if ($stmt->execute([$id_admin])) {
            $mensaje_exito_eliminar = "Administrador eliminado correctamente";
        } else {
            $error_eliminar = "Error al eliminar el administrador";
        }
    }
}

// Obtener lista de administradores
$stmt = $pdo->query("SELECT id, usuario, email, activo FROM administrador ORDER BY id");
$administradores = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Administradores</h1>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
        <p class="font-bold">Información</p>
        <p>La configuración de correo electrónico se ha movido a la página de <a href="configuracion_email.php" class="text-blue-600 hover:underline">Configuración de Email</a>.</p>
    </div>
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Volver al Panel
            </a>
        </div>
        
        <!-- Formulario para crear nuevo administrador -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
            <h2 class="text-xl font-semibold mb-4">Crear Nuevo Administrador</h2>
            
            <?php if (isset($errores) && !empty($errores)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (isset($mensaje_exito)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($mensaje_exito); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="usuario">
                            Nombre de Usuario
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="usuario" name="usuario" type="text" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            Contraseña
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="password" name="password" type="password" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="email" name="email" type="email" required>
                    </div>
                </div>
                
                <div class="flex items-center justify-end">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                        type="submit" name="crear_admin">
                        Crear Administrador
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Lista de administradores -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <h2 class="text-xl font-semibold mb-4">Administradores Existentes</h2>
            
            <?php if (isset($error_eliminar)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_eliminar); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($mensaje_exito_eliminar)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($mensaje_exito_eliminar); ?>
                </div>
            <?php endif; ?>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">USUARIO</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">EMAIL</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ESTADO</th>
                                <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($administradores as $admin): ?>
                            <tr>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo htmlspecialchars($admin['id']); ?></td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?php echo htmlspecialchars($admin['usuario']); ?>
                                    <?php if ($admin['id'] == $admin_id_actual): ?>
                                        <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Tú</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?php echo !empty($admin['email']) ? htmlspecialchars($admin['email']) : '<span class="text-gray-400 italic">No configurado</span>'; ?>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <?php if (isset($admin['activo']) && $admin['activo'] == 1): ?>
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Activo</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200">
                                    <form method="POST" action="" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este administrador?');">
                                        <input type="hidden" name="id_admin" value="<?php echo $admin['id']; ?>">
                                        <button type="submit" name="eliminar_admin" class="text-red-600 hover:text-red-900 <?php echo (count($administradores) <= 1 || $admin['id'] == $admin_id_actual) ? 'opacity-50 cursor-not-allowed' : ''; ?>" 
                                            <?php echo (count($administradores) <= 1 || $admin['id'] == $admin_id_actual) ? 'disabled' : ''; ?>>
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
</body>
</html>
