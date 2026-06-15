<?php
// Actualizar tabla de configuración para añadir campos de textos personalizables
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si las columnas ya existen
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'titulo_principal'");
    $tituloPrincipalExists = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'subtitulo'");
    $subtituloExists = $stmt->fetch();
    
    // Añadir columnas si no existen
    if (!$tituloPrincipalExists) {
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN titulo_principal VARCHAR(255) DEFAULT 'Sistema de Reservas de Restaurantes'");
        echo "Columna titulo_principal añadida correctamente.<br>";
    }
    
    if (!$subtituloExists) {
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN subtitulo VARCHAR(255) DEFAULT 'Introduce tus datos para comenzar la reserva'");
        echo "Columna subtitulo añadida correctamente.<br>";
    }
    
    echo "Actualización de la tabla completada.";
    
} catch (PDOException $e) {
    echo "Error al actualizar la tabla: " . $e->getMessage();
}
?>
