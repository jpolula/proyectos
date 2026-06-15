<?php
// Archivo para gestionar la ocupación por día
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

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

// Conectar a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener la configuración actual
    $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
    
    // Obtener el mes y año actual o los proporcionados por GET
    $mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
    $anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));
    
    // Validar mes y año
    if ($mes < 1 || $mes > 12) $mes = intval(date('m'));
    if ($anio < 2000 || $anio > 2100) $anio = intval(date('Y'));
    
    // Obtener el primer día del mes y el número de días
    $primerDia = new DateTime("$anio-$mes-01");
    $ultimoDia = new DateTime("$anio-$mes-" . $primerDia->format('t'));
    
    // Formatear para SQL
    $fechaInicio = $primerDia->format('Y-m-d');
    $fechaFin = $ultimoDia->format('Y-m-d');
    
    // Obtener los días disponibles
    $stmt = $pdo->prepare("
        SELECT DISTINCT fecha 
        FROM dias_disponibles 
        WHERE fecha BETWEEN ? AND ? AND disponible = 1
        ORDER BY fecha
    ");
    $stmt->execute([$fechaInicio, $fechaFin]);
    $diasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener las reservas por día, turno y zona
    $stmt = $pdo->prepare("
        SELECT 
            fecha, 
            turno_id, 
            zona, 
            COUNT(*) as num_reservas, 
            SUM(cantidad_personas) as total_personas
        FROM reservas
        WHERE fecha BETWEEN ? AND ?
        GROUP BY fecha, turno_id, zona
        ORDER BY fecha, turno_id, zona
    ");
    $stmt->execute([$fechaInicio, $fechaFin]);
    $reservasPorDia = $stmt->fetchAll();
    
    // Organizar las reservas por fecha para fácil acceso
    $reservasPorFecha = [];
    foreach ($reservasPorDia as $reserva) {
        $fecha = $reserva['fecha'];
        $turno = $reserva['turno_id'];
        $zona = $reserva['zona'];
        
        if (!isset($reservasPorFecha[$fecha])) {
            $reservasPorFecha[$fecha] = [];
        }
        
        $key = $turno . '_' . $zona;
        $reservasPorFecha[$fecha][$key] = [
            'num_reservas' => $reserva['num_reservas'],
            'total_personas' => $reserva['total_personas']
        ];
    }
    
    // Obtener información de los turnos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
    $turnos = $stmt->fetchAll();
    
    // Mensaje de éxito/error
    $mensaje = '';
    
} catch (PDOException $e) {
    $mensaje = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocupación por Día - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <header class="bg-white shadow-md rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Panel de Administración - Ocupación por Día</h1>
                <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Volver al Panel</a>
            </div>
        </header>
        
        <?php if (!empty($mensaje)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $mensaje; ?></p>
        </div>
        <?php endif; ?>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Seleccionar Mes y Año</h2>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="flex flex-wrap gap-4 mb-6">
                <div class="w-full md:w-auto">
                    <label for="mes" class="block text-gray-700 text-sm font-bold mb-2">Mes:</label>
                    <select name="mes" id="mes" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php
                        $nombresMeses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        
                        foreach ($nombresMeses as $num => $nombre) {
                            $selected = ($num == $mes) ? 'selected' : '';
                            echo "<option value=\"$num\" $selected>$nombre</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="w-full md:w-auto">
                    <label for="anio" class="block text-gray-700 text-sm font-bold mb-2">Año:</label>
                    <select name="anio" id="anio" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <?php
                        $anioActual = intval(date('Y'));
                        for ($i = $anioActual - 1; $i <= $anioActual + 5; $i++) {
                            $selected = ($i == $anio) ? 'selected' : '';
                            echo "<option value=\"$i\" $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="w-full md:w-auto flex items-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Ver Ocupación
                    </button>
                </div>
            </form>
            
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Capacidad Configurada:</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-medium text-blue-800">Mediodía:</h4>
                    <p>Interior: <strong><?php echo $config['capacidad_dentro_mediodia']; ?></strong> personas</p>
                    <p>Terraza: <strong><?php echo $config['capacidad_fuera_mediodia']; ?></strong> personas</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-medium text-blue-800">Noche:</h4>
                    <p>Interior: <strong><?php echo $config['capacidad_dentro_noche']; ?></strong> personas</p>
                    <p>Terraza: <strong><?php echo $config['capacidad_fuera_noche']; ?></strong> personas</p>
                </div>
            </div>
            
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Ocupación para <?php echo $nombresMeses[$mes] . ' ' . $anio; ?></h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Disponible
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider" colspan="2">
                                Mediodía
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider" colspan="2">
                                Noche
                            </th>
                        </tr>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50"></th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50"></th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Interior
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Terraza
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Interior
                            </th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Terraza
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Recorrer todos los días del mes
                        $currentDate = clone $primerDia;
                        while ($currentDate <= $ultimoDia) {
                            $fechaActual = $currentDate->format('Y-m-d');
                            $esDisponible = in_array($fechaActual, $diasDisponibles);
                            
                            echo "<tr>";
                            
                            // Fecha
                            echo "<td class='py-2 px-4 border-b border-gray-200'>";
                            echo $currentDate->format('d/m/Y') . " (" . $currentDate->format('l') . ")";
                            echo "</td>";
                            
                            // Disponible
                            echo "<td class='py-2 px-4 border-b border-gray-200'>";
                            if ($esDisponible) {
                                echo "<span class='inline-block bg-green-100 text-green-800 px-2 py-1 rounded'>Sí</span>";
                            } else {
                                echo "<span class='inline-block bg-red-100 text-red-800 px-2 py-1 rounded'>No</span>";
                            }
                            echo "</td>";
                            
                            // Mediodía - Interior
                            echo "<td class='py-2 px-4 border-b border-gray-200'>";
                            if (isset($reservasPorFecha[$fechaActual]['1_dentro'])) {
                                $reservas = $reservasPorFecha[$fechaActual]['1_dentro'];
                                $porcentaje = min(100, round(($reservas['total_personas'] / $config['capacidad_dentro_mediodia']) * 100));
                                echo "<div class='flex items-center'>";
                                echo "<div class='w-full bg-gray-200 rounded-full h-2.5 mr-2'>";
                                echo "<div class='bg-blue-600 h-2.5 rounded-full' style='width: {$porcentaje}%'></div>";
                                echo "</div>";
                                echo "<span>{$reservas['total_personas']}/{$config['capacidad_dentro_mediodia']} ({$porcentaje}%)</span>";
                                echo "</div>";
                                echo "<div class='text-xs text-gray-500 mt-1'>{$reservas['num_reservas']} reservas</div>";
                            } else {
                                echo "0/{$config['capacidad_dentro_mediodia']} (0%)";
                            }
                            echo "</td>";
                            
                            // Mediodía - Terraza
                            echo "<td class='py-2 px-4 border-b border-gray-200'>";
                            if (isset($reservasPorFecha[$fechaActual]['1_fuera'])) {
                                $reservas = $reservasPorFecha[$fechaActual]['1_fuera'];
                                $porcentaje = min(100, round(($reservas['total_personas'] / $config['capacidad_fuera_mediodia']) * 100));
                                echo "<div class='flex items-center'>";
                                echo "<div class='w-full bg-gray-200 rounded-full h-2.5 mr-2'>";
                                echo "<div class='bg-blue-600 h-2.5 rounded-full' style='width: {$porcentaje}%'></div>";
                                echo "</div>";
                                echo "<span>{$reservas['total_personas']}/{$config['capacidad_fuera_mediodia']} ({$porcentaje}%)</span>";
                                echo "</div>";
                                echo "<div class='text-xs text-gray-500 mt-1'>{$reservas['num_reservas']} reservas</div>";
                            } else {
                                echo "0/{$config['capacidad_fuera_mediodia']} (0%)";
                            }
                            echo "</td>";
                            
                            // Noche - Interior
                            echo "<td class='py-2 px-4 border-b border-gray-200'>";
                            if (isset($reservasPorFecha[$fechaActual]['2_dentro'])) {
                                $reservas = $reservasPorFecha[$fechaActual]['2_dentro'];
                                $porcentaje = min(100, round(($reservas['total_personas'] / $config['capacidad_dentro_noche']) * 100));
                                echo "<div class='flex items-center'>";
                                echo "<div class='w-full bg-gray-200 rounded-full h-2.5 mr-2'>";
                                echo "<div class='bg-blue-600 h-2.5 rounded-full' style='width: {$porcentaje}%'></div>";
                                echo "</div>";
                                echo "<span>{$reservas['total_personas']}/{$config['capacidad_dentro_noche']} ({$porcentaje}%)</span>";
                                echo "</div>";
                                echo "<div class='text-xs text-gray-500 mt-1'>{$reservas['num_reservas']} reservas</div>";
                            } else {
                                echo "0/{$config['capacidad_dentro_noche']} (0%)";
                            }
                            echo "</td>";
                            
                            // Noche - Terraza
                            echo "<td class='py-2 px-4 border-b border-gray-200'>";
                            if (isset($reservasPorFecha[$fechaActual]['2_fuera'])) {
                                $reservas = $reservasPorFecha[$fechaActual]['2_fuera'];
                                $porcentaje = min(100, round(($reservas['total_personas'] / $config['capacidad_fuera_noche']) * 100));
                                echo "<div class='flex items-center'>";
                                echo "<div class='w-full bg-gray-200 rounded-full h-2.5 mr-2'>";
                                echo "<div class='bg-blue-600 h-2.5 rounded-full' style='width: {$porcentaje}%'></div>";
                                echo "</div>";
                                echo "<span>{$reservas['total_personas']}/{$config['capacidad_fuera_noche']} ({$porcentaje}%)</span>";
                                echo "</div>";
                                echo "<div class='text-xs text-gray-500 mt-1'>{$reservas['num_reservas']} reservas</div>";
                            } else {
                                echo "0/{$config['capacidad_fuera_noche']} (0%)";
                            }
                            echo "</td>";
                            
                            echo "</tr>";
                            
                            // Avanzar al siguiente día
                            $currentDate->modify('+1 day');
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
