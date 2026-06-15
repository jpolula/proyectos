<?php
/**
 * Clase para gestionar la personalización del sitio
 */
class Personalizacion {
    private $pdo;
    private $config;
    
    /**
     * Constructor
     */
    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Obtener la configuración de personalización
            $stmt = $this->pdo->query("SELECT logo_path, color_principal, color_secundario, tipo_letra, titulo_principal, subtitulo FROM configuracion WHERE id = 1");
            $this->config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Establecer valores por defecto si no existen
            if (!isset($this->config['titulo_principal']) || empty($this->config['titulo_principal'])) {
                $this->config['titulo_principal'] = 'Sistema de Reservas de Restaurantes';
            }
            
            if (!isset($this->config['subtitulo']) || empty($this->config['subtitulo'])) {
                $this->config['subtitulo'] = 'Introduce tus datos para comenzar la reserva';
            }
            
        } catch (PDOException $e) {
            // Valores por defecto si hay error
            $this->config = [
                'logo_path' => null,
                'color_principal' => '#3B82F6',
                'color_secundario' => '#FF9800',
                'tipo_letra' => 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial',
                'titulo_principal' => 'Sistema de Reservas de Restaurantes',
                'subtitulo' => 'Introduce tus datos para comenzar la reserva'
            ];
        }
    }
    
    /**
     * Obtener la ruta del logo
     * 
     * @return string|null Ruta del logo o null si no hay logo
     */
    public function getLogo() {
        return $this->config['logo_path'];
    }
    
    /**
     * Obtener el color principal
     * 
     * @return string Color principal en formato hexadecimal
     */
    public function getColorPrincipal() {
        return $this->config['color_principal'];
    }
    
    /**
     * Obtener el color secundario
     * 
     * @return string Color secundario en formato hexadecimal
     */
    public function getColorSecundario() {
        return $this->config['color_secundario'] ?? '#FF9800';
    }
    
    /**
     * Obtener el tipo de letra
     * 
     * @return string Tipo de letra
     */
    public function getTipoLetra() {
        return $this->config['tipo_letra'];
    }
    
    /**
     * Obtener el título principal
     * 
     * @return string Título principal
     */
    public function getTituloPrincipal() {
        return $this->config['titulo_principal'];
    }
    
    /**
     * Obtener el subtítulo
     * 
     * @return string Subtítulo
     */
    public function getSubtitulo() {
        return $this->config['subtitulo'];
    }
    
    /**
     * Generar el HTML para incluir el logo
     * 
     * @param string $clases Clases CSS adicionales
     * @param string $alt Texto alternativo
     * @return string HTML del logo o texto alternativo si no hay logo
     */
    public function getLogoHTML($clases = '', $alt = 'Logo del Restaurante') {
        if (!empty($this->config['logo_path'])) {
            // Construir la ruta completa al archivo
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/reservar restaurantes/' . $this->config['logo_path'];
            
            // Verificar si el archivo existe
            if (file_exists($filePath)) {
                return '<img src="/reservar%20restaurantes/' . htmlspecialchars($this->config['logo_path']) . '" alt="' . htmlspecialchars($alt) . '" class="' . htmlspecialchars($clases) . '">';
            }
            
            // Si el archivo no existe, escribir un mensaje en el log para depuración
            error_log("Logo no encontrado en: " . $filePath);
        }
        
        // Si no hay logo, devolver texto alternativo
        return '<span class="' . htmlspecialchars($clases) . '">Sistema de Reservas</span>';
    }
    
    /**
     * Generar el HTML para incluir el favicon
     * 
     * @return string HTML del favicon o cadena vacía si no hay logo
     */
    public function getFaviconHTML() {
        if (!empty($this->config['logo_path'])) {
            // Construir la ruta completa al archivo
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/reservar restaurantes/' . $this->config['logo_path'];
            
            // Verificar si el archivo existe
            if (file_exists($filePath)) {
                $extension = pathinfo($this->config['logo_path'], PATHINFO_EXTENSION);
                $mimeType = 'image/png'; // Por defecto
                
                // Determinar el tipo MIME según la extensión
                switch (strtolower($extension)) {
                    case 'jpg':
                    case 'jpeg':
                        $mimeType = 'image/jpeg';
                        break;
                    case 'png':
                        $mimeType = 'image/png';
                        break;
                    case 'gif':
                        $mimeType = 'image/gif';
                        break;
                    case 'svg':
                        $mimeType = 'image/svg+xml';
                        break;
                }
                
                return '<link rel="icon" type="' . $mimeType . '" href="/reservar%20restaurantes/' . htmlspecialchars($this->config['logo_path']) . '">';
            }
            
            // Si el archivo no existe, escribir un mensaje en el log para depuración
            error_log("Favicon no encontrado en: " . $filePath);
        }
        
        return '';
    }
    
    /**
     * Generar el CSS para aplicar el color principal y el tipo de letra
     * 
     * @return string CSS para aplicar la personalización
     */
    public function getCSS() {
        $color = $this->getColorPrincipal();
        $colorSecundario = $this->getColorSecundario();
        $tipoLetra = $this->getTipoLetra();
        
        // Generar variantes de color (más oscuro y más claro)
        $colorDark = $this->adjustBrightness($color, -20);
        $colorLight = $this->adjustBrightness($color, 40);
        $colorSecundarioDark = $this->adjustBrightness($colorSecundario, -20);
        $colorSecundarioLight = $this->adjustBrightness($colorSecundario, 40);
        
        // Cargar fuente de Google si es necesaria
        $fontLink = '';
        if ($tipoLetra !== 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial') {
            $fontName = trim(explode(',', str_replace("'", '', $tipoLetra))[0]);
            $fontLink = '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=' . $fontName . ':wght@400;700&display=swap">';
        }
        
        return $fontLink . '
        <style>
            :root {
                --color-primary: ' . $color . ';
                --color-primary-dark: ' . $colorDark . ';
                --color-primary-light: ' . $colorLight . ';
                --color-secondary: ' . $colorSecundario . ';
                --color-secondary-dark: ' . $colorSecundarioDark . ';
                --color-secondary-light: ' . $colorSecundarioLight . ';
                --font-family: ' . $tipoLetra . ';
            }
            
            body {
                font-family: var(--font-family);
            }
            
            /* Reemplazar colores de Tailwind */
            .bg-blue-600 {
                background-color: var(--color-primary) !important;
            }
            
            .bg-blue-500 {
                background-color: var(--color-primary) !important;
            }
            
            .bg-blue-700 {
                background-color: var(--color-primary-dark) !important;
            }
            
            .hover\:bg-blue-700:hover {
                background-color: var(--color-primary-dark) !important;
            }
            
            .hover\:bg-blue-600:hover {
                background-color: var(--color-primary-dark) !important;
            }
            
            .hover\:bg-blue-500:hover {
                background-color: var(--color-primary) !important;
            }
            
            .text-blue-600 {
                color: var(--color-primary) !important;
            }
            
            .text-blue-500 {
                color: var(--color-primary) !important;
            }
            
            .hover\:text-blue-500:hover {
                color: var(--color-primary) !important;
            }
            
            .focus\:border-blue-500:focus {
                border-color: var(--color-primary) !important;
            }
            
            .focus\:ring-blue-500:focus {
                --tw-ring-color: var(--color-primary) !important;
            }
            
            .focus\:ring-offset-2:focus {
                --tw-ring-offset-width: 2px;
            }
            
            .focus\:ring-2:focus {
                --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
            }
            
            /* Botones */
            .btn-primary {
                background-color: var(--color-primary) !important;
                color: white !important;
            }
            
            .btn-primary:hover {
                background-color: var(--color-primary-dark) !important;
            }
            
            /* Elementos seleccionables con color secundario */
            .flatpickr-day.selected,
            .flatpickr-day.startRange,
            .flatpickr-day.endRange,
            .flatpickr-day.selected.inRange,
            .flatpickr-day.startRange.inRange,
            .flatpickr-day.endRange.inRange,
            .flatpickr-day.selected:focus,
            .flatpickr-day.startRange:focus,
            .flatpickr-day.endRange:focus,
            .flatpickr-day.selected:hover,
            .flatpickr-day.startRange:hover,
            .flatpickr-day.endRange:hover,
            .flatpickr-day.selected.prevMonthDay,
            .flatpickr-day.startRange.prevMonthDay,
            .flatpickr-day.endRange.prevMonthDay,
            .flatpickr-day.selected.nextMonthDay,
            .flatpickr-day.startRange.nextMonthDay,
            .flatpickr-day.endRange.nextMonthDay {
                background: var(--color-secondary) !important;
                border-color: var(--color-secondary) !important;
            }
            
            /* Botones de selección */
            .option-selected,
            .option-active,
            .selected-option,
            [data-selected="true"],
            .active-selection {
                background-color: var(--color-secondary) !important;
                border-color: var(--color-secondary-dark) !important;
                color: white !important;
            }
            
            /* Elementos interactivos */
            input:checked + label,
            .radio-checked,
            .checkbox-checked {
                background-color: var(--color-secondary-light) !important;
                border-color: var(--color-secondary) !important;
            }
            
            /* Elementos de selección personalizados */
            .custom-select-option.selected,
            .custom-radio:checked + label,
            .custom-checkbox:checked + label {
                background-color: var(--color-secondary-light) !important;
                border-color: var(--color-secondary) !important;
            }
        </style>';
    }
    
    /**
     * Ajustar el brillo de un color hexadecimal
     * 
     * @param string $hex Color en formato hexadecimal
     * @param int $steps Pasos para ajustar el brillo (positivo = más claro, negativo = más oscuro)
     * @return string Color ajustado en formato hexadecimal
     */
    private function adjustBrightness($hex, $steps) {
        // Eliminar el # si existe
        $hex = ltrim($hex, '#');
        
        // Convertir a RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Ajustar brillo
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        
        // Convertir de nuevo a hexadecimal
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
?>
