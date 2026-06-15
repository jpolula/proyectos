<?php
/**
 * Generador de PDF para reservas
 * Este archivo genera un PDF con los datos de las reservas filtradas
 */

// Evitar cualquier salida antes de generar el PDF
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Incluir archivos necesarios
require_once 'auth.php';
require_once '../vendor/autoload.php';

// No es necesario importar la clase FPDF con use ya que no usa namespace
// La clase FPDF estará disponible globalmente después de incluir autoload.php

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

// Solo se usa el formato A4
$formato = 'a4';

// Verificar si es historial o reservas actuales
$es_historial = isset($_GET['historial']) && $_GET['historial'] == 1;

// Procesar filtros según la página de origen
if ($es_historial) {
    // Filtros para historial_reservas.php
    $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
    $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
    $filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';
    $filtro_turno = isset($_GET['filtro_turno']) ? $_GET['filtro_turno'] : '';
    $filtro_zona = isset($_GET['filtro_zona']) ? $_GET['filtro_zona'] : '';
    $filtro_fecha = ''; // No se usa en historial
} else {
    // Filtros para reservas.php
    $filtro_fecha = isset($_GET['filtro_fecha']) ? $_GET['filtro_fecha'] : '';
    $filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';
    $filtro_turno = isset($_GET['filtro_turno']) ? $_GET['filtro_turno'] : '';
    $filtro_zona = isset($_GET['filtro_zona']) ? $_GET['filtro_zona'] : '';
    $fecha_inicio = '';
    $fecha_fin = '';
}

// Construir la consulta SQL base
$sql = "
    SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre,
           DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
           TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada,
           DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_creacion_formateada,
           (SELECT GROUP_CONCAT(cp.texto SEPARATOR ', ') 
            FROM reservas_checkboxes rc 
            JOIN checkboxes_personalizados cp ON rc.checkbox_id = cp.id 
            WHERE rc.reserva_id = r.id AND rc.valor = 1) AS checkboxes_seleccionados
    FROM reservas r
    JOIN clientes c ON r.cliente_id = c.id
    JOIN turnos t ON r.turno_id = t.id
    WHERE 1=1
";

$params = [];

// Agregar condiciones de filtro según el tipo de consulta
if ($es_historial) {
    // Obtener la fecha actual para comparar
    $fecha_actual = date('Y-m-d');
    $sql .= " AND r.fecha < ?";
    $params[] = $fecha_actual;
    
    // Filtros específicos para historial
    if (!empty($fecha_inicio)) {
        $sql .= " AND r.fecha >= ?";
        $params[] = $fecha_inicio;
    }
    
    if (!empty($fecha_fin)) {
        $sql .= " AND r.fecha <= ?";
        $params[] = $fecha_fin;
    }
} else {
    // Filtro de fecha para reservas actuales
    if (!empty($filtro_fecha)) {
        $sql .= " AND r.fecha = ?";
        $params[] = $filtro_fecha;
    }
}

// Filtros comunes para ambos tipos
if (!empty($filtro_estado)) {
    $sql .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_turno)) {
    $sql .= " AND r.turno_id = ?";
    $params[] = $filtro_turno;
}

if (!empty($filtro_zona)) {
    $sql .= " AND r.zona = ?";
    $params[] = $filtro_zona;
}

// Agregar orden
$sql .= " ORDER BY r.fecha DESC, r.hora ASC";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

// Crear una clase personalizada que extiende FPDF
class PDF extends \FPDF
{
    // Formato del PDF (a4 o ticket)
    private $formato;
    // Variable para saber si es historial o no
    private $es_historial;
    
