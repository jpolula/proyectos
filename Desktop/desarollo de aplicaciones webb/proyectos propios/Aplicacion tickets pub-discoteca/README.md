# Baru Summer Club - Sistema de Tickets

Esta aplicación permite gestionar tickets para el Baru Summer Club, con diferentes opciones de consumición (COPA, CERVEZAS, SIN CONSUMICIÓN).

## Características

- Creación de tickets con diferentes tipos de consumición
- Configuración de precios para cada tipo de consumición
- Personalización de frases del día y condiciones
- Generación de tickets en formato PDF
- Selección de iconos personalizados para los tickets

## Requisitos

- Java 17 o superior
- MySQL Server
- Maven

## Configuración de la Base de Datos

La aplicación se conecta a MySQL con el usuario `root` sin contraseña. La base de datos `baru_summer_club` se creará automáticamente al iniciar la aplicación si no existe.

## Uso

1. Ejecuta la aplicación con Maven:
   ```
   mvn clean compile exec:java
   ```

2. En la ventana principal, selecciona el tipo de consumición, la cantidad y completa los campos adicionales.

3. Haz clic en "Crear ticket" para generar un nuevo ticket.

4. Para configurar los precios y otras opciones, haz clic en el botón "Configuracion".

## Estructura del Proyecto

- `model`: Contiene las clases de modelo de datos
- `view`: Contiene las clases de la interfaz gráfica
- `controller`: Contiene las clases controladoras
- `util`: Contiene clases de utilidad

## Desarrollado por

Baru Summer Club Team
