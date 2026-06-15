<?php
// Incluir archivo de autenticación
require_once 'auth.php';
require_once '../enviar_correo_directo.php';

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

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$pageTitle = 'Configuración de Correo Electrónico';

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener la configuración actual
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = ?");
    $stmt->execute([1]); // Asumimos que el ID del administrador es 1
    $admin = $stmt->fetch();
    
    // Valores por defecto si no hay configuración
    $email_remitente = $admin['email_remitente'] ?? '';
    $email_password = $admin['email_password'] ?? '';
    $email_host = $admin['email_host'] ?? 'smtp.gmail.com';
    $email_puerto = $admin['email_puerto'] ?? 587;
    $email_seguridad = $admin['email_seguridad'] ?? 'tls';
    $email_nombre_remitente = $admin['email_nombre_remitente'] ?? '';
    $email_activo = $admin['email_activo'] ?? 0;
    $notificaciones_admin = $admin['notificaciones_admin'] ?? 'pendientes';
    
    // Procesar el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
            // Obtener datos del formulario
            $email_remitente = $_POST['email_remitente'] ?? '';
            $email_password = $_POST['email_password'] ?? '';
            $email_host = $_POST['email_host'] ?? 'smtp.gmail.com';
            $email_puerto = $_POST['email_puerto'] ?? 587;
            $email_seguridad = $_POST['email_seguridad'] ?? 'tls';
            $email_nombre_remitente = $_POST['email_nombre_remitente'] ?? '';
            $email_activo = isset($_POST['email_activo']) ? 1 : 0;
            $notificaciones_admin = $_POST['notificaciones_admin'] ?? 'pendientes';
            
            // Validar datos
            if (empty($email_remitente)) {
                throw new Exception("El correo electrónico del remitente es obligatorio.");
            }
            
            if ($email_activo && empty($email_password)) {
                throw new Exception("La contraseña es obligatoria si el correo está activo.");
            }
            
            // Actualizar la configuración
            $stmt = $pdo->prepare("
                UPDATE configuracion 
                SET 
                    email_remitente = ?,
                    email_password = ?,
                    email_host = ?,
                    email_puerto = ?,
                    email_seguridad = ?,
                    email_nombre_remitente = ?,
                    email_activo = ?,
                    notificaciones_admin = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $email_remitente,
                $email_password,
                $email_host,
                $email_puerto,
                $email_seguridad,
                $email_nombre_remitente,
                $email_activo,
                $notificaciones_admin,
                1 // ID del administrador
            ]);
            
            $mensaje = 'Configuración de correo electrónico actualizada correctamente.';
            $tipo_mensaje = 'success';
        }
    }
} catch (Exception $e) {
    $mensaje = 'Error: ' . $e->getMessage();
    $tipo_mensaje = 'error';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Barra de navegación superior -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold">Panel de Administración</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="logout.php" class="ml-4 px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900"><?php echo $pageTitle; ?></h1>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                    Volver al Panel
                </a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mb-4 p-4 rounded-md <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de Configuración de Correo -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Configuración de Correo Electrónico
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Configura el correo electrónico que se utilizará para enviar notificaciones a los usuarios.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar">
                        
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <!-- Correo Electrónico del Remitente -->
                            <div class="sm:col-span-3">
                                <label for="email_remitente" class="block text-sm font-medium text-gray-700">
                                    Correo Electrónico del Remitente *
                                </label>
                                <div class="mt-1">
                                    <input type="email" id="email_remitente" name="email_remitente" 
                                           value="<?php echo htmlspecialchars($email_remitente); ?>" 
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Ejemplo: restaurante@gmail.com
                                </p>
                            </div>
                            
                            <!-- Nombre del Remitente -->
                            <div class="sm:col-span-3">
                                <label for="email_nombre_remitente" class="block text-sm font-medium text-gray-700">
                                    Nombre del Remitente
                                </label>
                                <div class="mt-1">
                                    <input type="text" id="email_nombre_remitente" name="email_nombre_remitente" 
                                           value="<?php echo htmlspecialchars($email_nombre_remitente); ?>" 
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Ejemplo: Restaurante La Buena Mesa
                                </p>
                            </div>
                            
                            <!-- Contraseña -->
                            <div class="sm:col-span-3">
                                <label for="email_password" class="block text-sm font-medium text-gray-700">
                                    Contraseña de la Cuenta de Correo *
                                </label>
                                <div class="mt-1 relative">
                                    <input type="password" id="email_password" name="email_password" 
                                           value="<?php echo htmlspecialchars($email_password); ?>" 
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <button type="button" id="toggle_password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Para Gmail, usa una contraseña de aplicación
                                </p>
                            </div>
                            
                            <!-- Servidor SMTP -->
                            <div class="sm:col-span-3">
                                <label for="email_host" class="block text-sm font-medium text-gray-700">
                                    Servidor SMTP
                                </label>
                                <div class="mt-1">
                                    <input type="text" id="email_host" name="email_host" 
                                           value="<?php echo htmlspecialchars($email_host); ?>" 
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Por defecto: smtp.gmail.com
                                </p>
                            </div>
                            
                            <!-- Puerto -->
                            <div class="sm:col-span-2">
                                <label for="email_puerto" class="block text-sm font-medium text-gray-700">
                                    Puerto
                                </label>
                                <div class="mt-1">
                                    <input type="number" id="email_puerto" name="email_puerto" 
                                           value="<?php echo htmlspecialchars($email_puerto); ?>" 
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Por defecto: 587
                                </p>
                            </div>
                            
                            <!-- Seguridad -->
                            <div class="sm:col-span-2">
                                <label for="email_seguridad" class="block text-sm font-medium text-gray-700">
                                    Seguridad
                                </label>
                                <div class="mt-1">
                                    <select id="email_seguridad" name="email_seguridad" 
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="tls" <?php echo $email_seguridad === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo $email_seguridad === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Por defecto: TLS
                                </p>
                            </div>
                            
                            <!-- Activar Correo -->
                            <div class="sm:col-span-2">
                                <div class="flex items-start mt-6">
                                    <div class="flex items-center h-5">
                                        <input id="email_activo" name="email_activo" type="checkbox" 
                                               <?php echo $email_activo ? 'checked' : ''; ?> 
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="email_activo" class="font-medium text-gray-700">Activar envío de correos</label>
                                        <p class="text-gray-500">Habilita esta opción para enviar correos a los usuarios.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configuración de Notificaciones para Administradores -->
                            <div class="sm:col-span-6 mt-6">
                                <label for="notificaciones_admin" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notificaciones por correo para Administradores
                                </label>
                                <div class="mt-1">
                                    <select id="notificaciones_admin" name="notificaciones_admin" 
                                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="todas" <?php echo $notificaciones_admin === 'todas' ? 'selected' : ''; ?>>Todas las reservas</option>
                                        <option value="pendientes" <?php echo $notificaciones_admin === 'pendientes' ? 'selected' : ''; ?>>Solo reservas pendientes de confirmar</option>
                                        <option value="ninguna" <?php echo $notificaciones_admin === 'ninguna' ? 'selected' : ''; ?>>Ninguna notificación</option>
                                    </select>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    Selecciona qué tipos de reservas generarán notificaciones por correo electrónico a los administradores.
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Instrucciones para Gmail -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Instrucciones para Configurar Gmail
                    </h3>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <div class="prose max-w-none">
                        <p>Para usar Gmail como servidor de correo, sigue estos pasos:</p>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li>Activa la verificación en dos pasos en tu cuenta de Google.</li>
                            <li>Ve a <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-blue-600 hover:underline">Contraseñas de aplicaciones</a>.</li>
                            <li>Selecciona "Otra" en el menú desplegable y dale un nombre (por ejemplo, "Reservas Restaurante").</li>
                            <li>Haz clic en "Generar" y Google te proporcionará una contraseña de 16 caracteres.</li>
                            <li>Copia esa contraseña y pégala en el campo "Contraseña" de este formulario.</li>
                        </ol>
                        <p class="mt-4 text-sm text-red-600">
                            <strong>Importante:</strong> Nunca compartas esta contraseña. Se almacena en la base de datos para poder enviar correos automáticamente.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes. Todos los derechos reservados.
            </p>
        </div>
    </footer>

    <script>
        // Mostrar/ocultar contraseña
        document.getElementById('toggle_password').addEventListener('click', function() {
            const passwordInput = document.getElementById('email_password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar el ícono
            const eyeIcon = this.querySelector('svg');
            if (type === 'text') {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    </script>
</body>
</html>
