<?php
// Incluir la clase de personalización
require_once __DIR__ . '/../src/Utils/Personalizacion.php';

// Crear una instancia de la clase de personalización
$personalizacion = new Personalizacion();

// Obtener el CSS de personalización
$css_personalizacion = $personalizacion->getCSS();

// Función para obtener el logo
function obtener_logo($clases = '') {
    global $personalizacion;
    return $personalizacion->getLogoHTML($clases);
}

// Función para obtener el color principal
function obtener_color_principal() {
    global $personalizacion;
    return $personalizacion->getColorPrincipal();
}

// Función para obtener el tipo de letra
function obtener_tipo_letra() {
    global $personalizacion;
    return $personalizacion->getTipoLetra();
}

// Función para obtener el título principal
function obtener_titulo_principal() {
    global $personalizacion;
    return $personalizacion->getTituloPrincipal();
}

// Función para obtener el subtítulo
function obtener_subtitulo() {
    global $personalizacion;
    return $personalizacion->getSubtitulo();
}

// Función para obtener el favicon
function obtener_favicon() {
    global $personalizacion;
    return $personalizacion->getFaviconHTML();
}

// Imprimir el CSS de personalización
echo $css_personalizacion;

// Agregar estilos globales para formularios
echo '<style>
    /* Asegurar que todos los campos de formulario tengan texto negro */
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="number"],
    input[type="password"],
    input[type="date"],
    input[type="time"],
    input[type="datetime-local"],
    select,
    textarea {
        color: black !important;
    }
</style>';

// Imprimir el favicon
echo obtener_favicon();
?>
