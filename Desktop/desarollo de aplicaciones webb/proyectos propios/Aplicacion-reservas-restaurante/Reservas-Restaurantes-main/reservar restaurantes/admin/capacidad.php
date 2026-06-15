<?php
// Incluir archivo de autenticación
require_once 'auth.php';

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

// Obtener los turnos
try {
    $stmt = $pdo->query("SELECT id, nombre, hora_inicio, hora_fin FROM turnos ORDER BY id");
    $turnos = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error al obtener los turnos: ' . $e->getMessage());
}

// Obtener la capacidad actual (configuración general)
$capacidades = [];
try {
    // Obtener capacidades de la tabla de configuración
    $stmt = $pdo->query("SELECT * FROM configuracion LIMIT 1");
    $config = $stmt->fetch();
    
    // Valor por defecto para personas sin aprobación
    $max_personas_sin_aprobacion = $config ? $config['max_personas_sin_aprobacion'] : 6;
    
    // Obtener capacidades por turno y zona
    foreach ($turnos as $turno) {
        $turno_id = $turno['id'];
        $capacidades[$turno_id]['dentro'] = $config ? ($turno['nombre'] == 'mediodia' ? $config['capacidad_dentro_mediodia'] : $config['capacidad_dentro_noche']) : 30;
        $capacidades[$turno_id]['fuera'] = $config ? ($turno['nombre'] == 'mediodia' ? $config['capacidad_fuera_mediodia'] : $config['capacidad_fuera_noche']) : 20;
    }
} catch (PDOException $e) {
    die('Error al obtener la configuración: ' . $e->getMessage());
}

// Obtener fecha seleccionada (si existe)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener disponibilidad para la fecha seleccionada
$disponibilidad = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM dias_disponibles WHERE fecha = ?");
    $stmt->execute([$fecha]);
    $dias_disponibles = $stmt->fetchAll();
    
    // Inicializar disponibilidad (por defecto todo disponible)
    foreach ($turnos as $turno) {
        $turno_id = $turno['id'];
        $disponibilidad[$turno_id]['dentro'] = true;
        $disponibilidad[$turno_id]['fuera'] = true;
    }
    
    // Actualizar con datos de la base de datos
    foreach ($dias_disponibles as $dia) {
        $disponibilidad[$dia['turno_id']][$dia['zona']] = $dia['disponible'];
    }
} catch (PDOException $e) {
    $mensaje = 'Error al obtener la disponibilidad: ' . $e->getMessage();
    $tipo_mensaje = 'error';
}

