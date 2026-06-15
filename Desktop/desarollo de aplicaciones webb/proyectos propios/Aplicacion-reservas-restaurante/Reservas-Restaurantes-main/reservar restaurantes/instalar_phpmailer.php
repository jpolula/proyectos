<?php
require_once 'enviar_correo_directo.php';
// Script para instalar PHPMailer manualmente
echo "<h1>Instalando PHPMailer</h1>";

// Crear directorio vendor si no existe
if (!is_dir('vendor')) {
    mkdir('vendor', 0777, true);
    echo "<p>Directorio vendor creado.</p>";
}

// Crear directorio para PHPMailer
if (!is_dir('vendor/phpmailer')) {
    mkdir('vendor/phpmailer', 0777, true);
    echo "<p>Directorio vendor/phpmailer creado.</p>";
}

// Crear directorio para la biblioteca
if (!is_dir('vendor/phpmailer/phpmailer')) {
    mkdir('vendor/phpmailer/phpmailer', 0777, true);
    echo "<p>Directorio vendor/phpmailer/phpmailer creado.</p>";
}

// Crear directorio src
if (!is_dir('vendor/phpmailer/phpmailer/src')) {
    mkdir('vendor/phpmailer/phpmailer/src', 0777, true);
    echo "<p>Directorio vendor/phpmailer/phpmailer/src creado.</p>";
}

// URLs de los archivos principales de PHPMailer
$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'POP3.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/POP3.php',
    'OAuth.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/OAuth.php'
];

// Descargar cada archivo
foreach ($files as $filename => $url) {
    $destination = "vendor/phpmailer/phpmailer/src/$filename";
    
    echo "<p>Descargando $filename...</p>";
    $content = file_get_contents($url);
    
    if ($content === false) {
        echo "<p style='color:red'>Error al descargar $filename</p>";
        continue;
    }
    
    $result = file_put_contents($destination, $content);
    
    if ($result === false) {
        echo "<p style='color:red'>Error al guardar $filename</p>";
    } else {
        echo "<p style='color:green'>$filename descargado correctamente</p>";
    }
}

// Crear autoload.php
$autoload_content = '<?php
// Autoloader para PHPMailer
spl_autoload_register(function ($class) {
    // Verificar si la clase pertenece al namespace de PHPMailer
    if (strpos($class, "PHPMailer\\\\PHPMailer\\\\") === 0) {
        // Convertir namespace a ruta de archivo
        $file = __DIR__ . "/phpmailer/phpmailer/src/" . str_replace("PHPMailer\\\\PHPMailer\\\\", "", $class) . ".php";
        $file = str_replace("\\\\", "/", $file);
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});
';

file_put_contents('vendor/autoload.php', $autoload_content);
echo "<p style='color:green'>Archivo autoload.php creado</p>";

// Crear composer.json
$composer_json = '{
    "require": {
        "phpmailer/phpmailer": "^6.8"
    },
    "autoload": {
        "psr-4": {
            "PHPMailer\\\\PHPMailer\\\\": "vendor/phpmailer/phpmailer/src/"
        }
    }
}';

file_put_contents('composer.json', $composer_json);
echo "<p style='color:green'>Archivo composer.json creado</p>";

echo "<h2 style='color:green'>Instalación completada</h2>";
echo "<p>PHPMailer ha sido instalado manualmente en el directorio vendor.</p>";
echo "<p><a href='test_email.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;'>Volver a la página de prueba de correo</a></p>";
?>
