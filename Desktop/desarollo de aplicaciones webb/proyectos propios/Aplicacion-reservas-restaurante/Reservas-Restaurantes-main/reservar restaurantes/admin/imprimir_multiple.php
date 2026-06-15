<?php
// Incluir archivos necesarios
require_once 'auth.php';
require_once '../vendor/autoload.php';

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

// Verificar si se han proporcionado IDs de reserva
if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    echo "Error: No se han especificado reservas.";
    exit;
}

// Obtener los IDs de reserva
$ids = explode(',', $_GET['ids']);

// Validar que todos los IDs sean numéricos
foreach ($ids as $id) {
    if (!is_numeric($id)) {
        echo "Error: ID de reserva no válido.";
        exit;
    }
}

// Obtener datos de las reservas
$reservas = [];
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, 
        CASE WHEN r.zona = 'dentro' THEN 'Interior' ELSE 'Terraza' END AS zona_nombre
        FROM reservas r 
        LEFT JOIN clientes c ON r.cliente_id = c.id 
        LEFT JOIN turnos t ON r.turno_id = t.id 
        WHERE r.id IN ($placeholders)
        ORDER BY r.fecha ASC, r.hora ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($ids);
$reservas = $stmt->fetchAll();

if (count($reservas) === 0) {
    echo "Error: No se encontraron las reservas especificadas.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Múltiples Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        
        /* Contenedor principal */
        .ticket-container {
            width: 7cm;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin: 0 auto;
        }
        
        /* Cabecera del ticket */
        .ticket-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .ticket-header h1.reservado {
            font-size: 38px;
            font-weight: bold;
            margin: 0;
            padding: 10px 0;
            letter-spacing: 2px;
            text-align: center;
        }
        
        .big-cliente {
            font-size: 26px;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
            padding: 5px;
            border-bottom: 1px solid #000;
        }
        
        /* Número de personas destacado */
        .big-personas {
            text-align: center;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px dashed #000;
        }
        
        .big-personas span {
            font-size: 36px;
            font-weight: bold;
            display: block;
        }
        
        .personas-label {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        /* Contenido del ticket */
        .ticket-content {
            font-size: 14px;
            margin-top: 15px;
        }
        
        .details-container {
            margin: 15px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px dotted #ccc;
        }
        
        .detail-label {
            font-weight: bold;
            font-size: 14px;
        }
        
        .detail-value {
            font-size: 14px;
            text-align: right;
        }
        
        /* Pie del ticket */
        .ticket-footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 10px;
        }
        
        /* Botones */
        .button-container {
            text-align: center;
            margin: 20px 0;
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
        }
        
        .button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        /* Estilos específicos para impresión */
        @media print {
            body {
                background-color: white;
            }
            
            .ticket-container {
                width: 7cm;
                margin: 0 auto;
                border: none;
                box-shadow: none;
                padding: 0;
            }
            
            .button-container {
                display: none;
            }
            
            /* Ocultar URL y número de página */
            @page {
                margin: 0;
                size: 7cm auto;
            }
            
            /* Ocultar la URL que aparece en el pie de página */
            html {
                height: 100%;
                overflow: hidden;
            }
        }
        
        /* Contador de tickets */
        .ticket-counter {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 4px;
            z-index: 1000;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: auto !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: white;
            }
            
            .ticket-container {
                width: 7cm;
                margin: 0 auto;
                padding: 0;
                border: none;
                box-shadow: none;
                page-break-after: always; /* Forzar salto de página después de cada ticket */
                page-break-inside: avoid;
                break-inside: avoid;
                height: auto !important;
                overflow: visible !important;
                display: block !important; /* Mostrar todos los tickets */
            }
            
            /* El último ticket no necesita salto de página después */
            .ticket-container:last-child {
                page-break-after: auto;
            }
            
            .ticket-counter, .button-container {
                display: none;
            }
            
            /* Configurar página para impresión */
            @page {
                margin: 0;
                size: 7cm auto;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-counter">
        <span id="currentTicket">1</span> / <span id="totalTickets"><?php echo count($reservas); ?></span>
    </div>
    
    <?php foreach ($reservas as $index => $reserva): 
        // Obtener alérgenos y necesidades especiales
        $alergenos = !empty($reserva['observaciones']) ? $reserva['observaciones'] : 'Ninguno';
        $necesidades = !empty($reserva['necesidades_especiales']) ? $reserva['necesidades_especiales'] : 'Ninguna';
        
        // Formatear fecha y hora
        $fecha = date('d/m/Y', strtotime($reserva['fecha']));
        $hora = date('H:i', strtotime($reserva['hora']));
        
        // Nombre del cliente
        $nombre_cliente = $reserva['nombre'];
    ?>
    <div class="ticket-container" id="ticket-<?php echo $index + 1; ?>" <?php if ($index > 0) echo 'style="display: none;"'; ?>>
        <div class="ticket-header">
            <h1 class="reservado">RESERVADO</h1>
            <div class="big-cliente"><?php echo $nombre_cliente; ?></div>
        </div>
        
        <div class="big-personas">
            <span><?php echo $reserva['cantidad_personas']; ?></span>
            <div class="personas-label">PERSONAS</div>
        </div>
        
        <div class="ticket-content">
            <div class="details-container">
                <div class="detail-row">
                    <div class="detail-label">Fecha:</div>
                    <div class="detail-value"><?php echo $fecha; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Hora:</div>
                    <div class="detail-value"><?php echo $hora; ?> h</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Zona:</div>
                    <div class="detail-value"><?php echo $reserva['zona_nombre']; ?></div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Turno:</div>
                    <div class="detail-value"><?php echo $reserva['turno_nombre']; ?></div>
                </div>
            </div>
        </div>
        
        <div class="ticket-footer">
            <p>Gracias por su reserva</p>
        </div>
    </div>
    <?php endforeach; ?>
    
    <div class="button-container">
        <button class="button" id="prevTicket" disabled><i class="fas fa-arrow-left"></i> Anterior</button>
        <button class="button" id="printCurrent">Imprimir Actual</button>
        <button class="button" id="printAll">Imprimir Todos</button>
        <button class="button" id="nextTicket">Siguiente <i class="fas fa-arrow-right"></i></button>
        <a class="button" href="reservas.php" style="background-color: #f44336;">Volver</a>
    </div>
    
    <script>
        // Variables para controlar la navegación entre tickets
        const tickets = document.querySelectorAll('.ticket-container');
        const totalTickets = tickets.length;
        let currentTicket = 1;
        
        // Elementos de la interfaz
        const prevButton = document.getElementById('prevTicket');
        const nextButton = document.getElementById('nextTicket');
        const printCurrentButton = document.getElementById('printCurrent');
        const printAllButton = document.getElementById('printAll');
        const currentTicketDisplay = document.getElementById('currentTicket');
        
        // Función para mostrar un ticket específico
        function showTicket(ticketNumber) {
            // Ocultar todos los tickets
            tickets.forEach(ticket => ticket.style.display = 'none');
            
            // Mostrar el ticket actual
            document.getElementById(`ticket-${ticketNumber}`).style.display = 'block';
            
            // Actualizar el contador
            currentTicketDisplay.textContent = ticketNumber;
            
            // Actualizar estado de los botones
            prevButton.disabled = (ticketNumber === 1);
            nextButton.disabled = (ticketNumber === totalTickets);
            
            // Actualizar el ticket actual
            currentTicket = ticketNumber;
        }
        
        // Event listeners para los botones
        prevButton.addEventListener('click', () => {
            if (currentTicket > 1) {
                showTicket(currentTicket - 1);
            }
        });
        
        nextButton.addEventListener('click', () => {
            if (currentTicket < totalTickets) {
                showTicket(currentTicket + 1);
            }
        });
        
        // Imprimir ticket actual
        printCurrentButton.addEventListener('click', () => {
            window.print();
        });
        
        // Imprimir todos los tickets en un solo documento
        printAllButton.addEventListener('click', () => {
            const originalTicket = currentTicket;
            
            // Guardar el estado actual de visualización
            const originalDisplayStates = [];
            tickets.forEach(ticket => {
                originalDisplayStates.push(ticket.style.display);
            });
            
            // Mostrar todos los tickets para impresión
            tickets.forEach(ticket => {
                ticket.style.display = 'block';
            });
            
            // Imprimir todos los tickets
            setTimeout(() => {
                window.print();
                
                // Restaurar la visualización original después de imprimir
                setTimeout(() => {
                    // Ocultar todos los tickets excepto el actual
                    tickets.forEach((ticket, index) => {
                        ticket.style.display = 'none';
                    });
                    
                    // Mostrar el ticket que estaba activo
                    showTicket(originalTicket);
                }, 500);
            }, 300);
        });
        
        // Inicializar
        showTicket(1);
    </script>
</body>
</html>
