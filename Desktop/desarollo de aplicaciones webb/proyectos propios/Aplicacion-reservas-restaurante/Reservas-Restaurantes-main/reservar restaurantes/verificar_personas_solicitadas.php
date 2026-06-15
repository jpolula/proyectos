<?php
// Script para verificar el funcionamiento del campo personas_solicitadas

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<h1>Verificación del campo personas_solicitadas</h1>";
    
    // 1. Verificar si la columna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'personas_solicitadas'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if ($columna_existe) {
        echo "<p style='color:green'>✅ La columna 'personas_solicitadas' existe en la tabla 'reservas'.</p>";
        
        // Mostrar información de la columna
        $columna = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Información de la columna: " . print_r($columna, true) . "</p>";
    } else {
        echo "<p style='color:red'>❌ La columna 'personas_solicitadas' NO existe en la tabla 'reservas'.</p>";
        
        // Intentar crearla
        echo "<p>Intentando crear la columna...</p>";
        try {
            $pdo->exec("ALTER TABLE reservas ADD COLUMN personas_solicitadas INT DEFAULT 0 AFTER cantidad_personas");
            echo "<p style='color:green'>✅ Se ha creado la columna 'personas_solicitadas'.</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>❌ Error al crear la columna: " . $e->getMessage() . "</p>";
        }
    }
    
    // 2. Verificar reservas pendientes
    $stmt = $pdo->query("SELECT id, cantidad_personas, personas_solicitadas, estado, observaciones FROM reservas WHERE estado = 'pendiente'");
    $reservas_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Reservas pendientes encontradas: " . count($reservas_pendientes) . "</h2>";
    
    if (count($reservas_pendientes) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Cantidad Personas</th><th>Personas Solicitadas</th><th>Estado</th><th>Observaciones</th><th>Acciones</th></tr>";
        
        foreach ($reservas_pendientes as $reserva) {
            echo "<tr>";
            echo "<td>" . $reserva['id'] . "</td>";
            echo "<td>" . $reserva['cantidad_personas'] . "</td>";
            echo "<td>" . $reserva['personas_solicitadas'] . "</td>";
            echo "<td>" . $reserva['estado'] . "</td>";
            echo "<td>" . htmlspecialchars($reserva['observaciones']) . "</td>";
            echo "<td>";
            
            // Si personas_solicitadas es 0 pero hay un número en las observaciones, ofrecer arreglarlo
            if ($reserva['personas_solicitadas'] == 0 && preg_match('/Personas solicitadas: (\d+)/', $reserva['observaciones'], $matches)) {
                $num_real = $matches[1];
                echo "<a href='?accion=arreglar&id=" . $reserva['id'] . "&num=" . $num_real . "' style='color:blue'>Arreglar (establecer a " . $num_real . ")</a>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // 3. Procesar acciones
    if (isset($_GET['accion']) && $_GET['accion'] == 'arreglar' && isset($_GET['id']) && isset($_GET['num'])) {
        $id = (int)$_GET['id'];
        $num = (int)$_GET['num'];
        
        $stmt = $pdo->prepare("UPDATE reservas SET personas_solicitadas = ? WHERE id = ?");
        if ($stmt->execute([$num, $id])) {
            echo "<p style='color:green'>✅ Se ha actualizado la reserva #$id con $num personas solicitadas.</p>";
            echo "<p><a href='verificar_personas_solicitadas.php'>Actualizar página</a></p>";
        } else {
            echo "<p style='color:red'>❌ Error al actualizar la reserva.</p>";
        }
    }
    
    // 4. Verificar si hay alguna reserva con personas_solicitadas > 0
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE personas_solicitadas > 0");
    $count = $stmt->fetchColumn();
    
    echo "<h2>Estadísticas</h2>";
    echo "<p>Reservas con personas_solicitadas > 0: $count</p>";
    
    // 5. Mostrar la estructura de la tabla reservas
    echo "<h2>Estructura de la tabla reservas</h2>";
    $stmt = $pdo->query("DESCRIBE reservas");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
