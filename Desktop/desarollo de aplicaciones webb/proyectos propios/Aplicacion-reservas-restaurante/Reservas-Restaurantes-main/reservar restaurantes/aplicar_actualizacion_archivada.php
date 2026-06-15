<?php
/**
 * Script para aplicar la actualización de la base de datos
 * y añadir el estado 'archivada' a la tabla de reservas
 */

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

try {
    // Conexión a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si la columna estado ya incluye 'archivada'
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'estado'");
    $columna = $stmt->fetch();
    
    $ya_actualizado = false;
    if ($columna && strpos($columna['Type'], 'archivada') !== false) {
        $ya_actualizado = true;
    }
    
    if (!$ya_actualizado) {
        // Modificar la tabla de reservas para añadir el estado 'archivada'
        $sql = "ALTER TABLE reservas 
                MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'rechazada', 'cancelada', 'archivada') DEFAULT 'pendiente'";
        $pdo->exec($sql);
        
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;'>";
        echo "<h3>Actualización completada</h3>";
        echo "<p>Se ha añadido el estado 'archivada' a la tabla de reservas.</p>";
        echo "<p>Ahora las reservas de días pasados se archivarán automáticamente en lugar de eliminarse.</p>";
        echo "<p>Puedes ver las reservas archivadas en la sección 'Historial de Reservas' del panel de administración.</p>";
        echo "<p><a href='admin/index.php'>Volver al panel de administración</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px;'>";
        echo "<h3>Actualización no necesaria</h3>";
        echo "<p>La tabla de reservas ya incluye el estado 'archivada'.</p>";
        echo "<p>No se requieren cambios adicionales.</p>";
        echo "<p><a href='admin/index.php'>Volver al panel de administración</a></p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
    echo "<h3>Error durante la actualización</h3>";
    echo "<p>Se ha producido un error al actualizar la base de datos: " . $e->getMessage() . "</p>";
    echo "<p><a href='admin/index.php'>Volver al panel de administración</a></p>";
    echo "</div>";
}
?>