// Procesar el formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar actualización de disponibilidad por día o rango de fechas
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_disponibilidad') {
        try {
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];
            
            // Validar fechas
            if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
                throw new Exception("La fecha de fin no puede ser anterior a la fecha de inicio");
            }
            
            // Crear un array con todas las fechas en el rango
            $fechas = [];
            $current = strtotime($fecha_inicio);
            $end = strtotime($fecha_fin);
            
            while ($current <= $end) {
                $fechas[] = date('Y-m-d', $current);
                $current = strtotime('+1 day', $current);
            }
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Preparar consultas
            $deleteStmt = $pdo->prepare("DELETE FROM dias_disponibles WHERE fecha = ?");
            $insertStmt = $pdo->prepare("INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible) VALUES (?, ?, ?, ?)");
            
            // Procesar cada fecha en el rango
            foreach ($fechas as $fecha_actual) {
                // Eliminar registros existentes para esta fecha
                $deleteStmt->execute([$fecha_actual]);
                
                // Insertar nuevos registros según lo seleccionado
                foreach ($turnos as $turno) {
                    $turno_id = $turno['id'];
                    
                    // Zona interior
                    $disponible_dentro = isset($_POST["disponible_{$turno_id}_dentro"]) ? 1 : 0;
                    $insertStmt->execute([$fecha_actual, 'dentro', $turno_id, $disponible_dentro]);
                    
                    // Zona terraza
                    $disponible_fuera = isset($_POST["disponible_{$turno_id}_fuera"]) ? 1 : 0;
                    $insertStmt->execute([$fecha_actual, 'fuera', $turno_id, $disponible_fuera]);
                }
            }
            
            // Confirmar transacción
            $pdo->commit();
            
            if (count($fechas) === 1) {
                $mensaje = "Disponibilidad para el día " . date('d/m/Y', strtotime($fecha_inicio)) . " actualizada correctamente.";
            } else {
                $mensaje = "Disponibilidad actualizada correctamente para el rango del " . 
                           date('d/m/Y', strtotime($fecha_inicio)) . " al " . 
                           date('d/m/Y', strtotime($fecha_fin)) . ".";
            }
            $tipo_mensaje = 'success';
            
            // Actualizar fecha seleccionada
            $fecha = $fecha_inicio;
            
            // Actualizar disponibilidad
            $stmt = $pdo->prepare("SELECT * FROM dias_disponibles WHERE fecha = ?");
            $stmt->execute([$fecha]);
            $dias_disponibles = $stmt->fetchAll();
            
            // Reinicializar disponibilidad
            foreach ($turnos as $turno) {
                $turno_id = $turno['id'];
                $disponibilidad[$turno_id]['dentro'] = false;
                $disponibilidad[$turno_id]['fuera'] = false;
            }
            
            // Actualizar con datos de la base de datos
            foreach ($dias_disponibles as $dia) {
                $disponibilidad[$dia['turno_id']][$dia['zona']] = $dia['disponible'];
            }
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            $mensaje = 'Error al actualizar la disponibilidad: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $errores = false;
        
        // Obtener valores del formulario
        $max_personas = isset($_POST['max_personas_sin_aprobacion']) ? (int)$_POST['max_personas_sin_aprobacion'] : 6;
        
        // Capacidades por turno y zona
        $capacidad_dentro_mediodia = 0;
        $capacidad_fuera_mediodia = 0;
        $capacidad_dentro_noche = 0;
        $capacidad_fuera_noche = 0;
        
        // Validar que todos los campos sean números positivos
        foreach ($turnos as $turno) {
            $turno_id = $turno['id'];
            $dentro = isset($_POST["capacidad_{$turno_id}_dentro"]) ? (int)$_POST["capacidad_{$turno_id}_dentro"] : 0;
            $fuera = isset($_POST["capacidad_{$turno_id}_fuera"]) ? (int)$_POST["capacidad_{$turno_id}_fuera"] : 0;
            
            if ($dentro < 0 || $fuera < 0 || $max_personas < 1) {
                $mensaje = 'Todos los valores deben ser números positivos.';
                $tipo_mensaje = 'error';
                $errores = true;
                break;
            }
            
            // Guardar valores según el turno
            if ($turno['nombre'] == 'mediodia') {
                $capacidad_dentro_mediodia = $dentro;
                $capacidad_fuera_mediodia = $fuera;
            } else {
                $capacidad_dentro_noche = $dentro;
                $capacidad_fuera_noche = $fuera;
            }
        }
        
        if (!$errores) {
            try {
                // Verificar si existe la tabla de configuración
                $stmt = $pdo->query("SHOW TABLES LIKE 'configuracion'");
                $tabla_existe = $stmt->rowCount() > 0;
                
                if (!$tabla_existe) {
                    // Crear la tabla de configuración si no existe
                    $pdo->exec("CREATE TABLE configuracion (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        max_personas_sin_aprobacion INT NOT NULL DEFAULT 6,
                        capacidad_dentro_mediodia INT NOT NULL DEFAULT 30,
                        capacidad_dentro_noche INT NOT NULL DEFAULT 35,
                        capacidad_fuera_mediodia INT NOT NULL DEFAULT 20,
                        capacidad_fuera_noche INT NOT NULL DEFAULT 25,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )");
                    
                    // Insertar valores
                    $stmt = $pdo->prepare("INSERT INTO configuracion (
                        max_personas_sin_aprobacion, 
                        capacidad_dentro_mediodia, 
                        capacidad_dentro_noche, 
                        capacidad_fuera_mediodia, 
                        capacidad_fuera_noche
                    ) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $max_personas, 
                        $capacidad_dentro_mediodia, 
                        $capacidad_dentro_noche, 
                        $capacidad_fuera_mediodia, 
                        $capacidad_fuera_noche
                    ]);
                } else {
                    // Verificar si existe un registro en la tabla
                    $stmt = $pdo->query("SELECT COUNT(*) FROM configuracion");
                    $count = $stmt->fetchColumn();
                    
                    if ($count === 0) {
                        // Insertar un nuevo registro
                        $stmt = $pdo->prepare("INSERT INTO configuracion (
                            max_personas_sin_aprobacion, 
                            capacidad_dentro_mediodia, 
                            capacidad_dentro_noche, 
                            capacidad_fuera_mediodia, 
                            capacidad_fuera_noche
                        ) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $max_personas, 
                            $capacidad_dentro_mediodia, 
                            $capacidad_dentro_noche, 
                            $capacidad_fuera_mediodia, 
                            $capacidad_fuera_noche
                        ]);
                    } else {
                        // Actualizar el registro existente
                        $stmt = $pdo->prepare("UPDATE configuracion SET 
                            max_personas_sin_aprobacion = ?, 
                            capacidad_dentro_mediodia = ?, 
                            capacidad_dentro_noche = ?, 
                            capacidad_fuera_mediodia = ?, 
                            capacidad_fuera_noche = ? 
                            WHERE id = 1");
                        $stmt->execute([
                            $max_personas, 
                            $capacidad_dentro_mediodia, 
                            $capacidad_dentro_noche, 
                            $capacidad_fuera_mediodia, 
                            $capacidad_fuera_noche
                        ]);
                    }
                }
                
                $mensaje = 'Configuración actualizada correctamente.';
                $tipo_mensaje = 'success';
                
                // Actualizar los valores en memoria
                $max_personas_sin_aprobacion = $max_personas;
                foreach ($turnos as $turno) {
                    $turno_id = $turno['id'];
                    if ($turno['nombre'] == 'mediodia') {
                        $capacidades[$turno_id]['dentro'] = $capacidad_dentro_mediodia;
                        $capacidades[$turno_id]['fuera'] = $capacidad_fuera_mediodia;
                    } else {
                        $capacidades[$turno_id]['dentro'] = $capacidad_dentro_noche;
                        $capacidades[$turno_id]['fuera'] = $capacidad_fuera_noche;
                    }
                }
                
                // Redirigir para forzar la recarga de la página
                header("Location: capacidad.php?updated=1");
                exit;
            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar la configuración: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Título de la página
