<?php
// Script para diagnosticar y corregir problemas de capacidad
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Función para mostrar mensajes de éxito
function mostrarExito($mensaje) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>{$mensaje}</div>";
}

// Función para mostrar errores
function mostrarError($mensaje) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>{$mensaje}</div>";
}

// Función para mostrar advertencias
function mostrarAdvertencia($mensaje) {
    echo "<div style='background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>{$mensaje}</div>";
}

// Función para mostrar información
function mostrarInfo($mensaje) {
    echo "<div style='background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>{$mensaje}</div>";
}

// HTML inicial
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Diagnóstico de Capacidad</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .code {
            font-family: monospace;
            background-color: #f5f5f5;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Diagnóstico de Capacidad</h1>";

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si se ha solicitado aplicar correcciones
    $aplicar_correcciones = isset($_POST['aplicar_correcciones']) && $_POST['aplicar_correcciones'] === '1';
    $fecha_test = isset($_POST['fecha_test']) ? $_POST['fecha_test'] : date('Y-m-d');
    $zona_test = isset($_POST['zona_test']) ? $_POST['zona_test'] : 'dentro';
    $turno_test = isset($_POST['turno_test']) ? (int)$_POST['turno_test'] : 1;
    $personas_test = isset($_POST['personas_test']) ? (int)$_POST['personas_test'] : 2;
    
    // Formulario para probar la disponibilidad
    echo "<h2>Probar Disponibilidad</h2>";
    echo "<form method='post' class='mb-4'>";
    echo "<div style='display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 15px;'>";
    
    // Campo para la fecha
    echo "<div style='flex: 1; min-width: 200px;'>";
    echo "<label style='display: block; margin-bottom: 5px;'>Fecha:</label>";
    echo "<input type='date' name='fecha_test' value='{$fecha_test}' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "</div>";
    
    // Campo para la zona
    echo "<div style='flex: 1; min-width: 200px;'>";
    echo "<label style='display: block; margin-bottom: 5px;'>Zona:</label>";
    echo "<select name='zona_test' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "<option value='dentro'" . ($zona_test == 'dentro' ? ' selected' : '') . ">Interior</option>";
    echo "<option value='fuera'" . ($zona_test == 'fuera' ? ' selected' : '') . ">Terraza</option>";
    echo "</select>";
    echo "</div>";
    
    // Campo para el turno
    echo "<div style='flex: 1; min-width: 200px;'>";
    echo "<label style='display: block; margin-bottom: 5px;'>Turno:</label>";
    echo "<select name='turno_test' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    
    // Obtener turnos de la base de datos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
    $turnos = $stmt->fetchAll();
    
    foreach ($turnos as $turno) {
        $selected = ($turno['id'] == $turno_test) ? ' selected' : '';
        $nombre_turno = ($turno['nombre'] == 'mediodia') ? 'Mediodía' : ucfirst($turno['nombre']);
        echo "<option value='{$turno['id']}'{$selected}>{$nombre_turno}</option>";
    }
    
    echo "</select>";
    echo "</div>";
    
    // Campo para el número de personas
    echo "<div style='flex: 1; min-width: 200px;'>";
    echo "<label style='display: block; margin-bottom: 5px;'>Personas:</label>";
    echo "<input type='number' name='personas_test' value='{$personas_test}' min='1' max='20' style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<button type='submit' class='btn'>Verificar Disponibilidad</button>";
    echo "<input type='hidden' name='aplicar_correcciones' value='0'>";
    echo "</form>";
    
    // Si se ha enviado el formulario, verificar la disponibilidad
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h3>Resultados para: " . date('d/m/Y', strtotime($fecha_test)) . ", " . 
             ($zona_test == 'dentro' ? 'Interior' : 'Terraza') . ", " . 
             (isset($turnos[$turno_test-1]['nombre']) ? ucfirst($turnos[$turno_test-1]['nombre']) : "Turno $turno_test") . 
             ", {$personas_test} personas</h3>";
        
        // 1. Verificar si el día está en la tabla dias_disponibles
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM dias_disponibles WHERE fecha = ? AND turno_id = ? AND zona = ?");
        $stmt->execute([$fecha_test, $turno_test, $zona_test]);
        $existe_configuracion = $stmt->fetchColumn() > 0;
        
        if (!$existe_configuracion) {
            mostrarError("El día no está configurado en la tabla dias_disponibles.");
            
            // Opción para corregir
            echo "<form method='post' style='margin-bottom: 20px;'>";
            echo "<input type='hidden' name='fecha_test' value='{$fecha_test}'>";
            echo "<input type='hidden' name='zona_test' value='{$zona_test}'>";
            echo "<input type='hidden' name='turno_test' value='{$turno_test}'>";
            echo "<input type='hidden' name='personas_test' value='{$personas_test}'>";
            echo "<input type='hidden' name='aplicar_correcciones' value='1'>";
            echo "<input type='hidden' name='corregir_dia' value='1'>";
            echo "<button type='submit' class='btn'>Añadir día a la configuración</button>";
            echo "</form>";
            
            // Si se ha solicitado corregir
            if ($aplicar_correcciones && isset($_POST['corregir_dia'])) {
                $stmt = $pdo->prepare("INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) VALUES (?, ?, ?, 1)");
                $stmt->execute([$fecha_test, $turno_test, $zona_test]);
                mostrarExito("Se ha añadido el día a la configuración. Por favor, vuelve a verificar la disponibilidad.");
                $existe_configuracion = true;
            }
        } else {
            // Verificar si está marcado como disponible
            $stmt = $pdo->prepare("SELECT disponible FROM dias_disponibles WHERE fecha = ? AND turno_id = ? AND zona = ?");
            $stmt->execute([$fecha_test, $turno_test, $zona_test]);
            $disponible = $stmt->fetchColumn();
            
            if (!$disponible) {
                mostrarError("El día está configurado pero marcado como NO DISPONIBLE.");
                
                // Opción para corregir
                echo "<form method='post' style='margin-bottom: 20px;'>";
                echo "<input type='hidden' name='fecha_test' value='{$fecha_test}'>";
                echo "<input type='hidden' name='zona_test' value='{$zona_test}'>";
                echo "<input type='hidden' name='turno_test' value='{$turno_test}'>";
                echo "<input type='hidden' name='personas_test' value='{$personas_test}'>";
                echo "<input type='hidden' name='aplicar_correcciones' value='1'>";
                echo "<input type='hidden' name='marcar_disponible' value='1'>";
                echo "<button type='submit' class='btn'>Marcar día como disponible</button>";
                echo "</form>";
                
                // Si se ha solicitado corregir
                if ($aplicar_correcciones && isset($_POST['marcar_disponible'])) {
                    $stmt = $pdo->prepare("UPDATE dias_disponibles SET disponible = 1 WHERE fecha = ? AND turno_id = ? AND zona = ?");
                    $stmt->execute([$fecha_test, $turno_test, $zona_test]);
                    mostrarExito("Se ha marcado el día como disponible. Por favor, vuelve a verificar la disponibilidad.");
                    $disponible = true;
                }
            } else {
                mostrarExito("El día está correctamente configurado como DISPONIBLE.");
            }
        }
        
        // 2. Verificar la configuración de capacidad
        $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch();
        
        if (!$config) {
            mostrarError("No hay configuración de capacidad.");
            
            // Opción para corregir
            echo "<form method='post' style='margin-bottom: 20px;'>";
            echo "<input type='hidden' name='fecha_test' value='{$fecha_test}'>";
            echo "<input type='hidden' name='zona_test' value='{$zona_test}'>";
            echo "<input type='hidden' name='turno_test' value='{$turno_test}'>";
            echo "<input type='hidden' name='personas_test' value='{$personas_test}'>";
            echo "<input type='hidden' name='aplicar_correcciones' value='1'>";
            echo "<input type='hidden' name='crear_configuracion' value='1'>";
            echo "<button type='submit' class='btn'>Crear configuración por defecto</button>";
            echo "</form>";
            
            // Si se ha solicitado corregir
            if ($aplicar_correcciones && isset($_POST['crear_configuracion'])) {
                $stmt = $pdo->prepare("INSERT INTO configuracion (max_personas_sin_aprobacion, capacidad_dentro_mediodia, capacidad_dentro_noche, capacidad_fuera_mediodia, capacidad_fuera_noche) VALUES (6, 30, 35, 20, 25)");
                $stmt->execute();
                mostrarExito("Se ha creado la configuración por defecto. Por favor, vuelve a verificar la disponibilidad.");
                $config = [
                    'max_personas_sin_aprobacion' => 6,
                    'capacidad_dentro_mediodia' => 30,
                    'capacidad_dentro_noche' => 35,
                    'capacidad_fuera_mediodia' => 20,
                    'capacidad_fuera_noche' => 25
                ];
            }
        } else {
            mostrarExito("La configuración de capacidad existe.");
            
            // Mostrar la configuración
            echo "<h3>Configuración actual:</h3>";
            echo "<table>";
            echo "<tr><th>Parámetro</th><th>Valor</th></tr>";
            echo "<tr><td>Personas máximas sin aprobación</td><td>" . $config['max_personas_sin_aprobacion'] . "</td></tr>";
            echo "<tr><td>Capacidad interior mediodía</td><td>" . $config['capacidad_dentro_mediodia'] . "</td></tr>";
            echo "<tr><td>Capacidad terraza mediodía</td><td>" . $config['capacidad_fuera_mediodia'] . "</td></tr>";
            echo "<tr><td>Capacidad interior noche</td><td>" . $config['capacidad_dentro_noche'] . "</td></tr>";
            echo "<tr><td>Capacidad terraza noche</td><td>" . $config['capacidad_fuera_noche'] . "</td></tr>";
            echo "</table>";
        }
        
        // 3. Verificar las reservas existentes
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(cantidad_personas) as personas FROM reservas WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'");
        $stmt->execute([$fecha_test, $zona_test, $turno_test]);
        $reservas = $stmt->fetch();
        
        echo "<h3>Reservas confirmadas:</h3>";
        if ($reservas['total'] > 0) {
            echo "<p>Hay " . $reservas['total'] . " reservas confirmadas con un total de " . $reservas['personas'] . " personas.</p>";
            
            // Mostrar detalles de las reservas
            $stmt = $pdo->prepare("SELECT r.*, c.nombre, c.email FROM reservas r JOIN clientes c ON r.cliente_id = c.id WHERE r.fecha = ? AND r.zona = ? AND r.turno_id = ? AND r.estado = 'confirmada'");
            $stmt->execute([$fecha_test, $zona_test, $turno_test]);
            $detalles_reservas = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Email</th><th>Personas</th><th>Estado</th></tr>";
            foreach ($detalles_reservas as $reserva) {
                echo "<tr>";
                echo "<td>" . $reserva['id'] . "</td>";
                echo "<td>" . $reserva['nombre'] . "</td>";
                echo "<td>" . $reserva['email'] . "</td>";
                echo "<td>" . $reserva['cantidad_personas'] . "</td>";
                echo "<td>" . $reserva['estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay reservas confirmadas para esta fecha, zona y turno.</p>";
        }
        
        // 4. Verificar reservas pendientes
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(cantidad_personas) as personas FROM reservas WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'pendiente'");
        $stmt->execute([$fecha_test, $zona_test, $turno_test]);
        $pendientes = $stmt->fetch();
        
        echo "<h3>Reservas pendientes:</h3>";
        if ($pendientes['total'] > 0) {
            echo "<p>Hay " . $pendientes['total'] . " reservas pendientes con un total de " . $pendientes['personas'] . " personas.</p>";
            
            // Mostrar detalles de las reservas pendientes
            $stmt = $pdo->prepare("SELECT r.*, c.nombre, c.email FROM reservas r JOIN clientes c ON r.cliente_id = c.id WHERE r.fecha = ? AND r.zona = ? AND r.turno_id = ? AND r.estado = 'pendiente'");
            $stmt->execute([$fecha_test, $zona_test, $turno_test]);
            $detalles_pendientes = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Email</th><th>Personas</th><th>Estado</th></tr>";
            foreach ($detalles_pendientes as $reserva) {
                echo "<tr>";
                echo "<td>" . $reserva['id'] . "</td>";
                echo "<td>" . $reserva['nombre'] . "</td>";
                echo "<td>" . $reserva['email'] . "</td>";
                echo "<td>" . $reserva['cantidad_personas'] . "</td>";
                echo "<td>" . $reserva['estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay reservas pendientes para esta fecha, zona y turno.</p>";
        }
        
        // 5. Calcular disponibilidad
        if (isset($config) && $config) {
            // Determinar el campo de capacidad según turno y zona
            $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
            $stmt->execute([$turno_test]);
            $nombre_turno = $stmt->fetchColumn();
            
            $campo_capacidad = '';
            if ($nombre_turno == 'mediodia') {
                $campo_capacidad = ($zona_test == 'dentro') ? 'capacidad_dentro_mediodia' : 'capacidad_fuera_mediodia';
            } else {
                $campo_capacidad = ($zona_test == 'dentro') ? 'capacidad_dentro_noche' : 'capacidad_fuera_noche';
            }
            
            // Obtener el aforo máximo de la configuración
            $aforo_maximo = $config[$campo_capacidad] ?? (($zona_test == 'dentro') ? 30 : 20);
            
            // Calcular aforo disponible
            $total_reservado = $reservas['personas'] ?: 0;
            $aforo_disponible = $aforo_maximo - $total_reservado;
            
            echo "<h3>Cálculo de disponibilidad:</h3>";
            echo "<ul>";
            echo "<li>Aforo máximo: " . $aforo_maximo . "</li>";
            echo "<li>Total reservado: " . $total_reservado . "</li>";
            echo "<li>Aforo disponible: " . $aforo_disponible . "</li>";
            echo "</ul>";
            
            // Verificar si hay suficiente aforo para la reserva solicitada
            $max_personas_sin_aprobacion = $config['max_personas_sin_aprobacion'] ?? 6;
            $sera_confirmada = ($personas_test <= $max_personas_sin_aprobacion);
            
            echo "<h3>Resultado de la verificación:</h3>";
            
            if ($sera_confirmada) {
                if ($aforo_disponible >= $personas_test) {
                    mostrarExito("Hay suficiente aforo disponible para confirmar automáticamente la reserva de " . $personas_test . " personas.");
                } else {
                    mostrarError("No hay suficiente aforo disponible para confirmar automáticamente la reserva de " . $personas_test . " personas.");
                }
            } else {
                mostrarAdvertencia("La reserva de " . $personas_test . " personas requeriría aprobación del administrador porque supera el límite de " . $max_personas_sin_aprobacion . " personas sin aprobación.");
            }
        }
        
        // 6. Mostrar el código de verificación de disponibilidad
        echo "<h3>Código de verificación de disponibilidad:</h3>";
        echo "<p>Este es el código que se ejecuta para verificar la disponibilidad en <span class='code'>confirmar_reserva.php</span>:</p>";
        
        echo "<pre>
// Verificar disponibilidad
\$stmt = \$pdo->prepare(\"
    SELECT COUNT(*) 
    FROM dias_disponibles 
    WHERE fecha = ? AND turno_id = ? AND zona = ?
\");
\$stmt->execute([\$fecha_bd, \$turno_id, \$zona]);
\$existe_configuracion = \$stmt->fetchColumn() > 0;

// Si no existe configuración para ese día, considerarlo disponible por defecto
if (!\$existe_configuracion) {
    // Insertar automáticamente el día como disponible
    \$stmt = \$pdo->prepare(\"INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) VALUES (?, ?, ?, 1)\");
    \$stmt->execute([\$fecha_bd, \$turno_id, \$zona]);
    \$disponible = true;
} else {
    // Verificar si está marcado como disponible
    \$stmt = \$pdo->prepare(\"
        SELECT disponible 
        FROM dias_disponibles 
        WHERE fecha = ? AND turno_id = ? AND zona = ?
    \");
    \$stmt->execute([\$fecha_bd, \$turno_id, \$zona]);
    \$disponible = \$stmt->fetchColumn();
}

// Obtener la capacidad desde la configuración general
\$stmt = \$pdo->prepare(\"SELECT * FROM configuracion WHERE id = 1\");
\$stmt->execute();
\$config = \$stmt->fetch();

// Determinar el campo de capacidad según turno y zona
\$campo_capacidad = '';
if (\$nombre_turno == 'mediodia') {
    \$campo_capacidad = (\$zona == 'dentro') ? 'capacidad_dentro_mediodia' : 'capacidad_fuera_mediodia';
} else {
    \$campo_capacidad = (\$zona == 'dentro') ? 'capacidad_dentro_noche' : 'capacidad_fuera_noche';
}

// Obtener el aforo máximo de la configuración
\$aforo_maximo = \$config[\$campo_capacidad] ?? ((\$zona == 'dentro') ? 30 : 20);

// Obtener el número de personas ya reservadas
\$stmt = \$pdo->prepare(\"
    SELECT SUM(cantidad_personas) as total_reservado
    FROM reservas
    WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
\");
\$stmt->execute([\$fecha_bd, \$zona, \$turno_id]);
\$resultado = \$stmt->fetch();
\$total_reservado = \$resultado['total_reservado'] ?: 0;

// Calcular si hay suficiente aforo disponible
\$aforo_disponible = \$aforo_maximo - \$total_reservado;

// Si la reserva será confirmada automáticamente, verificar que haya suficiente aforo
// Si la reserva será pendiente, no verificar aforo (el administrador decidirá después)
\$hay_disponibilidad = true; // Por defecto consideramos que hay disponibilidad

// Verificar si el día está marcado como disponible
if (!\$disponible) {
    \$hay_disponibilidad = false;
}

// Si la reserva será confirmada automáticamente, verificar también el aforo
if (\$sera_confirmada && \$aforo_disponible < \$num_personas) {
    \$hay_disponibilidad = false;
}
</pre>";
    }
    
    // Mostrar enlace para volver
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='index.php' class='btn' style='background-color: #6c757d;'>Volver a la página principal</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    mostrarError("Error de base de datos: " . $e->getMessage());
} catch (Exception $e) {
    mostrarError("Error: " . $e->getMessage());
}

echo "</body></html>";
?>
