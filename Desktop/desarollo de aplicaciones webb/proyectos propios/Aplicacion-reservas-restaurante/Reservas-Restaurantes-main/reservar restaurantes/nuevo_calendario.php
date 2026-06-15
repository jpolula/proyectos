<?php
// nuevo_calendario.php - Implementación de un calendario simple y funcional
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

// Obtener el número máximo de personas sin aprobación desde la base de datos
$max_personas_sin_aprobacion = 8; // Valor por defecto
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        $max_personas_sin_aprobacion = $config['max_personas_sin_aprobacion'];
    }
} catch (PDOException $e) {
    // Silenciar error y usar valor por defecto
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y recoger los datos de la reserva
    $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
    $turno = isset($_POST['turno']) ? trim($_POST['turno']) : '';
    $zona = isset($_POST['zona']) ? trim($_POST['zona']) : '';
    $hora = isset($_POST['hora']) ? trim($_POST['hora']) : '';
    
    // Validación básica
    $errores = [];
    
    if (empty($fecha)) {
        $errores['fecha'] = 'La fecha es obligatoria';
    }
    
    if (empty($turno)) {
        $errores['turno'] = 'El turno es obligatorio';
    }
    
    if (empty($zona)) {
        $errores['zona'] = 'La zona es obligatoria';
    }
    
    if (empty($hora)) {
        $errores['hora'] = 'La hora es obligatoria';
    }
    
    // Si no hay errores, mostrar los datos seleccionados
    if (empty($errores)) {
        $mensaje_exito = "Reserva seleccionada correctamente: Fecha: $fecha, Turno: $turno, Zona: $zona, Hora: $hora";
    }
}

// Generar datos de ejemplo para el calendario
function obtenerDiasDisponibles() {
    $dias = [];
    $fecha_actual = new DateTime('2025-05-01');
    $fecha_fin = new DateTime('2025-05-31');
    
    while ($fecha_actual <= $fecha_fin) {
        $fecha_str = $fecha_actual->format('Y-m-d');
        $dias[] = $fecha_str;
        $fecha_actual->modify('+1 day');
    }
    
    return $dias;
}

// Generar horas disponibles según el turno
function obtenerHorasPorTurno($turno) {
    if ($turno == 'mediodia') {
        return ["13:00", "13:30", "14:00", "14:30", "15:00", "15:30"];
    } else if ($turno == 'noche') {
        return ["20:00", "20:30", "21:00", "21:30", "22:00", "22:30"];
    }
    return [];
}

