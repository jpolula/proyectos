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

// Conectar a la base de datos
$pdo = new PDO($dsn, $user, $pass, $options);

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$pageTitle = 'Gestión de Días Disponibles';

// Obtener mes y año seleccionados o usar el mes actual
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

// Nombres de los meses en español
$nombres_meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

// Mensaje de actualización exitosa
if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1) {
    $mensaje = 'Días disponibles actualizados correctamente.';
    $tipo_mensaje = 'success';
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $mes_post = isset($_POST['mes']) ? (int)$_POST['mes'] : (int)date('m');
        $anio_post = isset($_POST['anio']) ? (int)$_POST['anio'] : (int)date('Y');
        $dias_seleccionados = isset($_POST['dias_disponibles']) ? $_POST['dias_disponibles'] : [];
        $eliminar_reservas = isset($_POST['eliminar_reservas']) ? $_POST['eliminar_reservas'] : [];
        
        try {
            $pdo->beginTransaction();
            
            // Obtener todos los días del mes
            $fecha_inicio = sprintf('%04d-%02d-01', $anio_post, $mes_post);
            $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
            
            // Obtener los turnos disponibles
            $stmt = $pdo->query("SELECT id FROM turnos");
            $turnos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Obtener todos los días del mes actual
            $primer_dia = mktime(0, 0, 0, $mes_post, 1, $anio_post);
            $dias_en_mes = date('t', $primer_dia);
            $todos_los_dias = [];
            
            for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                $todos_los_dias[] = sprintf('%04d-%02d-%02d', $anio_post, $mes_post, $dia);
            }
            
            // Obtener los días que estaban disponibles antes
            $stmt = $pdo->prepare("
                SELECT DISTINCT fecha FROM dias_disponibles 
                WHERE fecha BETWEEN ? AND ? AND disponible = 1
            ");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $dias_antes_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Identificar días que se han deshabilitado
            $dias_deshabilitados = array_diff($dias_antes_disponibles, $dias_seleccionados);
            
            // Para cada día deshabilitado, verificar si hay reservas
            foreach ($dias_deshabilitados as $dia) {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) FROM reservas 
                    WHERE fecha = ?
                ");
                $stmt->execute([$dia]);
                $num_reservas = $stmt->fetchColumn();
                
                if ($num_reservas > 0 && !in_array($dia, $eliminar_reservas)) {
                    // Si hay reservas y no se ha confirmado la eliminación, 
                    // añadir el día a los seleccionados para mantenerlo disponible
                    $dias_seleccionados[] = $dia;
                } elseif ($num_reservas > 0 && in_array($dia, $eliminar_reservas)) {
                    // Si hay reservas y se ha confirmado la eliminación, eliminar las reservas
                    $stmt = $pdo->prepare("DELETE FROM reservas WHERE fecha = ?");
                    $stmt->execute([$dia]);
                }
            }
            
            // Identificar días que no están seleccionados (para deshabilitarlos)
            $dias_no_seleccionados = array_diff($todos_los_dias, $dias_seleccionados);
            
            // Primero, deshabilitar todos los días no seleccionados
            foreach ($dias_no_seleccionados as $dia) {
                foreach (['dentro', 'fuera'] as $zona) {
                    foreach ($turnos as $turno_id) {
                        // Verificar si ya existe el registro
                        $stmt = $pdo->prepare("
                            SELECT id FROM dias_disponibles 
                            WHERE fecha = ? AND zona = ? AND turno_id = ?
                        ");
                        $stmt->execute([$dia, $zona, $turno_id]);
                        $existe = $stmt->fetch();
                        
                        if ($existe) {
                            // Actualizar el registro existente a no disponible
                            $stmt = $pdo->prepare("
                                UPDATE dias_disponibles 
                                SET disponible = 0 
                                WHERE fecha = ? AND zona = ? AND turno_id = ?
                            ");
                            $stmt->execute([$dia, $zona, $turno_id]);
                        } else {
                            // Insertar nuevo registro como no disponible
                            $stmt = $pdo->prepare("
                                INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible) 
                                VALUES (?, ?, ?, 0)
                            ");
                            $stmt->execute([$dia, $zona, $turno_id]);
                        }
                    }
                }
            }
            
            // Luego, habilitar todos los días seleccionados
            foreach ($dias_seleccionados as $dia) {
                foreach (['dentro', 'fuera'] as $zona) {
                    foreach ($turnos as $turno_id) {
                        // Verificar si ya existe el registro
                        $stmt = $pdo->prepare("
                            SELECT id FROM dias_disponibles 
                            WHERE fecha = ? AND zona = ? AND turno_id = ?
                        ");
                        $stmt->execute([$dia, $zona, $turno_id]);
                        $existe = $stmt->fetch();
                        
                        if ($existe) {
                            // Actualizar el registro existente a disponible
                            $stmt = $pdo->prepare("
                                UPDATE dias_disponibles 
                                SET disponible = 1 
                                WHERE fecha = ? AND zona = ? AND turno_id = ?
                            ");
                            $stmt->execute([$dia, $zona, $turno_id]);
                        } else {
                            // Insertar nuevo registro como disponible
                            $stmt = $pdo->prepare("
                                INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible) 
                                VALUES (?, ?, ?, 1)
                            ");
                            $stmt->execute([$dia, $zona, $turno_id]);
                        }
                    }
                }
            }
            
            $pdo->commit();
            
            // Redirigir para evitar reenvío del formulario
            header("Location: " . $_SERVER['PHP_SELF'] . "?mes=$mes_post&anio=$anio_post&actualizado=1");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = 'Error al actualizar los días disponibles: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Generar el calendario
$primer_dia = mktime(0, 0, 0, $mes, 1, $anio);
$dias_en_mes = date('t', $primer_dia);
$dia_semana_inicio = date('N', $primer_dia); // 1 (lunes) a 7 (domingo)

// Obtener los días disponibles para el mes y año seleccionados
$fecha_inicio = sprintf('%04d-%02d-01', $anio, $mes);
$fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

$stmt = $pdo->prepare("
    SELECT DISTINCT fecha 
    FROM dias_disponibles 
    WHERE fecha BETWEEN ? AND ? 
    AND disponible = 1
");
$stmt->execute([$fecha_inicio, $fecha_fin]);
$dias_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Panel de Administración</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flatpickr para selectores de fecha -->
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
                    <a href="logout.php" class="ml-4 px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Cerrar Sesión</a>
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
            
            <!-- Selector de Mes y Año -->
            <div class="bg-white p-4 rounded-md shadow-md mb-6">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="flex flex-wrap items-end space-x-4">
                    <div>
                        <label for="mes" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                        <select id="mes" name="mes" class="p-2 border border-gray-300 rounded-md">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i === $mes ? 'selected' : ''; ?>>
                                    <?php echo $nombres_meses[$i]; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label for="anio" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                        <select id="anio" name="anio" class="p-2 border border-gray-300 rounded-md">
                            <?php for ($i = 2023; $i <= 2030; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i === $anio ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                            Ver Calendario
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Formulario de Días Disponibles -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Configuración de Días Disponibles para <?php echo $nombres_meses[$mes] . ' ' . $anio; ?>
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Selecciona los días en los que el restaurante estará abierto para reservas.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form id="form-dias-disponibles" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="mes" value="<?php echo $mes; ?>">
                        <input type="hidden" name="anio" value="<?php echo $anio; ?>">
                        
                        <div class="mb-6">
                            <div class="flex justify-between mb-4">
                                <div>
                                    <button type="button" id="seleccionar_todos" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300 mr-2">
                                        Seleccionar Todos
                                    </button>
                                    <button type="button" id="deseleccionar_todos" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                                        Deseleccionar Todos
                                    </button>
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                                    Guardar Cambios
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-7 gap-2 text-center font-medium text-gray-700 mb-2">
                                <div>Lun</div>
                                <div>Mar</div>
                                <div>Mié</div>
                                <div>Jue</div>
                                <div>Vie</div>
                                <div>Sáb</div>
                                <div>Dom</div>
                            </div>
                            <div class="grid grid-cols-7 gap-2">
                                <?php
                                // Espacios en blanco para los días anteriores al primer día del mes
                                for ($i = 1; $i < $dia_semana_inicio; $i++) {
                                    echo '<div></div>';
                                }
                                
                                // Días del mes
                                for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                                    $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                                    $es_disponible = in_array($fecha, $dias_disponibles);
                                    
                                    // Verificar si hay reservas para este día
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE fecha = ?");
                                    $stmt->execute([$fecha]);
                                    $num_reservas = $stmt->fetchColumn();
                                    $tiene_reservas = $num_reservas > 0;
                                    ?>
                                    <div class="p-2 border rounded-md <?php echo $es_disponible ? 'bg-green-100 border-green-300' : 'bg-gray-50 border-gray-200'; ?> <?php echo $tiene_reservas ? 'relative' : ''; ?>">
                                        <label class="flex items-center justify-center cursor-pointer">
                                            <input type="checkbox" name="dias_disponibles[]" value="<?php echo $fecha; ?>" 
                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2 dia-checkbox"
                                                   data-fecha="<?php echo $fecha; ?>"
                                                   data-tiene-reservas="<?php echo $tiene_reservas ? '1' : '0'; ?>"
                                                   <?php echo $es_disponible ? 'checked' : ''; ?>>
                                            <span><?php echo $dia; ?></span>
                                        </label>
                                        <?php if ($tiene_reservas): ?>
                                            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full"><?php echo $num_reservas; ?></span>
                                            <input type="hidden" name="dias_con_reservas[]" value="<?php echo $fecha; ?>">
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="modal-confirmacion" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirmación necesaria
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="modal-message">
                                Este día tiene reservas activas. ¿Deseas eliminar todas las reservas para este día?
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn-confirmar-eliminar" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Eliminar reservas
                </button>
                <button type="button" id="btn-cancelar-eliminar" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancelar
                </button>
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

    <script>
        // Variables para el modal
        const modal = document.getElementById('modal-confirmacion');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');
        const btnCancelar = document.getElementById('btn-cancelar-eliminar');
        let checkboxActual = null;
        let fechaActual = null;
        
        // Función para mostrar el modal de confirmación
        function mostrarModalConfirmacion(checkbox) {
            checkboxActual = checkbox;
            fechaActual = checkbox.dataset.fecha;
            
            // Actualizar el mensaje del modal
            document.getElementById('modal-message').textContent = 
                'El día ' + formatearFecha(fechaActual) + ' tiene reservas activas. ¿Deseas eliminar todas las reservas para este día?';
            
            // Mostrar el modal
            modal.classList.remove('hidden');
        }
        
        // Formatear fecha para mostrar en el modal
        function formatearFecha(fecha) {
            const partes = fecha.split('-');
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }
        
        // Función para actualizar el estilo del checkbox
        function actualizarEstiloCheckbox(checkbox) {
            const contenedor = checkbox.closest('div');
            if (checkbox.checked) {
                contenedor.classList.remove('bg-gray-50', 'border-gray-200');
                contenedor.classList.add('bg-green-100', 'border-green-300');
            } else {
                contenedor.classList.remove('bg-green-100', 'border-green-300');
                contenedor.classList.add('bg-gray-50', 'border-gray-200');
            }
        }
        
        // Manejar clics en los checkboxes
        document.querySelectorAll('.dia-checkbox').forEach(function(checkbox) {
            checkbox.addEventListener('click', function(e) {
                // Si estamos desmarcando y tiene reservas, mostrar confirmación
                if (checkbox.checked && checkbox.dataset.tieneReservas === '1') {
                    e.preventDefault(); // Prevenir el cambio de estado
                    mostrarModalConfirmacion(checkbox);
                }
                // En caso contrario, permitir el cambio y actualizar estilos
                else {
                    setTimeout(() => {
                        actualizarEstiloCheckbox(checkbox);
                    }, 0);
                }
            });
        });
        
        // Botón "Seleccionar Todos"
        document.getElementById('seleccionar_todos').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.dia-checkbox').forEach(function(checkbox) {
                checkbox.checked = true;
                actualizarEstiloCheckbox(checkbox);
            });
        });
        
        // Botón "Deseleccionar Todos"
        document.getElementById('deseleccionar_todos').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Identificar checkboxes con reservas
            const checkboxesConReservas = [];
            document.querySelectorAll('.dia-checkbox').forEach(function(checkbox) {
                if (checkbox.checked && checkbox.dataset.tieneReservas === '1') {
                    checkboxesConReservas.push(checkbox);
                } else {
                    checkbox.checked = false;
                    actualizarEstiloCheckbox(checkbox);
                }
            });
            
            // Si hay días con reservas, mostrar confirmación para el primero
            if (checkboxesConReservas.length > 0) {
                mostrarModalConfirmacion(checkboxesConReservas[0]);
            }
        });
        
        // Evento para confirmar la eliminación de reservas
        btnConfirmar.addEventListener('click', function() {
            // Crear un campo oculto para indicar que se deben eliminar las reservas
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'eliminar_reservas[]';
            input.value = fechaActual;
            document.getElementById('form-dias-disponibles').appendChild(input);
            
            // Desmarcar el checkbox
            checkboxActual.checked = false;
            actualizarEstiloCheckbox(checkboxActual);
            
            // Ocultar el modal
            modal.classList.add('hidden');
        });
        
        // Evento para cancelar la eliminación
        btnCancelar.addEventListener('click', function() {
            // Mantener el checkbox marcado
            checkboxActual.checked = true;
            actualizarEstiloCheckbox(checkboxActual);
            
            // Ocultar el modal
            modal.classList.add('hidden');
        });
    </script>
</body>
</html>
