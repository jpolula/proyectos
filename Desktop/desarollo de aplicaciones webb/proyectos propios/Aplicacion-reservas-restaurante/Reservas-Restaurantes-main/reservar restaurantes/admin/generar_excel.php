<?php
/**
 * Generador de XLS para el historial de reservas
 * Permite exportar las reservas históricas a un archivo Excel (XLS)
 */

// Incluir archivos necesarios
require_once 'auth.php';
require_once '../vendor/autoload.php';


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

// Conexión a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Determinar si es historial o reservas actuales
$es_historial = isset($_GET['historial']) && $_GET['historial'] == 1;

// Procesar filtros según la página de origen
if ($es_historial) {
    // Filtros para historial_reservas.php
    $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
    $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
    $filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';
    $filtro_turno = isset($_GET['filtro_turno']) ? $_GET['filtro_turno'] : '';
    $filtro_zona = isset($_GET['filtro_zona']) ? $_GET['filtro_zona'] : '';
    $filtro_fecha = ''; // No se usa en historial
} else {
    // Filtros para reservas.php
    $filtro_fecha = isset($_GET['filtro_fecha']) ? $_GET['filtro_fecha'] : date('Y-m-d');
    $filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';
    $filtro_turno = isset($_GET['filtro_turno']) ? $_GET['filtro_turno'] : '';
    $filtro_zona = isset($_GET['filtro_zona']) ? $_GET['filtro_zona'] : '';
    $fecha_inicio = '';
    $fecha_fin = '';
}

// Construir la consulta SQL base
$sql = "
    SELECT r.id, r.cliente_id, r.fecha, r.zona, r.turno_id, r.hora, r.cantidad_personas, r.personas_solicitadas, 
           r.observaciones, r.necesidades_especiales, r.tiene_alergenos, r.alergenos, r.estado, r.fecha_creacion, r.fecha_modificacion,
           c.nombre, c.email, c.telefono, t.nombre AS turno_nombre,
           DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
           TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada,
           DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_creacion_formateada
    FROM reservas r
    JOIN clientes c ON r.cliente_id = c.id
    JOIN turnos t ON r.turno_id = t.id
    WHERE 1=1
";

$params = [];

// Agregar condiciones de filtro según el tipo de consulta
if ($es_historial) {
    // Para historial, solo mostrar reservas pasadas
    $sql .= " AND r.fecha < ?";
    $params[] = date('Y-m-d');
    
    // Filtros específicos para historial
    if (!empty($fecha_inicio)) {
        $sql .= " AND r.fecha >= ?";
        $params[] = $fecha_inicio;
    }
    
    if (!empty($fecha_fin)) {
        $sql .= " AND r.fecha <= ?";
        $params[] = $fecha_fin;
    }
} else {
    // Filtro de fecha para reservas actuales
    if (!empty($filtro_fecha)) {
        $sql .= " AND r.fecha = ?";
        $params[] = $filtro_fecha;
    }
}

// Filtros comunes para ambos tipos
if (!empty($filtro_estado)) {
    $sql .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_turno)) {
    $sql .= " AND r.turno_id = ?";
    $params[] = $filtro_turno;
}

if (!empty($filtro_zona)) {
    $sql .= " AND r.zona = ?";
    $params[] = $filtro_zona;
}

// Agregar orden
$sql .= " ORDER BY r.fecha DESC, r.hora ASC";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

// Nombre del archivo XLS
$filename = ($es_historial ? 'Historial_Reservas_' : 'Reservas_') . date('Y-m-d') . '.xls';

// Cabeceras para la descarga
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Iniciar la salida del contenido HTML que Excel interpretará como una hoja de cálculo
echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>
";
echo "<head>\n";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
echo "<style>\n";
echo "td { border: 0.5pt solid #000000; vertical-align: middle; }\n";
echo "th { border: 0.5pt solid #000000; background-color: #DDEBF7; font-weight: bold; text-align: center; }\n";
echo ".titulo { font-size: 16pt; font-weight: bold; text-align: center; border: none; }\n";
echo ".filtro { font-weight: bold; }\n";
echo ".filtro-valor { }\n";
echo ".espacio { border: none; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";
echo "<table>\n";

// Título del informe
echo "<tr><td colspan=\"13\" class=\"titulo\">HISTORIAL DE RESERVAS - " . date('d/m/Y') . "</td></tr>\n";
echo "<tr><td colspan=\"13\" class=\"espacio\">&nbsp;</td></tr>\n";

