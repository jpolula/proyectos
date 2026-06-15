<?php
// Script para insertar los sábados de mayo 2025 como días disponibles

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
    
    // Obtener IDs de los turnos
    $stmt = $pdo->query("SELECT id FROM turnos");
    $turnos = $stmt->fetchAll();
    
    if (empty($turnos)) {
        die("No se encontraron turnos en la base de datos. Asegúrate de haber ejecutado el script de creación de la base de datos.");
    }
    
    // Sábados de mayo 2025
    $sabados = [
        '2025-05-03',
        '2025-05-10',
        '2025-05-17',
        '2025-05-24',
        '2025-05-31'
    ];
    
    // Zonas disponibles
    $zonas = ['dentro', 'fuera'];
    
    // Preparar la consulta de inserción
    $stmt = $pdo->prepare("
        INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible) 
        VALUES (:fecha, :zona, :turno_id, TRUE)
        ON DUPLICATE KEY UPDATE disponible = TRUE
    ");
    
    // Contador de inserciones
    $insertados = 0;
    
    // Insertar cada combinación de sábado, zona y turno
    foreach ($sabados as $sabado) {
        foreach ($zonas as $zona) {
            foreach ($turnos as $turno) {
                $stmt->execute([
                    'fecha' => $sabado,
                    'zona' => $zona,
                    'turno_id' => $turno['id']
                ]);
                $insertados++;
            }
        }
    }
    
    echo "<h1>Inserción completada</h1>";
    echo "<p>Se han insertado o actualizado $insertados registros para los sábados de mayo 2025.</p>";
    echo "<p>Todos los sábados de mayo ahora están disponibles para ambos turnos y zonas.</p>";
    echo "<p><a href='index.php'>Volver al formulario de reservas</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Ha ocurrido un error al insertar los datos: " . $e->getMessage() . "</p>";
}
?>
