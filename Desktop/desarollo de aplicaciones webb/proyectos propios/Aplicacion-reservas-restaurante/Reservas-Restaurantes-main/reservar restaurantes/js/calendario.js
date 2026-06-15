/**
 * Script para corregir el problema del calendario
 * Este script se encarga de hacer que todos los días del calendario sean seleccionables
 */

// Función para hacer todos los días seleccionables
function hacerTodosLosDiasSeleccionables() {
    console.log("Aplicando estilos a todos los días del calendario...");
    
    // Esperar a que el calendario esté completamente cargado
    setTimeout(function() {
        // Seleccionar todos los días del calendario
        const allDays = document.querySelectorAll('.flatpickr-day');
        console.log(`Encontrados ${allDays.length} días en el calendario`);
        
        // Aplicar estilos a todos los días
        allDays.forEach(day => {
            // Hacer que todos los días sean seleccionables
            day.classList.add('available');
            day.style.backgroundColor = '#4CAF50';
            day.style.borderColor = '#4CAF50';
            day.style.color = 'white';
            day.style.pointerEvents = 'auto';
            day.style.cursor = 'pointer';
            day.style.fontWeight = 'bold';
            
            // Quitar cualquier atributo disabled
            day.disabled = false;
            day.removeAttribute('disabled');
            day.removeAttribute('aria-disabled');
            
            // Asegurar que el día sea clickeable
            if (!day.dataset.hasClickListener) {
                day.addEventListener('click', function(e) {
                    console.log('Día clickeado:', day.innerHTML);
                });
                day.dataset.hasClickListener = 'true';
            }
        });
        
        console.log("Estilos aplicados correctamente");
    }, 500);
}

// Ejecutar la función cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM cargado, inicializando script de calendario...");
    
    // Primera ejecución
    hacerTodosLosDiasSeleccionables();
    
    // Ejecutar cada 2 segundos para asegurar que los estilos se mantengan
    setInterval(hacerTodosLosDiasSeleccionables, 2000);
    
    // Agregar listener al botón de actualizar calendario si existe
    const btnActualizar = document.getElementById('actualizarCalendarioBtn');
    if (btnActualizar) {
        btnActualizar.addEventListener('click', function() {
            console.log('Botón de actualizar calendario clickeado');
            hacerTodosLosDiasSeleccionables();
        });
    }
});
