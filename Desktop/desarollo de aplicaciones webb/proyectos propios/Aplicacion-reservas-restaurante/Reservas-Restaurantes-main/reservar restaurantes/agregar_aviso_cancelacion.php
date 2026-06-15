<?php
// Script para añadir un aviso sobre política de cancelación

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
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Texto del aviso sobre política de cancelación
    $aviso = 'IMPORTANTE: Si no puede asistir a su reserva, le rogamos que nos lo comunique con al menos 24 horas de antelación. En caso de cancelación tardía o no presentarse, podríamos aplicar un cargo.';
    
    // Comprobar si ya existe un aviso similar
    $stmt = $pdo->prepare("SELECT id FROM avisos_reserva WHERE texto LIKE ?");
    $stmt->execute(['%cancelación%']);
    $existe = $stmt->fetch();
    
    if ($existe) {
        // Actualizar el aviso existente
        $stmt = $pdo->prepare("UPDATE avisos_reserva SET texto = ?, activo = 1 WHERE id = ?");
        $stmt->execute([$aviso, $existe['id']]);
        echo "<h2>Aviso sobre política de cancelación actualizado correctamente.</h2>";
    } else {
        // Obtener el orden máximo actual
        $stmt = $pdo->query("SELECT MAX(orden) as max_orden FROM avisos_reserva");
        $resultado = $stmt->fetch();
        $orden = ($resultado['max_orden'] ?? 0) + 1;
        
        // Insertar el nuevo aviso
        $stmt = $pdo->prepare("INSERT INTO avisos_reserva (texto, orden, activo) VALUES (?, ?, 1)");
        $stmt->execute([$aviso, $orden]);
        echo "<h2>Aviso sobre política de cancelación añadido correctamente.</h2>";
    }
    
    echo "<p><a href='admin/avisos.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Ir a gestión de avisos</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>No se pudo añadir el aviso: " . $e->getMessage() . "</p>";
}
?>
