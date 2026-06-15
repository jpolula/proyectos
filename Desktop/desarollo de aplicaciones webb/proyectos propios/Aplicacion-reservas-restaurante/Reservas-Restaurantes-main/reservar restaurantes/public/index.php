<?php
// Punto de entrada principal de la aplicación

// Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Inicializar la configuración
App\Config\Config::init();

// Definir constantes útiles
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Aquí se implementará el enrutamiento de la aplicación
// Por ahora, simplemente mostramos una página de inicio básica

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas de Restaurantes</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Sistema de Reservas de Restaurantes</h1>
    </header>
    
    <main>
        <section class="welcome">
            <h2>Bienvenido al Sistema de Reservas</h2>
            <p>Esta aplicación te permitirá gestionar reservas de restaurantes de manera eficiente.</p>
        </section>
    </main>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>
