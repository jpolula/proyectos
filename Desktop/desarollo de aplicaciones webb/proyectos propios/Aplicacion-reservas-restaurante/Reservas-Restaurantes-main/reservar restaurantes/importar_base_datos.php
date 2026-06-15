<?php
// Script para importar la base de datos

// Cargar el autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Inicializar la configuración
App\Config\Config::init();

// Importar la clase DatabaseManager
use App\Utils\DatabaseManager;

// Crear instancia del gestor de base de datos
$dbManager = new DatabaseManager();

// Ruta al archivo SQL
$scriptPath = __DIR__ . '/database.sql';

// Ejecutar el script SQL
$result = $dbManager->executeScript($scriptPath);

// Mostrar resultado
if ($result['success']) {
    echo "<h1>Base de datos importada correctamente</h1>";
    echo "<p>La base de datos <strong>" . $dbManager->getDatabaseName() . "</strong> ha sido configurada con éxito.</p>";
    echo "<p>Ya puedes comenzar a utilizar el sistema de reservas.</p>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";
} else {
    echo "<h1>Error al importar la base de datos</h1>";
    echo "<p>" . $result['message'] . "</p>";
    
    if (!empty($result['errors'])) {
        echo "<h2>Detalles del error:</h2>";
        echo "<ul>";
        foreach ($result['errors'] as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    
    echo "<p><a href='index.php'>Volver al inicio</a></p>";
}
?>