// Obtener días disponibles
$dias_disponibles = obtenerDiasDisponibles();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Calendario - Sistema de Reservas</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Vanilla Calendar (más simple que Flatpickr) -->
    <link href="https://cdn.jsdelivr.net/npm/@uvarov.frontend/vanilla-calendar/build/vanilla-calendar.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@uvarov.frontend/vanilla-calendar/build/vanilla-calendar.min.js" defer></script>
    <style>
        .error-message {
            color: #b91c1c;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        /* Estilos para los botones de selección */
        .option-button {
            transition: all 0.3s ease;
        }
        .option-button.selected {
            background-color: #3b82f6;
            color: white;
            transform: scale(1.05);
        }
        /* Estilos para el calendario */
        .vanilla-calendar {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        .vanilla-calendar-day__btn {
            border-radius: 50%;
        }
        .vanilla-calendar-day__btn_selected {
            background-color: #3b82f6 !important;
            color: white !important;
        }
        .vanilla-calendar-day__btn_disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        .vanilla-calendar-day__btn_today {
            border: 2px solid #3b82f6;
        }
        .vanilla-calendar-day__btn_available {
            background-color: #10b981;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="bg-blue-600 text-white rounded-lg shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold">Nuevo Sistema de Calendario</h1>
            <p class="mt-2">Selecciona la fecha, turno y zona para tu reserva</p>
        </header>

        <main class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Detalles de la Reserva</h2>
                
                <?php if (isset($errores) && !empty($errores)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                    <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                    <ul class="list-disc pl-5">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (isset($mensaje_exito)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                    <p><?php echo $mensaje_exito; ?></p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                    <!-- Selección de Turno -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selecciona el turno *</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button type="button" 
                                    data-turno="mediodia" 
                                    class="option-button turno-option bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-center transition-all">
                                <span class="block text-lg font-medium">Mediodía</span>
                                <span class="block text-sm text-gray-500">13:00 - 16:00</span>
                            </button>
                            <button type="button" 
                                    data-turno="noche" 
                                    class="option-button turno-option bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-center transition-all">
                                <span class="block text-lg font-medium">Noche</span>
                                <span class="block text-sm text-gray-500">20:00 - 23:00</span>
                            </button>
                        </div>
                        <input type="hidden" id="turno" name="turno" value="">
                    </div>
                    
                    <!-- Selección de Zona -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selecciona la zona *</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button type="button" 
                                    data-zona="dentro" 
                                    class="option-button zona-option bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-center transition-all">
                                <span class="block text-lg font-medium">Interior</span>
                                <span class="block text-sm text-gray-500">Ambiente climatizado</span>
                            </button>
                            <button type="button" 
                                    data-zona="fuera" 
                                    class="option-button zona-option bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-center transition-all">
                                <span class="block text-lg font-medium">Terraza</span>
                                <span class="block text-sm text-gray-500">Al aire libre</span>
                            </button>
                        </div>
                        <input type="hidden" id="zona" name="zona" value="">
                    </div>
                    
                    <!-- Calendario Vanilla Calendar -->
                    <div class="form-group">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selecciona la fecha *</label>
                        <div id="vanilla-calendar" class="mb-2"></div>
                        <input type="hidden" id="fecha" name="fecha" value="">
                    </div>
                    
                    <!-- Selección de Hora -->
                    <div class="form-group" id="horaContainer" style="display: none;">
                        <label for="hora" class="block text-sm font-medium text-gray-700 mb-2">Selecciona la hora *</label>
                        <select id="hora" name="hora" 
                                class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecciona primero un turno</option>
                        </select>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" id="confirmarBtn" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" 
                                disabled>
                            Continuar con la reserva
                        </button>
                    </div>
                </form>
            </div>
        </main>
        
        <footer class="text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
        </footer>
    </div>
    
    <!-- Script para el calendario y la selección de opciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos del DOM
        const turnoButtons = document.querySelectorAll('.turno-option');
        const zonaButtons = document.querySelectorAll('.zona-option');
        const turnoInput = document.getElementById('turno');
        const zonaInput = document.getElementById('zona');
        const fechaInput = document.getElementById('fecha');
        const horaSelect = document.getElementById('hora');
        const horaContainer = document.getElementById('horaContainer');
        const confirmarBtn = document.getElementById('confirmarBtn');
        
        // Variables para almacenar selecciones
        let turnoSeleccionado = '';
        let zonaSeleccionada = '';
        let fechaSeleccionada = '';
        
        // Días disponibles desde PHP
        const diasDisponibles = <?php echo json_encode($dias_disponibles); ?>;
        
        // Inicializar Vanilla Calendar
        const calendar = new VanillaCalendar('#vanilla-calendar', {
            settings: {
                lang: 'es',
                iso8601: true,
                range: {
                    min: '2025-05-01',
                    max: '2025-05-31'
                },
                visibility: {
                    weekend: true,
                    today: true
                },
                selected: {
                    dates: [],
                    month: 4, // Mayo (0-indexado)
                    year: 2025
                }
            },
            actions: {
                clickDay(e, dates) {
                    // Solo permitir seleccionar una fecha
                    if (dates.length > 0) {
                        fechaSeleccionada = dates[0];
                        fechaInput.value = fechaSeleccionada;
                        console.log('Fecha seleccionada:', fechaSeleccionada);
                        
                        // Actualizar horas disponibles si ya se seleccionó un turno
                        if (turnoSeleccionado) {
                            actualizarHorasDisponibles();
                        }
                        
                        validarFormulario();
                    }
                }
            },
            DOMTemplates: {
                day: `
                    <div class="vanilla-calendar-day">
                        <button class="vanilla-calendar-day__btn" data-calendar-day></button>
                    </div>
                `
            }
        });
        
        // Inicializar calendario
        calendar.init();
        
        // Marcar días disponibles
        setTimeout(() => {
            const dayElements = document.querySelectorAll('.vanilla-calendar-day__btn');
            
            dayElements.forEach(day => {
                const dateAttr = day.getAttribute('data-calendar-day');
                if (dateAttr && diasDisponibles.includes(dateAttr)) {
                    day.classList.add('vanilla-calendar-day__btn_available');
                }
            });
        }, 100);
        
        // Eventos para botones de turno
        turnoButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Quitar selección de todos los botones
                turnoButtons.forEach(btn => btn.classList.remove('selected'));
                
                // Marcar este botón como seleccionado
                this.classList.add('selected');
                
                // Guardar el valor seleccionado
                turnoSeleccionado = this.getAttribute('data-turno');
                turnoInput.value = turnoSeleccionado;
                
                // Actualizar horas disponibles
                actualizarHorasDisponibles();
                
                // Validar formulario
                validarFormulario();
            });
        });
        
        // Eventos para botones de zona
        zonaButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Quitar selección de todos los botones
                zonaButtons.forEach(btn => btn.classList.remove('selected'));
                
                // Marcar este botón como seleccionado
                this.classList.add('selected');
                
                // Guardar el valor seleccionado
                zonaSeleccionada = this.getAttribute('data-zona');
                zonaInput.value = zonaSeleccionada;
                
                // Validar formulario
                validarFormulario();
            });
        });
        
        // Función para actualizar horas disponibles según el turno
        function actualizarHorasDisponibles() {
            let horas = [];
            
            if (turnoSeleccionado === 'mediodia') {
                horas = ["13:00", "13:30", "14:00", "14:30", "15:00", "15:30"];
            } else if (turnoSeleccionado === 'noche') {
                horas = ["20:00", "20:30", "21:00", "21:30", "22:00", "22:30"];
            }
            
            // Limpiar opciones actuales
            horaSelect.innerHTML = '';
            
            // Añadir opción por defecto
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Selecciona una hora';
            horaSelect.appendChild(defaultOption);
            
            // Añadir nuevas opciones
            horas.forEach(hora => {
                const option = document.createElement('option');
                option.value = hora;
                option.textContent = hora;
                horaSelect.appendChild(option);
            });
            
            // Mostrar el selector de hora
            horaContainer.style.display = 'block';
        }
        
        // Evento para cambio en selector de hora
        horaSelect.addEventListener('change', function() {
            validarFormulario();
        });
        
        // Función para validar el formulario
        function validarFormulario() {
            const formularioValido = 
                turnoSeleccionado !== '' && 
                zonaSeleccionada !== '' && 
                fechaSeleccionada !== '' && 
                horaSelect.value !== '';
            
            confirmarBtn.disabled = !formularioValido;
        }
    });
    </script>
</body>
</html>