$pageTitle = 'Gestión de Capacidad';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistema de Reservas</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Flatpickr para selección de fechas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Barra de navegación superior -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold">Panel de Administración</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="logout.php" class="ml-4 px-3 py-2 rounded-md text-sm font-medium text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900"><?php echo $pageTitle; ?></h1>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                    Volver al Panel
                </a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mb-4 p-4 rounded-md <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <!-- Información sobre la configuración -->
            <div class="bg-white p-4 rounded-md shadow-md mb-6">
                <h3 class="text-md font-medium text-gray-900 mb-2">Configuración General de Capacidad</h3>
                <p class="text-sm text-gray-600">
                    Esta configuración se aplica a todos los días. La capacidad máxima define el número de personas que pueden hacer una reserva sin necesidad de aprobación por parte del administrador.
                </p>
            </div>
            
            <!-- Formulario de Capacidad -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Configuración General de Capacidad
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Define la capacidad máxima del restaurante por turno y zona, y el número máximo de personas sin aprobación.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar">
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Capacidad máxima sin aprobación -->
                            <div class="bg-blue-50 p-4 rounded-md">
                                <h4 class="text-md font-medium text-gray-900 mb-4">
                                    Capacidad Máxima Sin Aprobación
                                </h4>
                                <div>
                                    <label for="max_personas_sin_aprobacion" class="block text-sm font-medium text-gray-700">
                                        Número máximo de personas
                                    </label>
                                    <div class="mt-1">
                                        <input type="number" name="max_personas_sin_aprobacion" id="max_personas_sin_aprobacion" 
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                               value="<?php echo $max_personas_sin_aprobacion; ?>" min="1" required>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Las reservas que excedan este número requerirán aprobación del administrador.
                                    </p>
                                </div>
                            </div>
                            <?php foreach ($turnos as $turno): ?>
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">
                                        Turno: <?php echo ucfirst($turno['nombre']); ?>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="capacidad_<?php echo $turno['id']; ?>_dentro" class="block text-sm font-medium text-gray-700">
                                                Capacidad Interior
                                            </label>
                                            <div class="mt-1">
                                                <input type="number" name="capacidad_<?php echo $turno['id']; ?>_dentro" id="capacidad_<?php echo $turno['id']; ?>_dentro" 
                                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                       value="<?php echo isset($capacidades[$turno['id']]['dentro']) ? $capacidades[$turno['id']]['dentro'] : 0; ?>" min="0" required>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Número máximo de personas en el interior.
                                            </p>
                                        </div>
                                        <div>
                                            <label for="capacidad_<?php echo $turno['id']; ?>_fuera" class="block text-sm font-medium text-gray-700">
                                                Capacidad Terraza
                                            </label>
                                            <div class="mt-1">
                                                <input type="number" name="capacidad_<?php echo $turno['id']; ?>_fuera" id="capacidad_<?php echo $turno['id']; ?>_fuera" 
                                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                       value="<?php echo isset($capacidades[$turno['id']]['fuera']) ? $capacidades[$turno['id']]['fuera'] : 0; ?>" min="0" required>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Número máximo de personas en la terraza.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Formulario de Disponibilidad por Día -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-8">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Disponibilidad por Día
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Gestiona qué zonas y turnos están disponibles para cada día específico.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar_disponibilidad">
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Selector de rango de fechas -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Seleccionar Rango de Fechas
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                                            Fecha de Inicio
                                        </label>
                                        <input type="date" name="fecha_inicio" id="fecha_inicio" 
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                               value="<?php echo $fecha; ?>" required>
                                    </div>
                                    <div>
                                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                                            Fecha de Fin
                                        </label>
                                        <input type="date" name="fecha_fin" id="fecha_fin" 
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                               value="<?php echo $fecha; ?>" required>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">
                                    Selecciona un rango de fechas para gestionar la disponibilidad.
                                </p>
                            </div>
                            
                            <!-- Disponibilidad por turno y zona -->
                            <div class="bg-yellow-50 p-4 rounded-md mb-4">
                                <h4 class="text-md font-medium text-yellow-800 mb-2">Información Importante</h4>
                                <p class="text-sm text-yellow-700 mb-2">
                                    Por defecto, todas las zonas y turnos están <strong>disponibles</strong>. Marca las casillas para activar la disponibilidad de cada zona y turno.
                                </p>
                                <p class="text-sm text-yellow-700">
                                    Si una casilla no está marcada, esa combinación de zona y turno no estará disponible para reservas.
                                </p>
                            </div>
                            
                            <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                                <div class="px-4 py-5 sm:p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4" id="rango_fechas_titulo">
                                        Disponibilidad para el día <?php echo date('d/m/Y', strtotime($fecha)); ?>
                                    </h3>
                                    
                                    <?php foreach ($turnos as $turno): ?>
                                    <div class="mb-6 pb-6 border-b border-gray-200 last:border-b-0 last:mb-0 last:pb-0">
                                        <h4 class="text-md font-medium text-gray-800 mb-4">Turno: <?php echo ucfirst($turno['nombre']); ?> (<?php echo substr($turno['hora_inicio'], 0, 5); ?> - <?php echo substr($turno['hora_fin'], 0, 5); ?>)</h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Zona Interior -->
                                            <div class="flex items-center">
                                                <input type="checkbox" id="disponible_<?php echo $turno['id']; ?>_dentro" name="disponible_<?php echo $turno['id']; ?>_dentro" 
                                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                       <?php echo (isset($disponibilidad[$turno['id']]['dentro']) && $disponibilidad[$turno['id']]['dentro']) ? 'checked' : ''; ?>>
                                                <label for="disponible_<?php echo $turno['id']; ?>_dentro" class="ml-2 block text-sm text-gray-900">
                                                    Zona Interior Disponible
                                                </label>
                                            </div>
                                            
                                            <!-- Zona Terraza -->
                                            <div class="flex items-center">
                                                <input type="checkbox" id="disponible_<?php echo $turno['id']; ?>_fuera" name="disponible_<?php echo $turno['id']; ?>_fuera" 
                                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                       <?php echo (isset($disponibilidad[$turno['id']]['fuera']) && $disponibilidad[$turno['id']]['fuera']) ? 'checked' : ''; ?>>
                                                <label for="disponible_<?php echo $turno['id']; ?>_fuera" class="ml-2 block text-sm text-gray-900">
                                                    Zona Terraza Disponible
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Guardar Disponibilidad
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes. Todos los derechos reservados.
            </p>
        </div>
    </footer>

    <!-- Scripts de JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Inicializar datepicker para el selector de fechas
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración básica del datepicker
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            
            // Configuración común para ambos datepickers
            const datepickerConfig = {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                locale: 'es',
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    // Actualizar el título con el rango de fechas
                    updateFechaTitulo();
                }
            };
            
            // Inicializar datepickers
            flatpickr(fechaInicio, datepickerConfig);
            
            // Configurar fecha fin para que no sea anterior a la fecha de inicio
            flatpickr(fechaFin, {
                ...datepickerConfig,
                onOpen: function(selectedDates) {
                    const fechaInicioVal = new Date(fechaInicio.value);
                    this.set('minDate', fechaInicioVal);
                    
                    // Si la fecha fin es anterior a la de inicio, actualizarla
                    const fechaFinVal = new Date(fechaFin.value);
                    if (fechaFinVal < fechaInicioVal) {
                        this.setDate(fechaInicioVal);
                        updateFechaTitulo();
                    }
                }
            });
            
            // Actualizar el título con el rango de fechas
            function updateFechaTitulo() {
                const inicio = fechaInicio.value ? new Date(fechaInicio.value) : null;
                const fin = fechaFin.value ? new Date(fechaFin.value) : null;
                
                if (inicio && fin) {
                    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
                    const inicioStr = inicio.toLocaleDateString('es-ES', opciones);
                    const finStr = fin.toLocaleDateString('es-ES', opciones);
                    
                    if (inicio.getTime() === fin.getTime()) {
                        document.getElementById('rango_fechas_titulo').textContent = 
                            `Disponibilidad para el día ${inicioStr}`;
                    } else {
                        document.getElementById('rango_fechas_titulo').textContent = 
                            `Disponibilidad del ${inicioStr} al ${finStr}`;
                    }
                }
            }
            
            // Actualizar título al cargar la página
            updateFechaTitulo();
            
            // Si hay un parámetro de fecha en la URL, establecerlo en los inputs
            const urlParams = new URLSearchParams(window.location.search);
            const fechaParam = urlParams.get('fecha');
            if (fechaParam) {
                fechaInicio.value = fechaParam;
                fechaFin.value = fechaParam;
                updateFechaTitulo();
            }
        });
    </script>
</body>
</html>
