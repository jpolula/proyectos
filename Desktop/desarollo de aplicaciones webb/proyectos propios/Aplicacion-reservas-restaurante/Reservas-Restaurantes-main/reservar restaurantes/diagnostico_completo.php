<?php
/**
 * diagnostico_completo.php
 * 
 * Script para diagnosticar y corregir problemas de capacidad y disponibilidad
 */

// Configuración de la base de datos
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

// Función para mostrar mensajes
function mostrarMensaje($tipo, $mensaje) {
    $colores = [
        'exito' => ['bg' => '#d4edda', 'color' => '#155724'],
        'error' => ['bg' => '#f8d7da', 'color' => '#721c24'],
        'info' => ['bg' => '#d1ecf1', 'color' => '#0c5460'],
        'advertencia' => ['bg' => '#fff3cd', 'color' => '#856404']
    ];
    
    $estilo = isset($colores[$tipo]) ? $colores[$tipo] : $colores['info'];
    echo "<div style='background-color: {$estilo['bg']}; color: {$estilo['color']}; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>{$mensaje}</div>";
}

// HTML inicial
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Diagnóstico Completo del Sistema de Reservas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
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
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #45a049;
        }
        .button-secondary {
            background-color: #2196F3;
        }
        .button-secondary:hover {
            background-color: #0b7dda;
        }
        .button-warning {
            background-color: #ff9800;
        }
        .button-warning:hover {
            background-color: #e68a00;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <h1>Diagnóstico Completo del Sistema de Reservas</h1>";

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si se ha enviado el formulario
    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
    $zona = isset($_POST['zona']) ? $_POST['zona'] : 'dentro';
    $turno_id = isset($_POST['turno_id']) ? (int)$_POST['turno_id'] : 1;
    $num_personas = isset($_POST['num_personas']) ? (int)$_POST['num_personas'] : 5;
    
    // Obtener el nombre del turno
    $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
    $stmt->execute([$turno_id]);
    $nombre_turno = $stmt->fetchColumn() ?: 'mediodia';
    
    // Mostrar formulario de diagnóstico
    echo "<form method='post' action=''>";
    echo "<div class='form-group'>";
    echo "<label for='fecha'>Fecha a diagnosticar:</label>";
    echo "<input type='date' id='fecha' name='fecha' value='{$fecha}' required>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='zona'>Zona:</label>";
    echo "<select id='zona' name='zona'>";
    echo "<option value='dentro'" . ($zona == 'dentro' ? ' selected' : '') . ">Interior</option>";
    echo "<option value='fuera'" . ($zona == 'fuera' ? ' selected' : '') . ">Terraza</option>";
    echo "</select>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='turno_id'>Turno:</label>";
    echo "<select id='turno_id' name='turno_id'>";
    
    // Obtener turnos de la base de datos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
    while ($turno = $stmt->fetch()) {
        $selected = ($turno['id'] == $turno_id) ? ' selected' : '';
        echo "<option value='{$turno['id']}'{$selected}>" . ucfirst($turno['nombre']) . "</option>";
    }
    
    echo "</select>";
    echo "</div>";
    
    echo "<div class='form-group'>";
    echo "<label for='num_personas'>Número de personas:</label>";
    echo "<input type='number' id='num_personas' name='num_personas' value='{$num_personas}' min='1' max='30' required>";
    echo "</div>";
    
    echo "<button type='submit' class='button'>Verificar Disponibilidad</button>";
    
    // Si se ha enviado el formulario, realizar diagnóstico
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h2>Resultados para: " . date('d/m/Y', strtotime($fecha)) . ", " . ($zona == 'dentro' ? 'Interior' : 'Terraza') . ", " . ucfirst($nombre_turno) . ", {$num_personas} personas</h2>";
        
        // 1. Verificar si el día existe en dias_disponibles
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM dias_disponibles 
            WHERE fecha = ? AND turno_id = ? AND zona = ?
        ");
        $stmt->execute([$fecha, $turno_id, $zona]);
        $existe_configuracion = $stmt->fetchColumn() > 0;
        
        if (!$existe_configuracion) {
            mostrarMensaje('advertencia', "El día no está configurado en la tabla dias_disponibles.");
            
            // Opción para corregir
            if (isset($_POST['corregir_dia']) && $_POST['corregir_dia'] == 1) {
                $stmt = $pdo->prepare("
                    INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                    VALUES (?, ?, ?, 1)
                ");
                $stmt->execute([$fecha, $turno_id, $zona]);
                mostrarMensaje('exito', "Se ha configurado el día como disponible en la tabla dias_disponibles.");
                $existe_configuracion = true;
            } else {
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                echo "<input type='hidden' name='zona' value='{$zona}'>";
                echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                echo "<input type='hidden' name='corregir_dia' value='1'>";
                echo "<button type='submit' class='button'>Configurar día como disponible</button>";
                echo "</form>";
            }
        } else {
            // Verificar si está marcado como disponible
            $stmt = $pdo->prepare("
                SELECT disponible 
                FROM dias_disponibles 
                WHERE fecha = ? AND turno_id = ? AND zona = ?
            ");
            $stmt->execute([$fecha, $turno_id, $zona]);
            $disponible = $stmt->fetchColumn();
            
            if ($disponible) {
                mostrarMensaje('exito', "El día está correctamente configurado como disponible.");
            } else {
                mostrarMensaje('error', "El día está configurado como NO disponible.");
                
                // Opción para corregir
                if (isset($_POST['corregir_disponible']) && $_POST['corregir_disponible'] == 1) {
                    $stmt = $pdo->prepare("
                        UPDATE dias_disponibles
                        SET disponible = 1
                        WHERE fecha = ? AND turno_id = ? AND zona = ?
                    ");
                    $stmt->execute([$fecha, $turno_id, $zona]);
                    mostrarMensaje('exito', "Se ha marcado el día como disponible.");
                    $disponible = true;
                } else {
                    echo "<form method='post' action=''>";
                    echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                    echo "<input type='hidden' name='zona' value='{$zona}'>";
                    echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                    echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                    echo "<input type='hidden' name='corregir_disponible' value='1'>";
                    echo "<button type='submit' class='button'>Marcar día como disponible</button>";
                    echo "</form>";
                }
            }
        }
        
        // 2. Verificar si hay bloqueos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM bloqueos 
            WHERE fecha = ? AND zona = ? AND turno_id = ?
        ");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $bloqueado = ($stmt->fetchColumn() > 0);
        
        if ($bloqueado) {
            mostrarMensaje('error', "El día está bloqueado para reservas.");
            
            // Opción para corregir
            if (isset($_POST['eliminar_bloqueo']) && $_POST['eliminar_bloqueo'] == 1) {
                $stmt = $pdo->prepare("
                    DELETE FROM bloqueos 
                    WHERE fecha = ? AND zona = ? AND turno_id = ?
                ");
                $stmt->execute([$fecha, $zona, $turno_id]);
                mostrarMensaje('exito', "Se ha eliminado el bloqueo para este día.");
                $bloqueado = false;
            } else {
                echo "<form method='post' action=''>";
                echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                echo "<input type='hidden' name='zona' value='{$zona}'>";
                echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                echo "<input type='hidden' name='eliminar_bloqueo' value='1'>";
                echo "<button type='submit' class='button'>Eliminar bloqueo</button>";
                echo "</form>";
            }
        } else {
            mostrarMensaje('exito', "El día no está bloqueado para reservas.");
        }
        
        // 3. Verificar capacidad
        $stmt = $pdo->prepare("
            SELECT aforo_maximo 
            FROM capacidad 
            WHERE fecha = ? AND zona = ? AND turno_id = ?
        ");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $aforo_maximo = $stmt->fetchColumn();
        
        // Si no hay configuración específica, obtener la capacidad por defecto
        if ($aforo_maximo === false) {
            // Convertir nombre del turno a formato del campo en la base de datos
            $campo_capacidad = 'capacidad_' . $zona . '_' . strtolower($nombre_turno);
            
            $stmt = $pdo->prepare("SELECT $campo_capacidad FROM configuracion WHERE id = 1");
            $stmt->execute();
            $aforo_maximo = $stmt->fetchColumn();
            
            // Si aún no hay valor, usar un valor por defecto
            if ($aforo_maximo === false) {
                $aforo_maximo = ($zona == 'dentro') ? 30 : 20;
            }
            
            mostrarMensaje('info', "No hay configuración específica de capacidad para este día. Se está usando la capacidad por defecto: {$aforo_maximo}.");
            
            // Opción para configurar capacidad específica
            if (isset($_POST['configurar_capacidad']) && $_POST['configurar_capacidad'] == 1) {
                $nueva_capacidad = isset($_POST['nueva_capacidad']) ? (int)$_POST['nueva_capacidad'] : $aforo_maximo;
                
                $stmt = $pdo->prepare("
                    INSERT INTO capacidad (fecha, turno_id, zona, aforo_maximo)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE aforo_maximo = ?
                ");
                $stmt->execute([$fecha, $turno_id, $zona, $nueva_capacidad, $nueva_capacidad]);
                
                mostrarMensaje('exito', "Se ha configurado una capacidad específica de {$nueva_capacidad} para este día.");
                $aforo_maximo = $nueva_capacidad;
            } else {
                echo "<form method='post' action=''>";
                echo "<div class='form-group'>";
                echo "<label for='nueva_capacidad'>Nueva capacidad:</label>";
                echo "<input type='number' id='nueva_capacidad' name='nueva_capacidad' value='{$aforo_maximo}' min='1' max='100' required>";
                echo "</div>";
                echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                echo "<input type='hidden' name='zona' value='{$zona}'>";
                echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                echo "<input type='hidden' name='configurar_capacidad' value='1'>";
                echo "<button type='submit' class='button'>Configurar capacidad específica</button>";
                echo "</form>";
            }
        } else {
            mostrarMensaje('info', "Hay una configuración específica de capacidad para este día: {$aforo_maximo}.");
            
            // Opción para modificar capacidad
            if (isset($_POST['modificar_capacidad']) && $_POST['modificar_capacidad'] == 1) {
                $nueva_capacidad = isset($_POST['nueva_capacidad']) ? (int)$_POST['nueva_capacidad'] : $aforo_maximo;
                
                $stmt = $pdo->prepare("
                    UPDATE capacidad
                    SET aforo_maximo = ?
                    WHERE fecha = ? AND turno_id = ? AND zona = ?
                ");
                $stmt->execute([$nueva_capacidad, $fecha, $turno_id, $zona]);
                
                mostrarMensaje('exito', "Se ha modificado la capacidad a {$nueva_capacidad} para este día.");
                $aforo_maximo = $nueva_capacidad;
            } else {
                echo "<form method='post' action=''>";
                echo "<div class='form-group'>";
                echo "<label for='nueva_capacidad'>Nueva capacidad:</label>";
                echo "<input type='number' id='nueva_capacidad' name='nueva_capacidad' value='{$aforo_maximo}' min='1' max='100' required>";
                echo "</div>";
                echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                echo "<input type='hidden' name='zona' value='{$zona}'>";
                echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                echo "<input type='hidden' name='modificar_capacidad' value='1'>";
                echo "<button type='submit' class='button'>Modificar capacidad</button>";
                echo "</form>";
            }
        }
        
        // 4. Verificar reservas existentes
        $stmt = $pdo->prepare("
            SELECT id, cliente_id, cantidad_personas, estado
            FROM reservas
            WHERE fecha = ? AND zona = ? AND turno_id = ?
            ORDER BY estado, id
        ");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $reservas = $stmt->fetchAll();
        
        if (count($reservas) > 0) {
            echo "<h3>Reservas existentes</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Personas</th><th>Estado</th><th>Acciones</th></tr>";
            
            $total_confirmadas = 0;
            $total_pendientes = 0;
            
            foreach ($reservas as $reserva) {
                // Obtener nombre del cliente
                $stmt = $pdo->prepare("SELECT nombre FROM clientes WHERE id = ?");
                $stmt->execute([$reserva['cliente_id']]);
                $nombre_cliente = $stmt->fetchColumn() ?: 'Cliente ' . $reserva['cliente_id'];
                
                echo "<tr>";
                echo "<td>{$reserva['id']}</td>";
                echo "<td>{$nombre_cliente}</td>";
                echo "<td>{$reserva['cantidad_personas']}</td>";
                echo "<td>" . ucfirst($reserva['estado']) . "</td>";
                echo "<td>";
                
                // Opciones para cambiar el estado
                if ($reserva['estado'] == 'pendiente') {
                    echo "<form method='post' action='' style='display:inline;'>";
                    echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                    echo "<input type='hidden' name='zona' value='{$zona}'>";
                    echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                    echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                    echo "<input type='hidden' name='reserva_id' value='{$reserva['id']}'>";
                    echo "<input type='hidden' name='cambiar_estado' value='confirmada'>";
                    echo "<button type='submit' class='button' style='padding: 5px 10px; font-size: 12px;'>Confirmar</button>";
                    echo "</form>";
                } else {
                    echo "<form method='post' action='' style='display:inline;'>";
                    echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                    echo "<input type='hidden' name='zona' value='{$zona}'>";
                    echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                    echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                    echo "<input type='hidden' name='reserva_id' value='{$reserva['id']}'>";
                    echo "<input type='hidden' name='cambiar_estado' value='pendiente'>";
                    echo "<button type='submit' class='button button-secondary' style='padding: 5px 10px; font-size: 12px;'>Poner pendiente</button>";
                    echo "</form>";
                }
                
                echo "<form method='post' action='' style='display:inline; margin-left: 5px;'>";
                echo "<input type='hidden' name='fecha' value='{$fecha}'>";
                echo "<input type='hidden' name='zona' value='{$zona}'>";
                echo "<input type='hidden' name='turno_id' value='{$turno_id}'>";
                echo "<input type='hidden' name='num_personas' value='{$num_personas}'>";
                echo "<input type='hidden' name='reserva_id' value='{$reserva['id']}'>";
                echo "<input type='hidden' name='eliminar_reserva' value='1'>";
                echo "<button type='submit' class='button button-warning' style='padding: 5px 10px; font-size: 12px;'>Eliminar</button>";
                echo "</form>";
                
                echo "</td>";
                echo "</tr>";
                
                // Contar reservas por estado
                if ($reserva['estado'] == 'confirmada') {
                    $total_confirmadas += $reserva['cantidad_personas'];
                } else {
                    $total_pendientes += $reserva['cantidad_personas'];
                }
            }
            
            echo "</table>";
            
            echo "<p><strong>Total personas en reservas confirmadas:</strong> {$total_confirmadas}</p>";
            echo "<p><strong>Total personas en reservas pendientes:</strong> {$total_pendientes}</p>";
        } else {
            mostrarMensaje('info', "No hay reservas existentes para este día, zona y turno.");
        }
        
        // Procesar cambios de estado de reservas
        if (isset($_POST['cambiar_estado']) && isset($_POST['reserva_id'])) {
            $reserva_id = (int)$_POST['reserva_id'];
            $nuevo_estado = $_POST['cambiar_estado'];
            
            $stmt = $pdo->prepare("
                UPDATE reservas
                SET estado = ?
                WHERE id = ?
            ");
            $stmt->execute([$nuevo_estado, $reserva_id]);
            
            mostrarMensaje('exito', "Se ha cambiado el estado de la reserva #{$reserva_id} a " . ucfirst($nuevo_estado) . ".");
            
            // Recargar la página para mostrar los cambios
            echo "<script>window.location.href = window.location.pathname + '?' + new Date().getTime();</script>";
        }
        
        // Procesar eliminación de reservas
        if (isset($_POST['eliminar_reserva']) && isset($_POST['reserva_id'])) {
            $reserva_id = (int)$_POST['reserva_id'];
            
            $stmt = $pdo->prepare("
                DELETE FROM reservas
                WHERE id = ?
            ");
            $stmt->execute([$reserva_id]);
            
            mostrarMensaje('exito', "Se ha eliminado la reserva #{$reserva_id}.");
            
            // Recargar la página para mostrar los cambios
            echo "<script>window.location.href = window.location.pathname + '?' + new Date().getTime();</script>";
        }
        
        // 5. Calcular disponibilidad actual
        $stmt = $pdo->prepare("
            SELECT SUM(cantidad_personas) as total_reservado
            FROM reservas
            WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
        ");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $resultado = $stmt->fetch();
        $total_reservado = $resultado['total_reservado'] ?: 0;
        
        $aforo_disponible = $aforo_maximo - $total_reservado;
        $hay_disponibilidad = $aforo_disponible >= $num_personas;
        
        echo "<h3>Resumen de disponibilidad</h3>";
        echo "<ul>";
        echo "<li><strong>Aforo máximo:</strong> {$aforo_maximo}</li>";
        echo "<li><strong>Total reservado (confirmadas):</strong> {$total_reservado}</li>";
        echo "<li><strong>Aforo disponible:</strong> {$aforo_disponible}</li>";
        echo "<li><strong>Personas solicitadas:</strong> {$num_personas}</li>";
        echo "<li><strong>¿Hay disponibilidad?</strong> " . ($hay_disponibilidad ? 'Sí' : 'No') . "</li>";
        echo "</ul>";
        
        if ($hay_disponibilidad) {
            mostrarMensaje('exito', "Hay suficiente aforo disponible para {$num_personas} personas.");
        } else {
            mostrarMensaje('error', "No hay suficiente aforo disponible para {$num_personas} personas.");
        }
        
        // Botón para volver a la página de reservas
        echo "<div style='margin-top: 30px;'>";
        echo "<a href='reserva.php' class='button'>Ir a la página de reservas</a>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    mostrarMensaje('error', "Error en la base de datos: " . $e->getMessage());
}

// HTML final
echo "</form></body></html>";
