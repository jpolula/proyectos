/**
 * reservas.js
 * 
 * Script para manejar las funcionalidades AJAX de la página de reservas
 */

// Función para confirmar una reserva mediante AJAX
function confirmarReserva(reservaId) {
    if (confirm('¿Está seguro de que desea confirmar esta reserva?')) {
        // Mostrar indicador de carga
        const btnConfirmar = document.querySelector(`button[data-reserva-id="${reservaId}"][data-accion="confirmar"]`);
        if (btnConfirmar) {
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...';
        }
        
        // Realizar la petición AJAX
        const formData = new FormData();
        formData.append('reserva_id', reservaId);
        
        fetch('confirmar_reserva_admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Mostrar mensaje de resultado
            const mensajeDiv = document.createElement('div');
            mensajeDiv.className = data.exito ? 'bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4' : 'bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4';
            mensajeDiv.innerHTML = `<p>${data.mensaje}</p>`;
            
            const contenedor = document.querySelector('.px-4.py-6.sm\\:px-0');
            if (contenedor) {
                contenedor.insertBefore(mensajeDiv, contenedor.firstChild);
            }
            
            // Si fue exitoso, actualizar la página después de 2 segundos
            if (data.exito && data.redireccionar) {
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                // Restaurar el botón
                if (btnConfirmar) {
                    btnConfirmar.disabled = false;
                    btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i> Aceptar';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ha ocurrido un error al procesar la solicitud.');
            
            // Restaurar el botón
            if (btnConfirmar) {
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i> Aceptar';
            }
        });
    }
}

// Inicializar los botones de confirmación cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Buscar todos los botones de confirmación y asignarles el evento click
    const botonesConfirmar = document.querySelectorAll('form[onsubmit*="¿Confirmar esta reserva?"] button[type="submit"]');
    
    botonesConfirmar.forEach(boton => {
        const form = boton.closest('form');
        const reservaId = form.querySelector('input[name="reserva_id"]').value;
        
        // Crear un nuevo botón que reemplazará al botón original
        const nuevoBoton = document.createElement('button');
        nuevoBoton.type = 'button';
        nuevoBoton.className = boton.className;
        nuevoBoton.innerHTML = boton.innerHTML;
        nuevoBoton.setAttribute('data-reserva-id', reservaId);
        nuevoBoton.setAttribute('data-accion', 'confirmar');
        nuevoBoton.onclick = function() {
            confirmarReserva(reservaId);
        };
        
        // Reemplazar el formulario completo con el nuevo botón
        form.parentNode.replaceChild(nuevoBoton, form);
    });
});
