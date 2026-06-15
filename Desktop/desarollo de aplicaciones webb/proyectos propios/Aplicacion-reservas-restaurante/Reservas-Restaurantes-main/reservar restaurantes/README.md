# Sistema de Reservas de Restaurantes

Aplicación web para la gestión de reservas de restaurantes desarrollada con PHP.

## Requisitos

- PHP 7.4 o superior
- Composer
- MySQL/MariaDB
- Servidor web (Apache/Nginx)

## Instalación

1. Clona este repositorio o descárgalo como ZIP
2. Navega al directorio del proyecto
3. Ejecuta `composer install` para instalar las dependencias
4. Copia el archivo `.env.example` a `.env` y configura tus variables de entorno
5. Configura tu servidor web para que apunte al directorio `public/` como raíz

## Estructura del Proyecto

```
reservar-restaurantes/
├── public/             # Archivos públicos accesibles desde el navegador
│   ├── css/            # Hojas de estilo CSS
│   ├── js/             # Archivos JavaScript
│   ├── images/         # Imágenes y recursos gráficos
│   └── index.php       # Punto de entrada principal
├── src/                # Código fuente de la aplicación
│   ├── Config/         # Archivos de configuración
│   ├── Controllers/    # Controladores
│   ├── Models/         # Modelos de datos
│   ├── Views/          # Vistas y plantillas
│   └── Utils/          # Utilidades y clases auxiliares
├── vendor/             # Dependencias de Composer (generado automáticamente)
├── .env                # Variables de entorno (crear a partir de .env.example)
├── .env.example        # Ejemplo de configuración de variables de entorno
├── .gitignore          # Archivos y directorios ignorados por Git
├── composer.json       # Definición de dependencias
└── README.md           # Documentación del proyecto
```

## Funcionalidades

- Gestión de reservas de restaurantes
- Envío de confirmaciones por correo electrónico
- (Más funcionalidades por implementar)

## Configuración de Correo Electrónico

Para habilitar el envío de correos electrónicos, configura las siguientes variables en tu archivo `.env`:

```
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Reservas Restaurantes"
```

## Licencia

Este proyecto está bajo la Licencia MIT.
