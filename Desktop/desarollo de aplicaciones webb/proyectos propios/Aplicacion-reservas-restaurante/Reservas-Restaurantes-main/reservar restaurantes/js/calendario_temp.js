
    // Script temporal para forzar la actualización del calendario
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            // Seleccionar todos los días del calendario
            const allDays = document.querySelectorAll('.flatpickr-day');
            
            // Aplicar estilos a todos los días
            allDays.forEach(day => {
                // Hacer que todos los días sean seleccionables
                day.classList.add('available');
                day.style.backgroundColor = '#4CAF50';
                day.style.borderColor = '#4CAF50';
                day.style.color = 'white';
                day.style.pointerEvents = 'auto';
                day.style.cursor = 'pointer';
                
                // Quitar cualquier atributo disabled
                day.disabled = false;
            });
            
            console.log('Calendario actualizado por script temporal');
        }, 1000);
    });
    