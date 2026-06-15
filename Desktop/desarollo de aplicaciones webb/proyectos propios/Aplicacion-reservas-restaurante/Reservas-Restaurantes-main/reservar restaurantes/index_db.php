<?php
require_once 'enviar_correo_directo.php';
// Archivo index.php con formulario de reserva usando Tailwind CSS y conexión a base de datos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas de Restaurantes</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flatpickr para el calendario -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <style>
        /* Estilos personalizados para el calendario */
        .flatpickr-day.available {
            background-color: #d1fae5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .flatpickr-day.unavailable {
            background-color: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
            text-decoration: line-through;
            pointer-events: none; /* Deshabilitar interacción */
            opacity: 0.7;
        }
        /* Asegurarse de que el calendario siempre esté visible */
        .flatpickr-calendar.open {
            display: block !important;
        }
        /* Ajustar altura del contenedor del calendario */
        .calendar-container {
            min-height: 350px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="bg-blue-600 text-white rounded-lg shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold">Sistema de Reservas de Restaurantes</h1>
            <p class="mt-2">Reserva tu mesa de manera rápida y sencilla</p>
        </header>

        <main class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Realiza tu reserva</h2>
                
                <form id="reservaForm" class="space-y-6">
                    <!-- Paso 1: Selección de turno -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <h3 class="text-xl font-medium text-blue-800 mb-4">1. Selecciona el turno</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border border-gray-300 rounded-md bg-white cursor-pointer hover:bg-blue-50 transition">
                                <input type="radio" name="turno" value="mediodia" class="mr-3 h-5 w-5 text-blue-600" required>
                                <div>
                                    <span class="font-medium block">Mediodía</span>
                                    <span class="text-sm text-gray-500">13:00 - 16:00</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border border-gray-300 rounded-md bg-white cursor-pointer hover:bg-blue-50 transition">
                                <input type="radio" name="turno" value="noche" class="mr-3 h-5 w-5 text-blue-600" required>
                                <div>
                                    <span class="font-medium block">Noche</span>
                                    <span class="text-sm text-gray-500">20:00 - 23:00</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Paso 2: Selección de zona -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <h3 class="text-xl font-medium text-blue-800 mb-4">2. Selecciona la zona</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border border-gray-300 rounded-md bg-white cursor-pointer hover:bg-blue-50 transition">
                                <input type="radio" name="zona" value="dentro" class="mr-3 h-5 w-5 text-blue-600" required>
                                <div>
                                    <span class="font-medium block">Interior</span>
                                    <span class="text-sm text-gray-500">Ambiente climatizado</span>
                                </div>
                            </label>
                            <label class="flex items-center p-4 border border-gray-300 rounded-md bg-white cursor-pointer hover:bg-blue-50 transition">
                                <input type="radio" name="zona" value="fuera" class="mr-3 h-5 w-5 text-blue-600" required>
                                <div>
                                    <span class="font-medium block">Terraza</span>
                                    <span class="text-sm text-gray-500">Al aire libre</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Paso 3: Selección de fecha -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <h3 class="text-xl font-medium text-blue-800 mb-4">3. Selecciona la fecha</h3>
                        <div id="calendarContainer" class="calendar-container">
                            <div id="fechaWrapper">
                                <input type="text" id="fecha" name="fecha" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Selecciona una fecha" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paso 4: Selección de hora -->
                    <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                        <h3 class="text-xl font-medium text-blue-800 mb-4">4. Selecciona la hora</h3>
                        <div id="horasContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                            <p class="col-span-full text-gray-500 italic">Selecciona primero una fecha para ver las horas disponibles</p>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="button" id="continuarBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            Continuar
                        </button>
                    </div>
                </form>
            </div>
        </main>
        
        <footer class="text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del DOM
            const turnoRadios = document.querySelectorAll('input[name="turno"]');
            const zonaRadios = document.querySelectorAll('input[name="zona"]');
            const horasContainer = document.getElementById('horasContainer');
            const continuarBtn = document.getElementById('continuarBtn');
            const fechaInput = document.getElementById('fecha');
            
            // Variables para almacenar selecciones
            let turnoSeleccionado = null;
            let zonaSeleccionada = null;
            let calendarioInicializado = false;
            
            // Horas disponibles por turno
            const horasPorTurno = {
                mediodia: ["13:00", "13:30", "14:00", "14:30", "15:00", "15:30"],
                noche: ["20:00", "20:30", "21:00", "21:30", "22:00", "22:30"]
            };
            
            // Inicializar calendario con días en rojo por defecto
            const flatpickrInstance = flatpickr("#fecha", {
                locale: "es",
                dateFormat: "d/m/Y",
                minDate: "today",
                maxDate: new Date().fp_incr(30),
                disableMobile: true,
                inline: true,
                onChange: function(selectedDates, dateStr) {
                    if (turnoSeleccionado) {
                        updateHoras(turnoSeleccionado);
                    }
                    validateForm();
                },
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    // Por defecto, todos los días están en rojo (no disponibles)
                    dayElem.classList.add('unavailable');
                }
            });
            
            // Función para obtener días disponibles desde la base de datos
            async function obtenerDiasDisponibles(turno, zona) {
                try {
                    // Crear una consulta a la base de datos
                    const formData = new FormData();
                    formData.append('turno', turno);
                    formData.append('zona', zona);
                    
                    const response = await fetch('consultar_dias_disponibles.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('Error en la consulta');
                    }
                    
                    const data = await response.json();
                    return data.dias || [];
                } catch (error) {
                    console.error('Error al obtener días disponibles:', error);
                    return [];
                }
            }
            
            // Actualizar el calendario cuando cambia el turno o la zona
            async function actualizarCalendario() {
                if (turnoSeleccionado && zonaSeleccionada) {
                    // Marcar todos los días como no disponibles primero
                    const allDays = document.querySelectorAll('.flatpickr-day');
                    allDays.forEach(day => {
                        day.classList.add('unavailable');
                        day.classList.remove('available');
                    });
                    
                    // Obtener días disponibles desde PHP
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', `consultar_dias_disponibles.php?turno=${turnoSeleccionado}&zona=${zonaSeleccionada}`, true);
                    
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                
                                if (response.success && response.dias) {
                                    // Procesar los días disponibles
                                    response.dias.forEach(dia => {
                                        const fecha = new Date(dia.fecha);
                                        const dateStr = fecha.toISOString().split('T')[0];
                                        const dayElement = document.querySelector(`.flatpickr-day[aria-label*="${dateStr}"]`);
                                        
                                        if (dayElement) {
                                            if (dia.disponible) {
                                                dayElement.classList.add('available');
                                                dayElement.classList.remove('unavailable');
                                            } else {
                                                dayElement.classList.add('unavailable');
                                                dayElement.classList.remove('available');
                                            }
                                        }
                                    });
                                } else {
                                    console.error('Error en la respuesta:', response.error || 'Formato incorrecto');
                                }
                            } catch (e) {
                                console.error('Error al procesar la respuesta:', e);
                            }
                        } else {
                            console.error('Error en la solicitud:', xhr.status);
                        }
                    };
                    
                    xhr.onerror = function() {
                        console.error('Error de red al obtener días disponibles');
                    };
                    
                    xhr.send();
                }
            }
            
            // Manejar cambio de turno
            turnoRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    turnoSeleccionado = this.value;
                    actualizarCalendario();
                    validateForm();
                });
            });
            
            // Manejar cambio de zona
            zonaRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    zonaSeleccionada = this.value;
                    actualizarCalendario();
                    validateForm();
                });
            });
            
            // Actualizar horas disponibles según el turno seleccionado
            function updateHoras(turno) {
                horasContainer.innerHTML = '';
                
                if (turno && horasPorTurno[turno]) {
                    horasPorTurno[turno].forEach(hora => {
                        const label = document.createElement('label');
                        label.className = 'flex items-center justify-center p-2 border border-gray-300 rounded-md bg-white cursor-pointer hover:bg-blue-50 transition';
                        label.innerHTML = `
                            <input type="radio" name="hora" value="${hora}" class="mr-2 h-4 w-4 text-blue-600" required>
                            <span>${hora}</span>
                        `;
                        horasContainer.appendChild(label);
                    });
                    
                    // Añadir event listeners a los nuevos radios de hora
                    document.querySelectorAll('input[name="hora"]').forEach(radio => {
                        radio.addEventListener('change', validateForm);
                    });
                } else {
                    horasContainer.innerHTML = '<p class="col-span-full text-gray-500 italic">Selecciona primero una fecha para ver las horas disponibles</p>';
                }
            }
            
            // Validar formulario para habilitar/deshabilitar botón de continuar
            function validateForm() {
                const fecha = document.getElementById('fecha').value;
                const turnoSelected = document.querySelector('input[name="turno"]:checked');
                const zonaSelected = document.querySelector('input[name="zona"]:checked');
                const horaSelected = document.querySelector('input[name="hora"]:checked');
                
                continuarBtn.disabled = !(fecha && turnoSelected && zonaSelected && horaSelected);
            }
            
            // Manejar clic en botón de continuar
            continuarBtn.addEventListener('click', function() {
                // Aquí se podría redirigir a la siguiente página o mostrar el siguiente paso del formulario
                alert('Formulario validado. En una aplicación real, aquí continuaría con los datos del cliente.');
                
                // Ejemplo de cómo obtener los valores seleccionados
                const formData = {
                    turno: document.querySelector('input[name="turno"]:checked').value,
                    zona: document.querySelector('input[name="zona"]:checked').value,
                    fecha: document.getElementById('fecha').value,
                    hora: document.querySelector('input[name="hora"]:checked').value
                };
                
                console.log('Datos del formulario:', formData);
            });
        });
    </script>
</body>
</html>
