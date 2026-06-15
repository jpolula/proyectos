<?php
// Página de confirmación de reserva
session_start();

// Verificar si hay datos de usuario y reserva en la sesión
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario']) || !isset($_SESSION['reserva']) || empty($_SESSION['reserva'])) {
    // Si no hay datos completos, redirigir al inicio
    header('Location: index.php');
    exit;
}

// Obtener los datos del usuario y la reserva
$usuario = $_SESSION['usuario'];
$reserva = $_SESSION['reserva'];

// Formatear la fecha para mostrarla
$fecha_obj = DateTime::createFromFormat('d/m/Y', $reserva['fecha']);
$fecha_formateada = $fecha_obj ? $fecha_obj->format('d \d\e F \d\e Y') : $reserva['fecha'];
$fecha_mysql = $fecha_obj ? $fecha_obj->format('Y-m-d') : date('Y-m-d');

// Traducir nombres de turnos y zonas
$turnos = [
    'mediodia' => 'Mediodía',
    'noche' => 'Noche'
];

$zonas = [
    'dentro' => 'Interior',
    'fuera' => 'Terraza'
];

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

$reserva_guardada = false;
$numero_reserva = 'R' . date('Ymd') . rand(1000, 9999);
$mensaje_error = '';
$requiere_confirmacion = false;

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener el número máximo de personas sin aprobación
    $stmt = $pdo->query("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
    $max_personas_sin_aprobacion = $config ? $config['max_personas_sin_aprobacion'] : 4;
    
    // Determinar si la reserva requiere confirmación
    $requiere_confirmacion = $usuario['num_personas'] > $max_personas_sin_aprobacion;
    $estado_reserva = $requiere_confirmacion ? 'pendiente' : 'confirmada';
    
    // Verificar si el cliente ya existe
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$usuario['email']]);
    $cliente = $stmt->fetch();
    
    $cliente_id = null;
    
    if ($cliente) {
        // Actualizar cliente existente
        $cliente_id = $cliente['id'];
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET nombre = ?, telefono = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $usuario['nombre'],
            $usuario['telefono'],
            $cliente_id
        ]);
    } else {
        // Insertar nuevo cliente
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, email, telefono) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $usuario['nombre'],
            $usuario['email'],
            $usuario['telefono']
        ]);
        $cliente_id = $pdo->lastInsertId();
    }
    
    // Obtener el ID del turno
    $stmt = $pdo->prepare("SELECT id FROM turnos WHERE nombre = ?");
    $stmt->execute([$reserva['turno']]);
    $turno = $stmt->fetch();
    $turno_id = $turno ? $turno['id'] : null;
    
    if (!$turno_id) {
        throw new Exception("Turno no válido");
    }
    
    // Insertar la reserva
    $stmt = $pdo->prepare("
        INSERT INTO reservas (
            cliente_id, fecha, zona, turno_id, hora, 
            cantidad_personas, tiene_alergenos, necesidades_especiales, estado
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");
    
    $stmt->execute([
        $cliente_id,
        $fecha_mysql,
        $reserva['zona'],
        $turno_id,
        $reserva['hora'],
        $usuario['num_personas'],
        $usuario['tiene_alergenos'] ? 1 : 0,
        $usuario['necesidades_especiales'] ?? '',
        $estado_reserva
    ]);
    
    $reserva_id = $pdo->lastInsertId();
    $reserva_guardada = true;
    
} catch (Exception $e) {
    $mensaje_error = "Error al guardar la reserva: " . $e->getMessage();
    error_log($mensaje_error);
}

// Si se hace clic en "Volver al inicio"
if (isset($_POST['volver_inicio'])) {
    // Limpiar la sesión
    session_unset();
    session_destroy();
    
    // Redirigir al inicio
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva - Sistema de Reservas de Restaurantes</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .confirmation-box {
            border: 2px dashed #4ade80;
            background-color: #f0fdf4;
        }
        
        .pending-box {
            border: 2px dashed #facc15;
            background-color: #fefce8;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="bg-blue-600 text-white rounded-lg shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold">Sistema de Reservas de Restaurantes</h1>
            <p class="mt-2">Confirmación de tu reserva</p>
        </header>

        <main class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <?php if ($reserva_guardada): ?>
                    <div class="text-center mb-8">
                        <?php if ($requiere_confirmacion): ?>
                            <div class="inline-block p-3 rounded-full bg-yellow-100 text-yellow-600 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">Reserva Pendiente de Confirmación</h2>
                            <p class="text-gray-600 mb-4">Hemos recibido tu solicitud de reserva. Debido al número de personas, necesitamos revisarla manualmente.</p>
                            <p class="text-gray-600 mb-4">Te hemos enviado un correo electrónico con los detalles. Te notificaremos cuando tu reserva sea confirmada.</p>
                            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
                                <p class="font-medium">⚠️ Importante:</p>
                                <p>Por favor, revisa también tu carpeta de spam o correo no deseado, ya que en ocasiones nuestros correos pueden ser filtrados por tu proveedor de correo electrónico.</p>
                            </div>
                            <p class="text-sm text-yellow-600 mt-2">Debido a que has reservado para <?php echo $usuario['num_personas']; ?> personas, la reserva requiere confirmación por parte del restaurante.</p>
                            <p class="text-sm text-yellow-600">Te contactaremos pronto para confirmar tu reserva.</p>
                        <?php else: ?>
                            <div class="inline-block p-3 rounded-full bg-green-100 text-green-600 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">¡Reserva Confirmada!</h2>
                            <p class="text-gray-600 mb-4">Tu reserva ha sido confirmada correctamente. Te hemos enviado un correo electrónico con los detalles.</p>
                            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-4">
                                <p class="font-medium">⚠️ Importante:</p>
                                <p>Por favor, revisa también tu carpeta de spam o correo no deseado, ya que en ocasiones nuestros correos pueden ser filtrados por tu proveedor de correo electrónico.</p>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Número de reserva: <span class="font-semibold"><?php echo $numero_reserva; ?></span></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="<?php echo $requiere_confirmacion ? 'pending-box' : 'confirmation-box'; ?> p-6 rounded-lg mb-8">
                        <h3 class="text-xl font-semibold <?php echo $requiere_confirmacion ? 'text-yellow-800' : 'text-green-800'; ?> mb-4">Detalles de la Reserva</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-lg font-medium text-gray-700 mb-2">Datos del Cliente</h4>
                                <ul class="space-y-2 text-gray-600">
                                    <li><span class="font-medium">Nombre:</span> <?php echo htmlspecialchars($usuario['nombre']); ?></li>
                                    <li><span class="font-medium">Email:</span> <?php echo htmlspecialchars($usuario['email']); ?></li>
                                    <li><span class="font-medium">Teléfono:</span> <?php echo htmlspecialchars($usuario['telefono']); ?></li>
                                    <li><span class="font-medium">Personas:</span> <?php echo $usuario['num_personas']; ?></li>
                                    <?php if (!empty($usuario['necesidades_especiales'])): ?>
                                    <li><span class="font-medium">Necesidades especiales:</span> <?php echo nl2br(htmlspecialchars($usuario['necesidades_especiales'])); ?></li>
                                    <?php endif; ?>
                                    <?php if (isset($usuario['tiene_alergenos']) && $usuario['tiene_alergenos']): ?>
                                    <li><span class="font-medium text-amber-600">Alérgenos:</span> <span class="text-amber-600">Sí, hay alérgenos o intolerancias</span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-lg font-medium text-gray-700 mb-2">Datos de la Reserva</h4>
                                <ul class="space-y-2 text-gray-600">
                                    <li><span class="font-medium">Fecha:</span> <?php echo $fecha_formateada; ?></li>
                                    <li><span class="font-medium">Hora:</span> <?php echo htmlspecialchars($reserva['hora']); ?></li>
                                    <li><span class="font-medium">Turno:</span> <?php echo isset($turnos[$reserva['turno']]) ? $turnos[$reserva['turno']] : $reserva['turno']; ?></li>
                                    <li><span class="font-medium">Zona:</span> <?php echo isset($zonas[$reserva['zona']]) ? $zonas[$reserva['zona']] : $reserva['zona']; ?></li>
                                    <li><span class="font-medium">Estado:</span> 
                                        <span class="<?php echo $requiere_confirmacion ? 'text-yellow-600' : 'text-green-600'; ?> font-medium">
                                            <?php echo $requiere_confirmacion ? 'Pendiente de confirmación' : 'Confirmada'; ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-6">
                        <h4 class="text-lg font-medium text-blue-800 mb-2">Información Importante</h4>
                        <ul class="list-disc pl-5 text-blue-700 space-y-1">
                            <li>Por favor, llega con 5-10 minutos de antelación.</li>
                            <li>La reserva se mantendrá hasta 15 minutos después de la hora reservada.</li>
                            <li>Para cancelaciones, por favor llama con al menos 2 horas de antelación.</li>
                            <?php if ($requiere_confirmacion): ?>
                            <li class="font-medium">Recibirás un correo electrónico o llamada para confirmar tu reserva.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="text-center">
                        <a href="index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-lg transition duration-300">
                            Volver al Inicio
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center mb-8">
                        <div class="inline-block p-3 rounded-full bg-red-100 text-red-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Error en la Reserva</h2>
                        <p class="text-gray-600"><?php echo !empty($mensaje_error) ? $mensaje_error : 'Ha ocurrido un error al procesar tu reserva. Por favor, inténtalo de nuevo.'; ?></p>
                    </div>
                    
                    <div class="text-center">
                        <a href="reserva.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-lg transition duration-300">
                            Volver al Formulario de Reserva
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <footer class="text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
        </footer>
    </div>
</body>
</html>
