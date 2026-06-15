<?php
/**
 * gestionar_calendario.php
 * 
 * Panel mejorado para la administración de días disponibles del restaurante.
 * Soluciona problemas de visualización en el calendario del usuario.
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

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$mes_actual = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$anio_actual = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$dias_disponibles = [];
$dias_con_reservas = [];

// Conexión a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Si se envió el formulario para guardar cambios
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cambios'])) {
        // Iniciar transacción para asegurar integridad
        $pdo->beginTransaction();
        
        try {
            // Obtener todos los turnos disponibles
            $stmtTurnos = $pdo->query("SELECT id FROM turnos");
            $turnos = $stmtTurnos->fetchAll(PDO::FETCH_COLUMN);
            
            // Si no hay turnos, crearlos
            if (empty($turnos)) {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS turnos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        nombre VARCHAR(50) NOT NULL,
                        hora_inicio TIME NOT NULL,
                        hora_fin TIME NOT NULL
                    )
                ");
                
                $pdo->exec("
                    INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
                    ('mediodia', '13:00', '16:00'),
                    ('noche', '20:00', '23:00')
                ");
                
                $stmtTurnos = $pdo->query("SELECT id FROM turnos");
                $turnos = $stmtTurnos->fetchAll(PDO::FETCH_COLUMN);
            }
            
            // Crear la tabla dias_disponibles si no existe
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS dias_disponibles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fecha DATE NOT NULL,
                    turno_id INT NOT NULL,
                    zona VARCHAR(50) NOT NULL,
                    disponible TINYINT(1) NOT NULL DEFAULT 1,
                    UNIQUE KEY fecha_turno_zona (fecha, turno_id, zona)
                )
            ");
            
            // Obtener los días seleccionados del formulario
            $dias_seleccionados = isset($_POST['dias_disponibles']) ? $_POST['dias_disponibles'] : [];
            $mes_post = $_POST['mes'] ?? $mes_actual;
            $anio_post = $_POST['anio'] ?? $anio_actual;
            
            // Eliminar reservas si es necesario
            $eliminar_reservas = isset($_POST['eliminar_reservas']) ? $_POST['eliminar_reservas'] : [];
            foreach ($eliminar_reservas as $fecha) {
                $stmt = $pdo->prepare("DELETE FROM reservas WHERE fecha = ?");
                $stmt->execute([$fecha]);
            }
            
            // Generar todos los días del mes
            $primer_dia = sprintf('%04d-%02d-01', $anio_post, $mes_post);
            $ultimo_dia = date('Y-m-t', strtotime($primer_dia));
            
            $inicio = new DateTime($primer_dia);
            $fin = new DateTime($ultimo_dia);
            $intervalo = new DateInterval('P1D');
            $periodo = new DatePeriod($inicio, $intervalo, $fin->modify('+1 day'));
            
            $todos_los_dias = [];
            foreach ($periodo as $fecha) {
                $todos_los_dias[] = $fecha->format('Y-m-d');
            }
            
            // IMPORTANTE: Marcar todos los días del mes como NO disponibles primero
            foreach ($todos_los_dias as $fecha) {
                foreach ($turnos as $turno_id) {
                    foreach (['dentro', 'fuera'] as $zona) {
                        // Verificar si ya existe el registro
                        $stmt = $pdo->prepare("
                            SELECT id FROM dias_disponibles 
                            WHERE fecha = ? AND zona = ? AND turno_id = ?
                        ");
                        $stmt->execute([$fecha, $zona, $turno_id]);
                        $existe = $stmt->fetch();
                        
                        if ($existe) {
                            // Actualizar como no disponible
                            $stmt = $pdo->prepare("
                                UPDATE dias_disponibles 
                                SET disponible = 0 
                                WHERE fecha = ? AND zona = ? AND turno_id = ?
                            ");
                            $stmt->execute([$fecha, $zona, $turno_id]);
                        } else {
                            // Insertar nuevo registro como no disponible
                            $stmt = $pdo->prepare("
                                INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible) 
                                VALUES (?, ?, ?, 0)
                            ");
                            $stmt->execute([$fecha, $zona, $turno_id]);
                        }
                    }
                }
            }
            
            // Luego, marcar días seleccionados como disponibles
            foreach ($dias_seleccionados as $fecha) {
                foreach ($turnos as $turno_id) {
                    foreach (['dentro', 'fuera'] as $zona) {
                        $stmt = $pdo->prepare("
                            UPDATE dias_disponibles 
                            SET disponible = 1 
                            WHERE fecha = ? AND zona = ? AND turno_id = ?
                        ");
                        $stmt->execute([$fecha, $zona, $turno_id]);
                    }
                }
            }
            
            $pdo->commit();
            
            $mensaje = "Los días disponibles se han actualizado correctamente.";
            $tipo_mensaje = "success";
            
            // Actualizar variables para mostrar el mes recién actualizado
            $mes_actual = $mes_post;
            $anio_actual = $anio_post;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = "Error al actualizar los días: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
    
    // Consultar días disponibles para el mes actual
    $primer_dia = sprintf('%04d-%02d-01', $anio_actual, $mes_actual);
    $ultimo_dia = date('Y-m-t', strtotime($primer_dia));
    
    $stmt = $pdo->prepare("
        SELECT DISTINCT fecha 
        FROM dias_disponibles 
        WHERE fecha BETWEEN ? AND ? 
        AND disponible = 1
    ");
    $stmt->execute([$primer_dia, $ultimo_dia]);
    $dias_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Consultar días con reservas
    $stmt = $pdo->prepare("
        SELECT DISTINCT fecha, COUNT(*) as num_reservas
        FROM reservas 
        WHERE fecha BETWEEN ? AND ?
        GROUP BY fecha
    ");
    $stmt->execute([$primer_dia, $ultimo_dia]);
    $reservas = $stmt->fetchAll();
    
    $dias_con_reservas = [];
    foreach ($reservas as $reserva) {
        $dias_con_reservas[$reserva['fecha']] = $reserva['num_reservas'];
    }
    
} catch (PDOException $e) {
    $mensaje = "Error de conexión: " . $e->getMessage();
    $tipo_mensaje = "error";
}

// Función para obtener el nombre del mes
function obtenerNombreMes($mes) {
    $nombres = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return $nombres[$mes] ?? '';
}

// Obtener número de días en el mes
$num_dias = date('t', strtotime($primer_dia));

// Obtener el día de la semana del primer día (0: domingo, 6: sábado)
$primer_dia_semana = date('w', strtotime($primer_dia));
// Ajustar para que la semana comience en lunes (0: lunes, 6: domingo)
$primer_dia_semana = ($primer_dia_semana == 0) ? 6 : $primer_dia_semana - 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Calendario - Administración</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .calendar-day {
            aspect-ratio: 1/1;
        }
        .calendar-day.available {
            background-color: #a7f3d0;
            border-color: #10b981;
        }
        .calendar-day:hover {
            background-color: #f3f4f6;
        }
        /* Estilo para días con reservas */
        .has-reservations {
            position: relative;
        }
        .reservation-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ef4444;
            color: white;
            font-size: 0.625rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión del Calendario</h1>
            <p class="text-gray-600">Administre los días disponibles para reservas</p>
        </header>
        
        <?php if ($mensaje): ?>
            <div class="mb-8 p-4 rounded-md <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Selector de mes y año -->
        <div class="mb-8 bg-white rounded-lg shadow-md p-6">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="flex flex-wrap items-center gap-4">
                <div>
                    <label for="mes" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                    <select id="mes" name="mes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i === $mes_actual ? 'selected' : ''; ?>>
                                <?php echo obtenerNombreMes($i); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div>
                    <label for="anio" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <select id="anio" name="anio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <?php for ($i = date('Y'); $i <= date('Y') + 2; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i === $anio_actual ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="self-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                        Ver Calendario
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Calendario -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form id="form-calendario" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <input type="hidden" name="mes" value="<?php echo $mes_actual; ?>">
                <input type="hidden" name="anio" value="<?php echo $anio_actual; ?>">
                
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        <?php echo obtenerNombreMes($mes_actual) . ' ' . $anio_actual; ?>
                    </h2>
                    
                    <div class="grid grid-cols-7 gap-2 mb-4">
                        <!-- Días de la semana -->
                        <div class="text-center font-semibold text-gray-700">Lun</div>
                        <div class="text-center font-semibold text-gray-700">Mar</div>
                        <div class="text-center font-semibold text-gray-700">Mié</div>
                        <div class="text-center font-semibold text-gray-700">Jue</div>
                        <div class="text-center font-semibold text-gray-700">Vie</div>
                        <div class="text-center font-semibold text-gray-700">Sáb</div>
                        <div class="text-center font-semibold text-gray-700">Dom</div>
                        
                        <!-- Celdas vacías para los días anteriores al primer día del mes -->
                        <?php for ($i = 0; $i < $primer_dia_semana; $i++): ?>
                            <div class="border border-gray-200 rounded-md calendar-day opacity-0"></div>
                        <?php endfor; ?>
                        
                        <!-- Días del mes -->
                        <?php for ($dia = 1; $dia <= $num_dias; $dia++): ?>
                            <?php 
                                $fecha = sprintf('%04d-%02d-%02d', $anio_actual, $mes_actual, $dia);
                                $es_disponible = in_array($fecha, $dias_disponibles);
                                $tiene_reservas = isset($dias_con_reservas[$fecha]) && $dias_con_reservas[$fecha] > 0;
                                $num_reservas = $dias_con_reservas[$fecha] ?? 0;
                            ?>
                            <div class="relative">
                                <label for="dia-<?php echo $dia; ?>" class="flex items-center justify-center calendar-day border <?php echo $es_disponible ? 'available' : 'border-gray-200'; ?> rounded-md cursor-pointer p-2 text-center">
                                    <input 
                                        type="checkbox" 
                                        id="dia-<?php echo $dia; ?>" 
                                        name="dias_disponibles[]" 
                                        value="<?php echo $fecha; ?>" 
                                        class="hidden calendar-checkbox" 
                                        <?php echo $es_disponible ? 'checked' : ''; ?>
                                        data-fecha="<?php echo $fecha; ?>"
                                        data-tiene-reservas="<?php echo $tiene_reservas ? '1' : '0'; ?>"
                                    >
                                    <span><?php echo $dia; ?></span>
                                </label>
                                <?php if ($tiene_reservas): ?>
                                    <span class="reservation-badge" title="<?php echo $num_reservas . ' reserva(s)'; ?>"><?php echo $num_reservas; ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="flex items-center justify-between mt-6">
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-sm bg-a7f3d0 border border-10b981"></div>
                                <span class="text-sm text-gray-700">Día disponible</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-sm bg-white border border-gray-200"></div>
                                <span class="text-sm text-gray-700">Día no disponible</span>
                            </div>
                        </div>
                        
                        <div>
                            <button type="button" id="seleccionar-todos" class="text-blue-600 hover:text-blue-800 text-sm mr-4">
                                Seleccionar todos
                            </button>
                            <button type="button" id="deseleccionar-todos" class="text-blue-600 hover:text-blue-800 text-sm">
                                Deseleccionar todos
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="guardar_cambios" value="1" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Modal de confirmación para días con reservas -->
        <div id="modal-confirmacion" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirmación necesaria
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="modal-message">
                                    Este día tiene reservas activas. ¿Estás seguro de que deseas marcarlo como no disponible?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="btn-eliminar-reservas" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Eliminar reservas
                    </button>
                    <button type="button" id="btn-cancelar" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Variables para el modal
        const modal = document.getElementById('modal-confirmacion');
        const btnEliminarReservas = document.getElementById('btn-eliminar-reservas');
        const btnCancelar = document.getElementById('btn-cancelar');
        let checkboxActual = null;
        
        // Función para actualizar el estilo de las casillas del calendario
        function actualizarEstiloCalendario() {
            const checkboxes = document.querySelectorAll('.calendar-checkbox');
            checkboxes.forEach(checkbox => {
                const label = checkbox.closest('label');
                if (checkbox.checked) {
                    label.classList.add('available');
                } else {
                    label.classList.remove('available');
                }
            });
        }
        
        // Gestionar clics en las casillas del calendario
        document.querySelectorAll('.calendar-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                // Si se está desmarcando un día con reservas, mostrar modal de confirmación
                if (!this.checked && this.dataset.tieneReservas === '1') {
                    e.preventDefault();
                    
                    checkboxActual = this;
                    const fecha = this.dataset.fecha;
                    
                    // Formatear fecha para mostrar en el modal (YYYY-MM-DD a DD/MM/YYYY)
                    const fechaFormateada = fecha.split('-').reverse().join('/');
                    
                    document.getElementById('modal-message').textContent = 
                        `El día ${fechaFormateada} tiene reservas activas. ¿Estás seguro de que deseas marcarlo como no disponible? Se eliminarán todas las reservas para este día.`;
                    
                    modal.classList.remove('hidden');
                } else {
                    // Actualizar estilo
                    actualizarEstiloCalendario();
                }
            });
        });
        
        // Botones Seleccionar/Deseleccionar todos
        document.getElementById('seleccionar-todos').addEventListener('click', function() {
            document.querySelectorAll('.calendar-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
            actualizarEstiloCalendario();
        });
        
        document.getElementById('deseleccionar-todos').addEventListener('click', function() {
            const checkboxesConReservas = [];
            
            // Primero recopilar checkboxes con reservas
            document.querySelectorAll('.calendar-checkbox[data-tiene-reservas="1"]').forEach(checkbox => {
                if (checkbox.checked) {
                    checkboxesConReservas.push(checkbox);
                }
            });
            
            // Si hay días con reservas que se van a desmarcar, mostrar advertencia
            if (checkboxesConReservas.length > 0) {
                if (confirm(`Hay ${checkboxesConReservas.length} días con reservas. ¿Deseas continuar y eliminar todas esas reservas?`)) {
                    // Crear campos ocultos para indicar que se deben eliminar las reservas
                    checkboxesConReservas.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'eliminar_reservas[]';
                        input.value = checkbox.dataset.fecha;
                        document.getElementById('form-calendario').appendChild(input);
                    });
                } else {
                    // Cancelar la operación
                    return;
                }
            }
            
            // Desmarcar todos los checkboxes
            document.querySelectorAll('.calendar-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            actualizarEstiloCalendario();
        });
        
        // Gestión del modal
        btnEliminarReservas.addEventListener('click', function() {
            // Crear un campo oculto para indicar que se deben eliminar las reservas
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'eliminar_reservas[]';
            input.value = checkboxActual.dataset.fecha;
            document.getElementById('form-calendario').appendChild(input);
            
            // Desmarcar el checkbox
            checkboxActual.checked = false;
            actualizarEstiloCalendario();
            
            // Ocultar el modal
            modal.classList.add('hidden');
        });
        
        btnCancelar.addEventListener('click', function() {
            // Volver a marcar el checkbox
            checkboxActual.checked = true;
            
            // Ocultar el modal
            modal.classList.add('hidden');
        });
        
        // Color de fondo verde para días disponibles
        document.addEventListener('DOMContentLoaded', function() {
            actualizarEstiloCalendario();
        });
    </script>
</body>
</html>