// Añadir información de filtros si existen
// Mostrar filtros aplicados
if ($es_historial) {
    if (!empty($fecha_inicio) || !empty($fecha_fin) || !empty($filtro_estado) || !empty($filtro_turno) || !empty($filtro_zona)) {
        echo "<tr><td colspan=\"13\" class=\"filtro\">FILTROS APLICADOS:</td></tr>\n";
        
        if (!empty($fecha_inicio)) {
            echo "<tr><td class=\"filtro\">Desde:</td><td colspan=\"12\" class=\"filtro-valor\">" . date('d/m/Y', strtotime($fecha_inicio)) . "</td></tr>\n";
        }
        
        if (!empty($fecha_fin)) {
            echo "<tr><td class=\"filtro\">Hasta:</td><td colspan=\"12\" class=\"filtro-valor\">" . date('d/m/Y', strtotime($fecha_fin)) . "</td></tr>\n";
        }
    }
} else {
    if (!empty($filtro_fecha) || !empty($filtro_estado) || !empty($filtro_turno) || !empty($filtro_zona)) {
        echo "<tr><td colspan=\"13\" class=\"filtro\">FILTROS APLICADOS:</td></tr>\n";
        
        if (!empty($filtro_fecha)) {
            echo "<tr><td class=\"filtro\">Fecha:</td><td colspan=\"12\" class=\"filtro-valor\">" . date('d/m/Y', strtotime($filtro_fecha)) . "</td></tr>\n";
        }
    }
    
    if (!empty($fecha_fin)) {
        echo "<tr><td class=\"filtro\">Hasta:</td><td colspan=\"12\" class=\"filtro-valor\">" . date('d/m/Y', strtotime($fecha_fin)) . "</td></tr>\n";
    }
    
    if (!empty($filtro_estado)) {
        echo "<tr><td class=\"filtro\">Estado:</td><td colspan=\"12\" class=\"filtro-valor\">" . ucfirst($filtro_estado) . "</td></tr>\n";
    }
    
    if (!empty($filtro_turno)) {
        // Obtener el nombre del turno
        $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
        $stmt->execute([$filtro_turno]);
        $turno = $stmt->fetch();
        
        echo "<tr><td class=\"filtro\">Turno:</td><td colspan=\"12\" class=\"filtro-valor\">" . ucfirst($turno['nombre']) . "</td></tr>\n";
    }
    
    if (!empty($filtro_zona)) {
        echo "<tr><td class=\"filtro\">Zona:</td><td colspan=\"12\" class=\"filtro-valor\">" . ($filtro_zona === 'dentro' ? 'Interior' : 'Terraza') . "</td></tr>\n";
    }
    
    // Línea en blanco después de los filtros
    echo "<tr><td colspan=\"13\" class=\"espacio\">&nbsp;</td></tr>\n";
}

// Establecer los encabezados de las columnas
$headers = [
    'ID', 'Cliente', 'Email', 'Teléfono', 'Fecha', 'Hora', 'Turno', 
    'Zona', 'Personas', 'Estado', 'Observaciones', 'Necesidades Especiales', 'Alérgenos'
];

// Escribir encabezados
echo "<tr>\n";
foreach ($headers as $header) {
    echo "<th>" . htmlspecialchars($header) . "</th>\n";
}
echo "</tr>\n";

// Llenar los datos
foreach ($reservas as $reserva) {
    // Mostrar personas_solicitadas si existe, sino cantidad_personas
    $personas = ($reserva['personas_solicitadas'] > 0) ? $reserva['personas_solicitadas'] : $reserva['cantidad_personas'];
    
    // Limpiar observaciones (quitar info de personas solicitadas si existe)
    $observaciones = $reserva['observaciones'];
    if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente') {
        $observaciones = preg_replace('/Personas solicitadas: \d+/', '', $observaciones);
    }
    
    echo "<tr>\n";
    echo "<td>" . $reserva['id'] . "</td>\n";
    echo "<td>" . htmlspecialchars($reserva['nombre']) . "</td>\n";
    echo "<td>" . htmlspecialchars($reserva['email']) . "</td>\n";
    echo "<td>" . htmlspecialchars($reserva['telefono']) . "</td>\n";
    echo "<td>" . $reserva['fecha_formateada'] . "</td>\n";
    echo "<td>" . $reserva['hora_formateada'] . "h</td>\n";
    echo "<td>" . ucfirst($reserva['turno_nombre']) . "</td>\n";
    echo "<td>" . ($reserva['zona'] === 'dentro' ? 'Interior' : 'Terraza') . "</td>\n";
    echo "<td>" . $personas . "</td>\n";
    echo "<td>" . ucfirst($reserva['estado']) . "</td>\n";
    echo "<td>" . htmlspecialchars(trim($observaciones)) . "</td>\n";
    echo "<td>" . htmlspecialchars($reserva['necesidades_especiales']) . "</td>\n";
    echo "<td>" . htmlspecialchars($reserva['alergenos']) . "</td>\n";
    echo "</tr>\n";
}

// Cerrar la tabla y el documento HTML
echo "</table>\n";
echo "</body>\n";
echo "</html>\n";

exit;
