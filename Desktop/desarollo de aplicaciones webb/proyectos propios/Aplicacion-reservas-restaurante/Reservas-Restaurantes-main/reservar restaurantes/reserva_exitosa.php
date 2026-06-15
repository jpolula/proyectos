<?php
// Iniciar sesión para acceder a las variables de sesión
session_start();

// Verificar si se viene de una reserva exitosa
$mostrar_confirmacion = isset($_SESSION["reserva_exitosa"]) && $_SESSION["reserva_exitosa"] === true;

// Obtener el estado de la reserva (confirmada o pendiente)
$estado_reserva = isset($_SESSION["estado_reserva"]) ? $_SESSION["estado_reserva"] : "confirmada";
$reserva_pendiente = ($estado_reserva === "pendiente");

// Obtener la URL de inicio configurada (por defecto index.php)
$url_inicio = isset($_SESSION["url_inicio"]) && !empty($_SESSION["url_inicio"]) ? $_SESSION["url_inicio"] : 'index.php';

// Obtener la configuración de confeti de la base de datos
// Por defecto, mostrar el confeti (para asegurar compatibilidad con versiones anteriores)
$mostrar_confeti = true;
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la columna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'mostrar_confeti'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->query("SELECT mostrar_confeti FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        // Solo desactivar si explícitamente es 0
        if (isset($config['mostrar_confeti']) && $config['mostrar_confeti'] == 0) {
            $mostrar_confeti = false;
        }
    }
    
    // Si la columna no existe, mantener el valor predeterminado (true)
} catch (PDOException $e) {
    // Si hay un error, por defecto mostrar confeti
    error_log("Error al verificar configuración de confeti: " . $e->getMessage());
}

// Limpiar las variables de sesión después de usarlas
if (isset($_SESSION["reserva_exitosa"])) {
    unset($_SESSION["reserva_exitosa"]);
}

// Limpiar el estado de la reserva
if (isset($_SESSION["estado_reserva"])) {
    unset($_SESSION["estado_reserva"]);
}

// No limpiamos url_inicio para que se mantenga disponible en caso de que el usuario haga otra reserva
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Exitosa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <?php include_once 'includes/header.php'; ?>
    <style>
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: #f00;
            animation: confetti-fall 3s ease-in-out infinite;
            z-index: 1000;
        }
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Confeti animado (solo se muestra si viene de una reserva exitosa y está activado en la configuración) -->
    <?php if ($mostrar_confirmacion && $mostrar_confeti): ?>
    <div id="confetti-container"></div>
    <?php endif; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center text-center mb-4">
            <div class="mb-4" style="max-width: 250px; margin: 0 auto;">
                <?php echo obtener_logo('w-full h-auto'); ?>
            </div>
        </div>
        
        <!-- Encabezado -->
        <header class="text-white rounded-lg shadow-lg p-6 mb-8 text-center" style="background-color: <?php echo $reserva_pendiente ? 'var(--color-secondary)' : 'var(--color-primary)'; ?>">
            <p class="text-2xl font-bold"><?php echo $reserva_pendiente ? 'Su reserva está pendiente de confirmación' : '¡Reserva Confirmada!'; ?></p>
            <p style="color: rgba(255,255,255,0.8);">Gracias por confiar en nosotros</p>
        </header>
        
        <main class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8 mb-8 text-center">
                <?php if ($mostrar_confirmacion): ?>
                    <!-- Mensaje de confirmación -->
                    <div class="mb-8">
                        <?php if ($reserva_pendiente): ?>
                        <!-- Icono y mensaje para reserva pendiente -->
                        <div class="mx-auto w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-clock text-5xl text-yellow-500"></i>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Su reserva requiere confirmación por parte de administración</h2>
                        <p class="text-gray-600 mb-6">
                            Hemos recibido su solicitud de reserva. Debido al número de personas, necesitamos verificar la disponibilidad.
                            Le enviaremos un correo electrónico cuando su reserva sea confirmada.
                        </p>
                        <?php else: ?>
                        <!-- Icono y mensaje para reserva confirmada -->
                        <div class="mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-check text-5xl text-green-500"></i>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">¡Su reserva ha sido registrada con éxito!</h2>
                        <p class="text-gray-600 mb-6">
                            Hemos enviado un correo electrónico con los detalles de su reserva.
                            Si no lo recibe en los próximos minutos, por favor revise su carpeta de spam.
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="p-6 rounded-lg mb-8" style="background-color: var(--color-primary-light);">
                        <h3 class="text-lg font-medium mb-3" style="color: white;">Información importante</h3>
                        <ul class="text-left space-y-2" style="color: white;">
                            <li><i class="fas fa-envelope mr-2"></i> Los detalles de su reserva le llegarán por correo electrónico en los próximos minutos.</li>
                            <li><i class="fas fa-exclamation-triangle mr-2"></i> Si no encuentra el correo, revise su carpeta de spam o correo no deseado.</li>
                            <li><i class="fas fa-phone mr-2"></i> En caso de no recibir el correo ni en spam, por favor llame al restaurante para confirmar su reserva.</li>
                            <li><i class="fas fa-clock mr-2"></i> Llegue puntual a la hora reservada.</li>
                            <li><i class="fas fa-calendar-times mr-2"></i> Si necesita cancelar o modificar su reserva, contáctenos con al menos 24 horas de antelación.</li>
                            <?php if ($reserva_pendiente): ?>
                            <li><i class="fas fa-check-circle mr-2"></i> Su reserva requiere aprobación, recibirá un correo de confirmación cuando sea aprobada.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Mensaje para accesos directos a esta página -->
                    <div class="mb-8">
                        <div class="mx-auto w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-exclamation text-5xl text-yellow-500"></i>
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">No hay reserva en proceso</h2>
                        <p class="text-gray-600 mb-6">
                            Parece que ha accedido directamente a esta página sin completar el proceso de reserva.
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="<?php echo htmlspecialchars($url_inicio); ?>" class="text-white px-6 py-3 rounded-md text-base font-medium transition duration-300" style="background-color: var(--color-primary);">
                        <i class="fas fa-home mr-2"></i> Volver al inicio
                    </a>
                    <a href="reserva.php" class="text-white px-6 py-3 rounded-md text-base font-medium transition duration-300" style="background-color: var(--color-secondary);">
                        <i class="fas fa-calendar-plus mr-2"></i> Hacer otra reserva
                    </a>
                </div>
            </div>
        </main>
        
        <!-- Pie de página -->
        <footer class="mt-12 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
            <p class="mt-2">Realizada con ❤️ por <a href="https://impulsatelecom.com/" target="_blank" class="hover:text-gray-700 transition-colors duration-300">Impulsa Telecom</a></p>
        </footer>
    </div>

    <?php if ($mostrar_confirmacion && $mostrar_confeti): ?>
    <script>
        // Crear confeti animado
        document.addEventListener('DOMContentLoaded', function() {
            const confettiContainer = document.getElementById('confetti-container');
            const colors = ['#f00', '#0f0', '#00f', '#ff0', '#f0f', '#0ff'];
            
            // Crear 100 piezas de confeti
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.width = Math.random() * 10 + 5 + 'px';
                confetti.style.height = Math.random() * 10 + 5 + 'px';
                confetti.style.animationDuration = Math.random() * 3 + 2 + 's';
                confetti.style.animationDelay = Math.random() * 5 + 's';
                
                confettiContainer.appendChild(confetti);
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
