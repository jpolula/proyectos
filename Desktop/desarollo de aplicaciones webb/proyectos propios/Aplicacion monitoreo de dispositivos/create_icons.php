<?php
// Asegurarse de que exista el directorio de iconos
if (!file_exists('icons')) {
    mkdir('icons');
}

// Función para crear un icono simple
function createBasicIcon($name) {
    $width = 48;
    $height = 48;
    
    $im = imagecreatetruecolor($width, $height);
    
    // Hacer el fondo transparente
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);
    
    // Colores básicos
    $black = imagecolorallocate($im, 0, 0, 0);
    $white = imagecolorallocate($im, 255, 255, 255);
    $gray = imagecolorallocate($im, 128, 128, 128);
    
    // Dibujar el icono según el tipo
    switch ($name) {
        case 'router':
            imagefilledrectangle($im, 8, 16, 40, 32, $black);
            imageline($im, 16, 8, 16, 16, $black);
            imageline($im, 24, 8, 24, 16, $black);
            imageline($im, 32, 8, 32, 16, $black);
            break;
            
        case 'parabolica':
            imagearc($im, 24, 32, 32, 32, 180, 360, $black);
            imageline($im, 24, 32, 24, 8, $black);
            imagefilledellipse($im, 24, 20, 8, 8, $black);
            break;
            
        case 'radio':
            imagefilledrectangle($im, 12, 8, 36, 40, $black);
            for ($i = 0; $i < 3; $i++) {
                imageline($im, 16, 16 + ($i * 8), 32, 16 + ($i * 8), $gray);
            }
            break;
            
        case 'pc':
            imagefilledrectangle($im, 8, 8, 40, 32, $black);
            imagefilledrectangle($im, 18, 32, 30, 36, $black);
            imagefilledrectangle($im, 14, 36, 34, 40, $black);
            imagefilledrectangle($im, 12, 12, 36, 28, $white);
            break;
            
        case 'casa':
            imagefilledpolygon($im, [24,4, 4,24, 44,24], 3, $black);
            imagefilledrectangle($im, 8, 24, 40, 44, $black);
            imagefilledrectangle($im, 20, 32, 28, 44, $white);
            imagefilledrectangle($im, 12, 28, 18, 34, $white);
            imagefilledrectangle($im, 30, 28, 36, 34, $white);
            break;
            
        case 'switch':
            imagefilledrectangle($im, 4, 16, 44, 32, $black);
            for ($i = 0; $i < 6; $i++) {
                imagefilledrectangle($im, 8 + ($i * 6), 20, 12 + ($i * 6), 28, $white);
            }
            imagefilledellipse($im, 14, 18, 3, 3, $gray);
            imagefilledellipse($im, 24, 18, 3, 3, $gray);
            imagefilledellipse($im, 34, 18, 3, 3, $gray);
            break;
            
        case 'ap':
            imagefilledellipse($im, 24, 24, 24, 24, $black);
            for ($i = 0; $i < 3; $i++) {
                imagearc($im, 24, 24, 16 + ($i * 8), 16 + ($i * 8), 45, 135, $white);
                imagearc($im, 24, 24, 16 + ($i * 8), 16 + ($i * 8), 225, 315, $white);
            }
            imagefilledellipse($im, 24, 24, 8, 8, $white);
            break;
    }
    
    return $im;
}

// Crear los iconos si no existen
$icons = ['router', 'parabolica', 'radio', 'pc', 'casa', 'switch', 'ap'];
foreach ($icons as $icon) {
    $iconPath = "icons/{$icon}.png";
    if (!file_exists($iconPath)) {
        $im = createBasicIcon($icon);
        imagepng($im, $iconPath);
        imagedestroy($im);
    }
}
