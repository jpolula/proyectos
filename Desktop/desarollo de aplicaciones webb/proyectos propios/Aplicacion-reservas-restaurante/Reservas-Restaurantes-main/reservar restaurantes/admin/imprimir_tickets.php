<?php
// Incluir archivos necesarios
require_once 'auth.php';
require_once '../vendor/autoload.php';
require_once '../src/Utils/Personalizacion.php';

// Crear instancia de Personalizacion
$personalizacion = new Personalizacion();

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

// Obtener y limpiar los IDs
$ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));

if (empty($ids)) {
    echo "Error: No se han proporcionado IDs válidos.";
    exit;
}

// Eliminar duplicados
$ids = array_unique($ids);

// Obtener datos de las reservas
$placeholders = str_repeat('?,', count($ids) - 1) . '?';
$sql = "SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, 
        CASE WHEN r.zona = 'dentro' THEN 'Interior' ELSE 'Terraza' END AS zona_nombre
        FROM reservas r 
        LEFT JOIN clientes c ON r.cliente_id = c.id 
        LEFT JOIN turnos t ON r.turno_id = t.id 
        WHERE r.id IN ($placeholders)";

$stmt = $pdo->prepare($sql);
$stmt->execute($ids);

if ($stmt->rowCount() === 0) {
    echo "Error: No se encontraron reservas con los IDs proporcionados.";
    exit;
}

$reservas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets de Reserva</title>
    <style>
        /* Asegurar que todo el contenido sea visible e imprimible */
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: auto;
            overflow: visible !important;
        }
        
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        
        .tickets-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .ticket-container {
            width: 7cm;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 0;
            page-break-inside: avoid;
        }
        
        /* Cabecera del ticket */
        .ticket-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .logo-container {
            margin-bottom: 10px;
            text-align: center;
        }
        
        .ticket-logo {
            max-height: 120px;
            max-width: 100%;
            height: auto;
            width: auto;
            margin-bottom: 5px;
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
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 15px;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
            z-index: 1000;
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
        
        .button.secondary {
            background-color: #f44336;
        }
        
        /* Estilos específicos para impresión */
        @media print {
            body {
                background-color: white;
                margin: 0;
                padding: 0;
                height: auto;
            }
            
            .tickets-container {
                display: block;
            }
            
            .ticket-container {
                width: 7cm;
                margin: 0 auto 20px auto;
                padding: 15px;
                border: none;
                box-shadow: none;
                page-break-after: always;
            }
            
            .ticket-container:last-child {
                page-break-after: auto;
            }
            
            .button-container {
                display: none;
            }
            
            /* Ocultar URL y número de página */
            @page {
                margin: 0.5cm;
                size: auto;
                orphans: 0;
                widows: 0;
            }
        }
    </style>
</head>
<body>
    <div class="tickets-container">
        <?php foreach ($reservas as $reserva): 
            $fecha = date('d/m/Y', strtotime($reserva['fecha']));
            $hora = date('H:i', strtotime($reserva['hora']));
        ?>
        <div class="ticket-container">
            <div class="ticket-header">
                <?php 
                // Mostrar el logo si existe
                $logoHTML = $personalizacion->getLogoHTML('ticket-logo', 'Logo del Restaurante');
                if (strpos($logoHTML, '<img') !== false) {
                    echo '<div class="logo-container">' . $logoHTML . '</div>';
                }
                ?>
                <h1 class="reservado">RESERVADO</h1>
                <div class="big-cliente"><?php echo htmlspecialchars($reserva['nombre']); ?></div>
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
                    
                    <?php if (!empty($reserva['observaciones'])): ?>
                    <div class="detail-row">
                        <div class="detail-label">Observaciones:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($reserva['observaciones']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($reserva['necesidades_especiales'])): ?>
                    <div class="detail-row">
                        <div class="detail-label">Necesidades especiales:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($reserva['necesidades_especiales']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="ticket-footer">
                <p>Gracias por su reserva</p>
                <p>#<?php echo $reserva['id']; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="button-container">
        <button class="button" onclick="window.print();">Imprimir Todos los Tickets</button>
        <a class="button secondary" href="reservas.php">Volver</a>
    </div>
    
    <script>
        // Auto-imprimir si se pasa el parámetro autoprint=1
        window.onload = function() {
            if (window.location.search.indexOf('autoprint=1') > -1) {
                window.print();
                // Redirigir después de imprimir (con un pequeño retraso para dar tiempo a la impresión)
                setTimeout(function() {
                    window.location.href = 'reservas.php';
                }, 1000);
            }
        };
    </script>
</body>
</html>