    // Método para calcular el número de líneas que ocupará un texto
    function NbLines($w, $txt)
    {
        // Calcula el número de líneas que ocupará un texto en una celda de ancho w
        $cw = &$this->CurrentFont['cw'];
        if($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace('\r', '', $txt);
        $nb = strlen($s);
        if($nb > 0 && $s[$nb - 1] == '\n')
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i < $nb)
        {
            $c = $s[$i];
            if($c == '\n')
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if($l > $wmax)
            {
                if($sep == -1)
                {
                    if($i == $j)
                        $i++;
                }
                else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
    
    // Constructor
    function __construct($formato = 'a4', $es_historial = false)
    {
        $this->formato = $formato;
        $this->es_historial = $es_historial;
        
        // Solo se usa el formato A4
        parent::__construct('P', 'mm', 'A4');
        $this->SetAutoPageBreak(true, 15);
        
        $this->SetMargins(10, 10, 10);
    }
    
    // Cabecera de página - personalizada según formato
    function Header()
    {
        // Determinar el tamaño de letra según el formato
        $tamanoTitulo = ($this->formato === 'ticket') ? 12 : 16;
        $tamanoSubtitulo = ($this->formato === 'ticket') ? 8 : 10;
        
        if ($this->formato === 'ticket') {
            // Encabezado simple para ticket (optimizado para blanco y negro)
            $this->SetTextColor(0); // Negro
            $this->SetFont('Arial', 'B', $tamanoTitulo);
            $this->Cell(0, 10, 'RESERVAS', 0, 1, 'C');
            
            // Línea debajo del título
            $this->Line($this->GetX(), $this->GetY() - 2, $this->GetX() + $this->GetPageWidth() - 10, $this->GetY() - 2);
            
            // Fecha y hora de generación
            $this->SetFont('Arial', '', $tamanoSubtitulo);
            $fecha_actual = date('d/m/Y H:i:s');
            $this->Cell(0, 8, 'Generado el: ' . $fecha_actual, 0, 1, 'C');
            
            // Filtros aplicados
            $filtros = array();
            
            if ($this->es_historial) {
                // Mostrar que es un historial
                $filtros[] = 'Tipo: Historial de reservas pasadas';
                
                // Filtros de rango de fechas para historial
                if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
                    $filtros[] = 'Período: ' . date('d/m/Y', strtotime($_GET['fecha_inicio'])) . ' a ' . date('d/m/Y', strtotime($_GET['fecha_fin']));
                } elseif (!empty($_GET['fecha_inicio'])) {
                    $filtros[] = 'Desde: ' . date('d/m/Y', strtotime($_GET['fecha_inicio']));
                } elseif (!empty($_GET['fecha_fin'])) {
                    $filtros[] = 'Hasta: ' . date('d/m/Y', strtotime($_GET['fecha_fin']));
                }
            } else {
                // Filtro de fecha específica para reservas actuales
                if (!empty($_GET['filtro_fecha'])) {
                    $filtros[] = 'Fecha: ' . date('d/m/Y', strtotime($_GET['filtro_fecha']));
                }
            }
            
            // Filtros comunes
            if (!empty($_GET['filtro_estado'])) {
                $filtros[] = 'Estado: ' . ucfirst($_GET['filtro_estado']);
            }
            if (!empty($_GET['filtro_turno'])) {
                // Obtener el nombre del turno
                global $pdo;
                $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
                $stmt->execute([$_GET['filtro_turno']]);
                $turno = $stmt->fetch();
                if ($turno) {
                    $filtros[] = 'Turno: ' . ucfirst($turno['nombre']);
                }
            }
            if (!empty($_GET['filtro_zona'])) {
                $filtros[] = 'Zona: ' . ($_GET['filtro_zona'] === 'dentro' ? 'Interior' : 'Terraza');
            }
            
            if (!empty($filtros)) {
                $this->SetFont('Arial', 'I', $tamanoSubtitulo);
                $textoFiltros = 'Filtros: ' . implode(', ', $filtros);
                $this->MultiCell($this->GetPageWidth() - 10, 6, $textoFiltros, 0, 'C');
            }
            
            $this->Ln(5);
        } else {
            // Encabezado para formato A4 (con colores)
            // Colores para el encabezado A4
            $colorPrimario = array(41, 128, 185); // Azul
            $colorTextoClaro = array(255, 255, 255); // Blanco
            $colorTextoOscuro = array(44, 62, 80); // Azul oscuro
            
            // Barra superior de color
            $this->SetFillColor($colorPrimario[0], $colorPrimario[1], $colorPrimario[2]);
            $this->Rect(0, 0, $this->GetPageWidth(), 25, 'F');
            
            // Logo (si existe)
            $logoPath = '../assets/img/logo.png';
            if (file_exists($logoPath)) {
                $this->Image($logoPath, 10, 5, 25);
            }
            
            // Título con texto blanco sobre fondo azul
            $this->SetTextColor($colorTextoClaro[0], $colorTextoClaro[1], $colorTextoClaro[2]);
            $this->SetFont('Arial', 'B', $tamanoTitulo);
            $this->SetY(8); // Posicionar el título en la barra azul
            $titulo = $this->es_historial ? 'HISTORIAL DE RESERVAS' : 'LISTADO DE RESERVAS';
            $this->Cell(0, 10, $titulo, 0, 1, 'C');
            
            // Sección de información (fecha y filtros)
            $this->SetY(30); // Posicionar debajo de la barra azul
            $this->SetTextColor($colorTextoOscuro[0], $colorTextoOscuro[1], $colorTextoOscuro[2]);
            
            // Fecha y hora de generación con estilo mejorado
            $this->SetFont('Arial', 'B', $tamanoSubtitulo);
            $fecha_actual = date('d/m/Y H:i:s');
            
            // Crear un recuadro para la fecha
            $this->SetFillColor(245, 245, 245); // Gris muy claro
            $this->SetDrawColor(220, 220, 220); // Gris claro para el borde
            
            $textoFecha = 'Generado el: ' . $fecha_actual;
            $anchoFecha = $this->GetStringWidth($textoFecha) + 10;
            
            // En A4, centrar el recuadro
            $x = ($this->GetPageWidth() - $anchoFecha) / 2;
            $this->SetX($x);
            $this->Cell($anchoFecha, 8, $textoFecha, 1, 1, 'C', true);
            
            // Filtros aplicados con estilo mejorado
            $filtros = array();
            
            if ($this->es_historial) {
                // Mostrar que es un historial
                $filtros[] = 'Tipo: Historial de reservas pasadas';
                
                // Filtros de rango de fechas para historial
                if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
                    $filtros[] = 'Período: ' . date('d/m/Y', strtotime($_GET['fecha_inicio'])) . ' a ' . date('d/m/Y', strtotime($_GET['fecha_fin']));
                } elseif (!empty($_GET['fecha_inicio'])) {
                    $filtros[] = 'Desde: ' . date('d/m/Y', strtotime($_GET['fecha_inicio']));
                } elseif (!empty($_GET['fecha_fin'])) {
                    $filtros[] = 'Hasta: ' . date('d/m/Y', strtotime($_GET['fecha_fin']));
                }
            } else {
                // Filtro de fecha específica para reservas actuales
                if (!empty($_GET['filtro_fecha'])) {
                    $filtros[] = 'Fecha: ' . date('d/m/Y', strtotime($_GET['filtro_fecha']));
                }
            }
            
            // Filtros comunes
            if (!empty($_GET['filtro_estado'])) {
                $filtros[] = 'Estado: ' . ucfirst($_GET['filtro_estado']);
            }
            if (!empty($_GET['filtro_turno'])) {
                // Obtener el nombre del turno
                global $pdo;
                $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
                $stmt->execute([$_GET['filtro_turno']]);
                $turno = $stmt->fetch();
                if ($turno) {
                    $filtros[] = 'Turno: ' . ucfirst($turno['nombre']);
                }
            }
            if (!empty($_GET['filtro_zona'])) {
                $filtros[] = 'Zona: ' . ($_GET['filtro_zona'] === 'dentro' ? 'Interior' : 'Terraza');
            }
            
            if (!empty($filtros)) {
                $this->Ln(2);
                $this->SetFont('Arial', 'I', $tamanoSubtitulo);
                $textoFiltros = 'Filtros: ' . implode(', ', $filtros);
                $this->MultiCell(0, 6, $textoFiltros, 0, 'C');
            }
            
            $this->Ln(5);
        }
    }
    
    // Pie de página - personalizado según formato
    function Footer()
    {
        // Solo mostrar pie de página en formato A4, no en ticket
        if ($this->formato === 'a4') {
            // Posición a 1.5 cm del final
            $this->SetY(-15);
            
            // Texto del pie de página
            $this->SetTextColor(0); // Negro
            $this->SetFont('Arial', 'I', 8);
            
            // Número de página
            $this->Cell(0, 10, 'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
        // No mostrar pie de página en formato ticket
    }
    
    // Función para imprimir la tabla de reservas en formato A4
    function TablaReservasA4($reservas)
    {
        // Definir colores más atractivos y visuales
        $colorCabecera = array(41, 128, 185); // Azul
        $colorFilaImpar = array(240, 248, 255); // Azul muy claro
        $colorFilaPar = array(255, 255, 255); // Blanco
        $colorTextoHeader = array(255, 255, 255); // Blanco
        $colorBorde = array(70, 130, 180); // Azul acero
        $colorAlergenos = array(255, 240, 245); // Rosa muy claro
        $colorNecesidades = array(240, 255, 240); // Verde muy claro
        $colorContacto = array(248, 249, 250); // Gris muy claro
        
        // Configurar estilos
        $this->SetFillColor($colorCabecera[0], $colorCabecera[1], $colorCabecera[2]);
        $this->SetTextColor($colorTextoHeader[0], $colorTextoHeader[1], $colorTextoHeader[2]);
        $this->SetDrawColor($colorBorde[0], $colorBorde[1], $colorBorde[2]);
        $this->SetLineWidth(0.3);
        
        // Definir anchos de columna para datos principales
        $w = array(15, 60, 30, 30, 30, 20);
        $header = array('ID', 'Cliente', 'Fecha/Hora', 'Turno', 'Zona', 'Pers.');
        
        // Cabecera de la tabla principal
        $this->SetFont('Arial', 'B', 11);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 12, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Restaurar colores para las filas de datos
        $this->SetTextColor(0, 0, 0); // Negro para el texto
        $this->SetFont('Arial', '', 9); // Fuente normal para los datos
        
        // Datos con filas alternadas de color
        $fill = false; // Comenzar con fondo blanco
        
        foreach ($reservas as $reserva) {
            // Alternar colores de fondo para las filas
            if ($fill) {
                $this->SetFillColor($colorFilaImpar[0], $colorFilaImpar[1], $colorFilaImpar[2]);
            } else {
                $this->SetFillColor($colorFilaPar[0], $colorFilaPar[1], $colorFilaPar[2]);
            }
            
            // Limpiar observaciones
            $observaciones_limpias = $reserva['observaciones'];
            if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente') {
                $observaciones_limpias = preg_replace('/Personas solicitadas: \d+\s*\|?\s*/', '', $observaciones_limpias);
                $observaciones_limpias = preg_replace('/\|\s*$/', '', $observaciones_limpias);
                $observaciones_limpias = trim($observaciones_limpias);
            }
            
            // Limpiar cualquier mención de "Alérgenos:" en las observaciones
            if (!empty($observaciones_limpias)) {
                $observaciones_limpias = preg_replace('/^(Alérgenos:|Alergenos:|ALERGENOS:)\s*/i', '', $observaciones_limpias);
                $observaciones_limpias = trim($observaciones_limpias);
            }
            
            // Preparar información de alérgenos y necesidades especiales
            $alergenos = '';
            $necesidades = '';
            
            // Preparar alérgenos
            if ($reserva['tiene_alergenos'] && !empty($observaciones_limpias)) {
                $alergenos = $observaciones_limpias;
            } elseif (!empty($observaciones_limpias)) {
                $alergenos = $observaciones_limpias;
            }
            
            // Preparar necesidades especiales
            if (!empty($reserva['necesidades_especiales'])) {
                $necesidades = $reserva['necesidades_especiales'];
            }
            
            // Determinar la cantidad de personas
            $cantidad_personas = $reserva['cantidad_personas'];
            if ($cantidad_personas == 0 && $reserva['estado'] === 'pendiente' && !empty($reserva['observaciones'])) {
                if (preg_match('/Personas solicitadas: (\d+)/', $reserva['observaciones'], $matches)) {
                    $cantidad_personas = $matches[1];
                }
            }
            
            // Altura estándar para la fila principal
            $altura_fila = 10;
            
            // FILA 1: DATOS PRINCIPALES DE LA RESERVA
            // ID
            $this->Cell($w[0], $altura_fila, $reserva['id'], 1, 0, 'C', $fill);
            
            // Cliente - Solo nombre
            $this->Cell($w[1], $altura_fila, $reserva['nombre'], 1, 0, 'L', $fill);
            
            // Fecha/Hora
            $fechaHora = $reserva['fecha_formateada'] . "\n" . $reserva['hora_formateada'] . " h";
            $x_fecha = $this->GetX();
            $y_fecha = $this->GetY();
            $this->MultiCell($w[2], $altura_fila/2, $fechaHora, 1, 'C', $fill);
            $this->SetXY($x_fecha + $w[2], $y_fecha);
            
            // Turno
            $this->Cell($w[3], $altura_fila, ucfirst($reserva['turno_nombre']), 1, 0, 'C', $fill);
            
            // Zona
            $zona = ($reserva['zona'] === 'dentro') ? 'Interior' : 'Terraza';
            $this->Cell($w[4], $altura_fila, $zona, 1, 0, 'C', $fill);
            
            // Personas
            $this->Cell($w[5], $altura_fila, $cantidad_personas, 1, 1, 'C', $fill);
            
            // FILA 2: DATOS DE CONTACTO
            // Color de fondo suave para la fila de contacto
            $this->SetFillColor($colorContacto[0], $colorContacto[1], $colorContacto[2]);
            $this->SetFont('Arial', 'I', 8);
            
            // Etiqueta de contacto
            $this->Cell($w[0], 8, 'Contacto:', 1, 0, 'R', true);
            
            // Email con etiqueta (sin caracteres especiales)
            $email = !empty($reserva['email']) ? $reserva['email'] : '';
            $this->Cell($w[1] + $w[2], 8, "Correo: " . $email, 1, 0, 'L', true);
            
            // Telefono con etiqueta (sin caracteres especiales)
            $telefono = !empty($reserva['telefono']) ? $reserva['telefono'] : '';
            $this->Cell(array_sum($w) - $w[0] - $w[1] - $w[2], 8, "Telefono: " . $telefono, 1, 1, 'L', true);
            
            // Calcular el ancho disponible para el contenido
            $ancho_contenido = array_sum($w) - $w[0];
            
            // FILA 3: ALERGENOS (sin acento)
            if (!empty($alergenos)) {
                // Eliminar espacios innecesarios
                $alergenos = trim($alergenos);
                
                $this->SetFillColor($colorAlergenos[0], $colorAlergenos[1], $colorAlergenos[2]);
                $this->SetFont('Arial', 'B', 6); // Fuente más pequeña para la etiqueta
                
                // Crear una celda con altura fija para la etiqueta, un poco más ancha
                $this->Cell($w[0] + 5, 6, 'Alergenos:', 'LTB', 0, 'R', true); // Sin acento
                
                $this->SetFont('Arial', '', 6); // Fuente aún más pequeña para el texto
                
                // Acortar el texto si es demasiado largo
                if (strlen($alergenos) > 70) {
                    $alergenos = substr($alergenos, 0, 67) . '...';
                }
                
                // Usar Cell en lugar de MultiCell, con ancho reducido para compensar la etiqueta más ancha
                $this->Cell($ancho_contenido - 5, 6, $alergenos, 'RTB', 1, 'L', true);
            }
            
            // FILA 4: NECESIDADES ESPECIALES (solo si hay)
            if (!empty($necesidades)) {
                // Eliminar espacios innecesarios
                $necesidades = trim($necesidades);
                
                $this->SetFillColor($colorNecesidades[0], $colorNecesidades[1], $colorNecesidades[2]);
                $this->SetFont('Arial', 'B', 6); // Fuente más pequeña para la etiqueta
                
                // Crear una celda con altura fija para la etiqueta, un poco más ancha
                $this->Cell($w[0] + 5, 6, 'Necesidades:', 'LTB', 0, 'R', true);
                
                $this->SetFont('Arial', '', 6); // Fuente aún más pequeña para el texto
                
                // Acortar el texto si es demasiado largo
                if (strlen($necesidades) > 70) {
                    $necesidades = substr($necesidades, 0, 67) . '...';
                }
                
                // Usar Cell en lugar de MultiCell, con ancho reducido para compensar la etiqueta más ancha
                $this->Cell($ancho_contenido - 5, 6, $necesidades, 'RTB', 1, 'L', true);
            }
            
            // FILA 5: CHECKBOXES PERSONALIZADOS (solo si hay)
            if (!empty($reserva['checkboxes_seleccionados'])) {
                // Eliminar espacios innecesarios
                $checkboxes = trim($reserva['checkboxes_seleccionados']);
                
                // Usar un color azul claro para los checkboxes
                $this->SetFillColor(230, 240, 255); // Azul muy claro
                $this->SetFont('Arial', 'B', 6); // Fuente más pequeña para la etiqueta
                
                // Crear una celda con altura fija para la etiqueta, un poco más ancha
                $this->Cell($w[0] + 5, 6, 'Preferencias:', 'LTB', 0, 'R', true);
                
                $this->SetFont('Arial', '', 6); // Fuente aún más pequeña para el texto
                
                // Convertir la lista separada por comas a una lista con viñetas
                $checkboxes_array = explode(', ', $checkboxes);
                $checkboxes_formateados = '';
                foreach ($checkboxes_array as $checkbox) {
                    $checkboxes_formateados .= '• ' . trim($checkbox) . ' ';
                }
                
                // Acortar el texto si es demasiado largo
                if (strlen($checkboxes_formateados) > 70) {
                    $checkboxes_formateados = substr($checkboxes_formateados, 0, 67) . '...';
                }
                
                // Usar Cell en lugar de MultiCell, con ancho reducido para compensar la etiqueta más ancha
                $this->Cell($ancho_contenido - 5, 6, $checkboxes_formateados, 'RTB', 1, 'L', true);
            }
            
            // Restaurar fuente
            $this->SetFont('Arial', '', 9);
            
            // Añadir espacio entre reservas
            $this->Ln(5);
            
            // Alternar el relleno para la siguiente fila
            $fill = !$fill;
        }
    }
    
    // Nota: La funcionalidad de PDF de ticket ha sido eliminada
    // Solo se mantiene el formato A4
}

// Crear instancia de PDF (solo formato A4)
$pdf = new PDF('a4', $es_historial);
$pdf->AliasNbPages();
$pdf->AddPage();

// Generar la tabla en formato A4
$pdf->TablaReservasA4($reservas);

// Configurar metadatos del PDF
$titulo_pdf = $es_historial ? 'Historial de Reservas' : 'Listado de Reservas';
$pdf->SetTitle($titulo_pdf);
$pdf->SetAuthor('Sistema de Reservas');
$pdf->SetCreator('Sistema de Reservas');

// Salida normal para formato A4
$pdf->Output();
