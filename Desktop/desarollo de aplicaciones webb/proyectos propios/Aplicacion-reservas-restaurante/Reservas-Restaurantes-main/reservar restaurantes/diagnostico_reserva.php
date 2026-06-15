<?php
// Iniciar sesión para acceder a las variables de sesión
session_start();

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

echo "<h1>Diagnóstico del Sistema de Reservas</h1>";

// Verificar si hay variables de sesión
echo "<h2>Variables de Sesión</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Conectar a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color:green;'>✓ Conexión a la base de datos establecida correctamente</p>";
    
    // Verificar tabla dias_disponibles
    $stmt = $pdo->query("SHOW TABLES LIKE 'dias_disponibles'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green;'>✓ Tabla dias_disponibles existe</p>";
        
        // Verificar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE dias_disponibles");
        echo "<h3>Estructura de la tabla dias_disponibles:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si hay datos en la tabla
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM dias_disponibles");
        $count = $stmt->fetch();
        echo "<p>Total de registros en dias_disponibles: " . $count['total'] . "</p>";
        
        if ($count['total'] > 0) {
            // Mostrar algunos ejemplos
            $stmt = $pdo->query("SELECT * FROM dias_disponibles LIMIT 5");
            echo "<h3>Ejemplos de días disponibles:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Fecha</th><th>Turno ID</th><th>Zona</th><th>Disponible</th></tr>";
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['fecha'] . "</td>";
                echo "<td>" . $row['turno_id'] . "</td>";
                echo "<td>" . $row['zona'] . "</td>";
                echo "<td>" . $row['disponible'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar días disponibles para hoy y próximos días
            $hoy = date('Y-m-d');
            $stmt = $pdo->prepare("
                SELECT fecha, turno_id, zona, disponible 
                FROM dias_disponibles 
                WHERE fecha >= ? AND disponible = 1
                ORDER BY fecha
                LIMIT 10
            ");
            $stmt->execute([$hoy]);
            $dias = $stmt->fetchAll();
            
            echo "<h3>Próximos días disponibles:</h3>";
            if (count($dias) > 0) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Fecha</th><th>Turno ID</th><th>Zona</th></tr>";
                foreach ($dias as $dia) {
                    echo "<tr>";
                    echo "<td>" . $dia['fecha'] . "</td>";
                    echo "<td>" . $dia['turno_id'] . "</td>";
                    echo "<td>" . $dia['zona'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color:red;'>No hay días disponibles próximos</p>";
            }
        } else {
            echo "<p style='color:red;'>⚠ La tabla dias_disponibles está vacía</p>";
        }
    } else {
        echo "<p style='color:red;'>⚠ La tabla dias_disponibles no existe</p>";
    }
    
    // Verificar tabla turnos
    $stmt = $pdo->query("SHOW TABLES LIKE 'turnos'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green;'>✓ Tabla turnos existe</p>";
        
        // Mostrar turnos
        $stmt = $pdo->query("SELECT * FROM turnos");
        echo "<h3>Turnos configurados:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Hora Inicio</th><th>Hora Fin</th></tr>";
        while ($row = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . $row['hora_inicio'] . "</td>";
            echo "<td>" . $row['hora_fin'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>⚠ La tabla turnos no existe</p>";
    }
    
    // Verificar tabla configuracion
    $stmt = $pdo->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green;'>✓ Tabla configuracion existe</p>";
        
        // Mostrar configuración
        $stmt = $pdo->query("SELECT * FROM configuracion LIMIT 1");
        $config = $stmt->fetch();
        if ($config) {
            echo "<h3>Configuración actual:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>";
            foreach ($config as $key => $value) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            echo "<tr>";
            foreach ($config as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
            echo "</table>";
        } else {
            echo "<p style='color:red;'>⚠ La tabla configuracion está vacía</p>";
        }
    } else {
        echo "<p style='color:red;'>⚠ La tabla configuracion no existe</p>";
    }
    
    // Probar una consulta de verificación de disponibilidad
    echo "<h2>Prueba de Verificación de Disponibilidad</h2>";
    
    // Usar la primera fecha disponible si existe
    if (isset($dias) && count($dias) > 0) {
        $fecha_prueba = $dias[0]['fecha'];
        $turno_prueba = $dias[0]['turno_id'];
        $zona_prueba = $dias[0]['zona'];
        
        echo "<p>Probando con: Fecha=$fecha_prueba, Turno=$turno_prueba, Zona=$zona_prueba</p>";
        
        // Verificar si el día está disponible
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as disponible 
            FROM dias_disponibles 
            WHERE fecha = ? AND turno_id = ? AND zona = ? AND disponible = 1
        ");
        $stmt->execute([$fecha_prueba, $turno_prueba, $zona_prueba]);
        $resultado = $stmt->fetch();
        
        echo "<p>Resultado de disponibilidad: " . ($resultado['disponible'] > 0 ? "Disponible ✓" : "No disponible ⚠") . "</p>";
        
        // Obtener capacidad
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN ? = 'dentro' AND t.nombre = 'mediodia' THEN c.capacidad_dentro_mediodia
                    WHEN ? = 'fuera' AND t.nombre = 'mediodia' THEN c.capacidad_fuera_mediodia
                    WHEN ? = 'dentro' AND t.nombre = 'noche' THEN c.capacidad_dentro_noche
                    WHEN ? = 'fuera' AND t.nombre = 'noche' THEN c.capacidad_fuera_noche
                    ELSE 0
                END AS capacidad
            FROM configuracion c, turnos t
            WHERE c.id = 1 AND t.id = ?
        ");
        $stmt->execute([$zona_prueba, $zona_prueba, $zona_prueba, $zona_prueba, $turno_prueba]);
        $resultado = $stmt->fetch();
        
        if ($resultado) {
            $capacidad_total = (int)$resultado['capacidad'];
            echo "<p>Capacidad total para esta combinación: $capacidad_total</p>";
            
            // Verificar ocupación
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(num_personas), 0) as ocupacion 
                FROM reservas 
                WHERE fecha = ? AND turno_id = ? AND zona = ? AND estado = 'confirmada'
            ");
            $stmt->execute([$fecha_prueba, $turno_prueba, $zona_prueba]);
            $ocupacion = $stmt->fetch();
            
            $personas_ocupadas = (int)$ocupacion['ocupacion'];
            $capacidad_disponible = $capacidad_total - $personas_ocupadas;
            
            echo "<p>Personas ocupadas: $personas_ocupadas</p>";
            echo "<p>Capacidad disponible: $capacidad_disponible</p>";
            
            if (isset($_SESSION['num_personas'])) {
                echo "<p>Personas solicitadas en la sesión: {$_SESSION['num_personas']}</p>";
                echo "<p>Resultado: " . ($_SESSION['num_personas'] <= $capacidad_disponible ? "Hay capacidad suficiente ✓" : "No hay capacidad suficiente ⚠") . "</p>";
            } else {
                echo "<p style='color:red;'>⚠ No hay número de personas definido en la sesión</p>";
            }
        } else {
            echo "<p style='color:red;'>⚠ No se pudo obtener la capacidad para esta combinación</p>";
        }
    } else {
        echo "<p style='color:red;'>⚠ No hay días disponibles para probar</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error de conexión: " . $e->getMessage() . "</p>";
}
?>
