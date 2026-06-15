<?php
// Script para actualizar el campo personas_solicitadas en reservas existentes

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<h1>Actualización del campo personas_solicitadas</h1>";
    
    // 1. Verificar si la columna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'personas_solicitadas'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if (!$columna_existe) {
        echo "<p style='color:red'>❌ La columna 'personas_solicitadas' NO existe en la tabla 'reservas'.</p>";
        echo "<p>Creando la columna...</p>";
        
        try {
            $pdo->exec("ALTER TABLE reservas ADD COLUMN personas_solicitadas INT DEFAULT 0 AFTER cantidad_personas");
            echo "<p style='color:green'>✅ Se ha creado la columna 'personas_solicitadas'.</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>❌ Error al crear la columna: " . $e->getMessage() . "</p>";
            exit;
        }
    } else {
        echo "<p style='color:green'>✅ La columna 'personas_solicitadas' existe en la tabla 'reservas'.</p>";
    }
    
    // 2. Buscar todas las reservas pendientes
    $stmt = $pdo->query("SELECT id, cantidad_personas, personas_solicitadas, estado, observaciones FROM reservas WHERE estado = 'pendiente'");
    $reservas_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Reservas pendientes encontradas: " . count($reservas_pendientes) . "</h2>";
    
    // 3. Actualizar las reservas pendientes
    $actualizadas = 0;
    
    foreach ($reservas_pendientes as $reserva) {
        $id = $reserva['id'];
        $personas_solicitadas = $reserva['personas_solicitadas'];
        $observaciones = $reserva['observaciones'];
        
        // Si personas_solicitadas es 0, intentar extraer el número de las observaciones
        if ($personas_solicitadas == 0) {
            $num_real = 0;
            
            // Buscar en las observaciones
            if (preg_match('/Personas solicitadas: (\d+)/', $observaciones, $matches)) {
                $num_real = (int)$matches[1];
            }
            
            // Si encontramos un número, actualizar el campo personas_solicitadas
            if ($num_real > 0) {
                $stmt = $pdo->prepare("UPDATE reservas SET personas_solicitadas = ? WHERE id = ?");
                if ($stmt->execute([$num_real, $id])) {
                    echo "<p>✅ Reserva #$id actualizada: personas_solicitadas = $num_real</p>";
                    $actualizadas++;
                } else {
                    echo "<p style='color:red'>❌ Error al actualizar la reserva #$id</p>";
                }
            } else {
                // Si no hay información en las observaciones, usar un valor predeterminado (por ejemplo, 4)
                $num_predeterminado = 4;
                $stmt = $pdo->prepare("UPDATE reservas SET personas_solicitadas = ? WHERE id = ?");
                if ($stmt->execute([$num_predeterminado, $id])) {
                    echo "<p>⚠️ Reserva #$id actualizada con valor predeterminado: personas_solicitadas = $num_predeterminado</p>";
                    $actualizadas++;
                } else {
                    echo "<p style='color:red'>❌ Error al actualizar la reserva #$id</p>";
                }
            }
        }
    }
    
    echo "<h2>Resumen</h2>";
    echo "<p>Total de reservas pendientes: " . count($reservas_pendientes) . "</p>";
    echo "<p>Reservas actualizadas: $actualizadas</p>";
    
    // 4. Verificar que todas las reservas pendientes tengan un valor en personas_solicitadas
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente' AND (personas_solicitadas IS NULL OR personas_solicitadas = 0)");
    $pendientes_sin_valor = $stmt->fetchColumn();
    
    if ($pendientes_sin_valor > 0) {
        echo "<p style='color:orange'>⚠️ Aún hay $pendientes_sin_valor reservas pendientes sin un valor en personas_solicitadas.</p>";
    } else {
        echo "<p style='color:green'>✅ Todas las reservas pendientes tienen un valor en personas_solicitadas.</p>";
    }
    
    // 5. Verificar que el campo se esté utilizando correctamente en el panel de administración
    echo "<h2>Verificación final</h2>";
    echo "<p>Para asegurarse de que el panel de administración esté utilizando correctamente el campo personas_solicitadas:</p>";
    echo "<ol>";
    echo "<li>Vaya al panel de administración</li>";
    echo "<li>Verifique que las reservas pendientes muestren el número correcto de personas</li>";
    echo "<li>Intente confirmar una reserva pendiente y verifique que se actualice correctamente la cantidad de personas</li>";
    echo "</ol>";
    
    echo "<p><a href='admin/reservas.php' style='color:blue'>Ir al panel de administración</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
