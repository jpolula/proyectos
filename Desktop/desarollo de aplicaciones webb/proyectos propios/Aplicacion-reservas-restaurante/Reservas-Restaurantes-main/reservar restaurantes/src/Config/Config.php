<?php
namespace App\Config;

class Config {
    /**
     * Inicializa la configuración de la aplicación
     */
    public static function init() {
        // Cargar variables de entorno
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->safeLoad();
        
        // Configuración de zona horaria
        date_default_timezone_set('Europe/Madrid');
        
        // Configuración de errores
        if ($_ENV['APP_ENV'] ?? 'development' === 'production') {
            error_reporting(0);
            ini_set('display_errors', 0);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
        
        // Iniciar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Obtiene una variable de entorno
     * 
     * @param string $key Clave de la variable
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public static function env($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}
