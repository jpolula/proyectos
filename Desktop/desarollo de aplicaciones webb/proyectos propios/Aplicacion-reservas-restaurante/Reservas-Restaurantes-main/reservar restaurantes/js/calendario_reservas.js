/**
 * calendario_reservas.js
 * Script para gestionar el calendario y las reservas del restaurante
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const fechaInput = document.getElementById('fecha');
    const zonaContainer = document.getElementById('zonaContainer');
    const turnoContainer = document.getElementById('turnoContainer');
    const confirmarBtn = document.getElementById('confirmarBtn');
    const mensajeError = document.getElementById('mensajeError');
    const textoError = document.getElementById('textoError');
    
    // Variables para almacenar selecciones
    let fechaSeleccionada = '';
    let zonaSeleccionada = '';
    let turnoSeleccionado = '';
    
    // Obtener el número de personas de la sesión
    let numPersonas = 0;
    
    // Intentar obtener el número de personas de la URL (si se pasa como parámetro)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('num_personas')) {
        numPersonas = parseInt(urlParams.get('num_personas')) || 0;
    }
    
    // Si no está en la URL, intentar obtenerlo del sessionStorage
    if (numPersonas <= 0) {
        numPersonas = parseInt(sessionStorage.getItem('num_personas')) || 0;
    }
    
    console.log(`👥 Número de personas para la reserva: ${numPersonas}`);
    
    // Instancia de Flatpickr
    let flatpickrInstance = null;
    
    // Horas disponibles por turno (predefinidas)
    const horasPorTurno = {
        mediodia: ["13:00", "13:30", "14:00", "14:30", "15:00", "15:30"],
        noche: ["20:00", "20:30", "21:00", "21:30", "22:00", "22:30"]
    };
    
    /**
     * Función para cargar los días disponibles desde el servidor
     */
    function cargarDiasDisponibles() {
        console.log('📅 Iniciando carga de días disponibles...');
        
        // Mostrar mensaje de carga
        if (fechaInput) {
            fechaInput.placeholder = "Cargando disponibilidad...";
        }
        
        // Parámetro para evitar caché
        const timestamp = Date.now();
        
        // Realizar la petición AJAX
        fetch(`consultar_dias_disponibles.php?modo=dias&nocache=${timestamp}`)
            .then(response => {
                console.log('📡 Respuesta recibida del servidor');
                return response.json();
            })
            .then(data => {
                console.log('Respuesta completa del servidor:', data);
                
                if (data.success && data.dias && data.dias.length > 0) {
                    console.log(`✅ Se encontraron ${data.dias.length} días disponibles`);
                    
                    // Extraer las fechas en formato YYYY-MM-DD
                    const fechasDisponibles = data.dias.map(dia => {
                        if (typeof dia === 'object' && dia.fecha) {
                            return dia.fecha;
                        } else if (typeof dia === 'string') {
                            return dia;
                        }
                        return null;
                    }).filter(Boolean);
                    
                    console.log('📅 Fechas disponibles:', fechasDisponibles);
                    
                    if (fechasDisponibles.length > 0) {
                        inicializarCalendario(fechasDisponibles);
                    } else {
                        // Si no hay fechas disponibles, usar todas las de mayo 2025
                        const fechasRespaldo = generarFechasMayo2025();
                        inicializarCalendario(fechasRespaldo);
                        mostrarError('No se encontraron días disponibles. Se muestran todos los días de mayo 2025.');
                    }
                } else {
                    console.error('❌ No se encontraron días disponibles');
                    mostrarError('Error al cargar los días disponibles. Se muestran todos los días de mayo 2025.');
                    
                    // Usar fechas de respaldo para mayo 2025
                    const fechasRespaldo = generarFechasMayo2025();
                    inicializarCalendario(fechasRespaldo);
                }
            })
            .catch(error => {
                console.error('❌ Error al cargar días disponibles:', error);
                mostrarError('Error al cargar los días disponibles. Se muestran todos los días de mayo 2025.');
                
                // Usar fechas de respaldo para mayo 2025
                const fechasRespaldo = generarFechasMayo2025();
                inicializarCalendario(fechasRespaldo);
            });
    }
    
    /**
     * Función para generar todas las fechas de mayo 2025
     */
    function generarFechasMayo2025() {
        const fechas = [];
        const añoMes = '2025-05-';
        
        for (let dia = 1; dia <= 31; dia++) {
            const fechaFormateada = añoMes + String(dia).padStart(2, '0');
            fechas.push(fechaFormateada);
        }
        
        return fechas;
    }
    
    /**
     * Función para inicializar/reinicializar el calendario
     */
    function inicializarCalendario(fechasHabilitadas) {
        console.log('🔄 Inicializando calendario con', fechasHabilitadas.length, 'fechas disponibles');
        
        // Destruir instancia previa si existe
        if (flatpickrInstance) {
            flatpickrInstance.destroy();
        }
        
        if (!fechaInput) {
            console.error('❌ No se encontró el elemento de entrada de fecha');
            return;
        }
        
        // Opciones de configuración para Flatpickr
        const flatpickrOptions = {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: "2025-05-01",
            maxDate: "2025-05-31",
            inline: true,
            enable: fechasHabilitadas,
            monthSelectorType: 'dropdown',
            yearSelectorType: 'dropdown',
            onChange: function(selectedDates, dateStr) {
                if (selectedDates.length > 0) {
                    fechaSeleccionada = dateStr;
                    console.log('🗓️ Fecha seleccionada:', fechaSeleccionada);
                    mostrarTurnos();
                    validarFormulario();
                }
            },
            onReady: function() {
                aplicarEstilosCalendario();
                setTimeout(aplicarEstilosCalendario, 100);
            },
            onMonthChange: function() {
                setTimeout(aplicarEstilosCalendario, 100);
            },
            onYearChange: function() {
                setTimeout(aplicarEstilosCalendario, 100);
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                // Marcar días disponibles con color verde
                if (fechasHabilitadas.includes(dayElem.dateObj.toISOString().split('T')[0])) {
                    dayElem.style.backgroundColor = '#38b2ac'; // teal-500
                    dayElem.style.color = 'white';
                    dayElem.style.borderRadius = '0.5rem';
                    dayElem.style.fontWeight = 'bold';
                    dayElem.style.border = '2px solid #2c9a94';
                }
            }
        };
        
        // Crear instancia de Flatpickr
        flatpickrInstance = flatpickr(fechaInput, flatpickrOptions);
    }
    
    /**
     * Función para aplicar estilos al calendario después de inicializarlo
     */
    function aplicarEstilosCalendario() {
        console.log('🎨 Aplicando estilos al calendario');
        
        // Seleccionar todos los días habilitados
        const diasDisponibles = document.querySelectorAll('.flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay)');
        
        // Aplicar estilos a los días disponibles
        diasDisponibles.forEach(dia => {
            const fechaStr = dia.dateObj ? dia.dateObj.toISOString().split('T')[0] : null;
            
            if (fechaStr && flatpickrInstance.config.enable.includes(fechaStr)) {
                dia.style.backgroundColor = '#38b2ac'; // teal-500
                dia.style.color = 'white';
                dia.style.borderRadius = '0.5rem';
                dia.style.fontWeight = 'bold';
                dia.style.border = '2px solid #2c9a94';
            }
        });
    }
    
    /**
     * Función para mostrar un mensaje de error
     */
    function mostrarError(mensaje) {
        if (textoError && mensajeError) {
            textoError.textContent = mensaje;
            mensajeError.classList.remove('hidden');
        } else {
            console.error(mensaje);
        }
    }
    
    /**
     * Función para mostrar los turnos disponibles
     */
    function mostrarTurnos() {
        if (!turnoContainer) return;
        
        console.log('👁️ Mostrando sección de turnos');
        turnoContainer.classList.remove('hidden');
        
        // Hacer scroll al contenedor de turnos
        turnoContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    /**
     * Función para validar el formulario
     */
    function validarFormulario() {
        const formularioValido = fechaSeleccionada && turnoSeleccionado && zonaSeleccionada;
        
        if (confirmarBtn) {
            confirmarBtn.disabled = !formularioValido;
            
            if (formularioValido) {
                confirmarBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                confirmarBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
        
        return formularioValido;
    }
    
    // Inicializar calendario al cargar la página
    cargarDiasDisponibles();
    
    // Configurar eventos para los botones de turno
    document.querySelectorAll('#turnoOptions button').forEach(btn => {
        btn.addEventListener('click', function() {
            // Quitar selección de todos los botones
            document.querySelectorAll('#turnoOptions button').forEach(b => {
                b.classList.remove('selected-option');
                b.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            // Aplicar selección al botón actual
            this.classList.remove('bg-gray-200', 'text-gray-700');
            this.classList.add('selected-option');
            
            // Guardar turno seleccionado
            turnoSeleccionado = this.dataset.turno;
            console.log('🕒 Turno seleccionado:', turnoSeleccionado);
            
            // Mostrar siguiente paso (zonas)
            mostrarZonas();
        });
    });
    
    // Función para mostrar las zonas
    function mostrarZonas() {
        if (!zonaContainer) return;
        
        console.log('👁️ Mostrando sección de zonas');
        zonaContainer.classList.remove('hidden');
        
        // Hacer scroll al contenedor de zonas
        zonaContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    // Configurar eventos para los botones de zona
    document.querySelectorAll('#zonaOptions button').forEach(btn => {
        btn.addEventListener('click', function() {
            // Quitar selección de todos los botones
            document.querySelectorAll('#zonaOptions button').forEach(b => {
                b.classList.remove('selected-option');
                b.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            // Aplicar selección al botón actual
            this.classList.remove('bg-gray-200', 'text-gray-700');
            this.classList.add('selected-option');
            
            // Guardar zona seleccionada
            zonaSeleccionada = this.dataset.zona;
            console.log('🏠 Zona seleccionada:', zonaSeleccionada);
            
            // Validar formulario
            validarFormulario();
        });
    });
});
