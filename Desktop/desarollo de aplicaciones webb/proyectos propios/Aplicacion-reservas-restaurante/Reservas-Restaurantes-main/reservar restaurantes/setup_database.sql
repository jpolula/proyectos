-- Script de configuración de la base de datos para el sistema de reservas de restaurantes
-- Versión actualizada: 13/05/2025

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS restaurante_reservas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurante_reservas;

-- Tabla de administrador
CREATE TABLE administrador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    -- Campos para la configuración de correo
    email_remitente VARCHAR(100),
    email_password VARCHAR(255),
    email_host VARCHAR(100) DEFAULT 'smtp.gmail.com',
    email_puerto INT DEFAULT 587,
    email_seguridad ENUM('tls', 'ssl') DEFAULT 'tls',
    email_nombre_remitente VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de turnos (editable por el administrador)
CREATE TABLE turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre ENUM('mediodia', 'noche') NOT NULL UNIQUE,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de reservas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    hora TIME NOT NULL,
    cantidad_personas INT NOT NULL,
    personas_solicitadas INT DEFAULT 0,
    observaciones TEXT,
    necesidades_especiales TEXT,
    tiene_alergenos BOOLEAN DEFAULT FALSE,
    alergenos TEXT,
    estado ENUM('pendiente', 'confirmada', 'rechazada', 'cancelada', 'archivada') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Capacidad por fecha + zona + turno
CREATE TABLE capacidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    aforo_maximo INT NOT NULL,
    UNIQUE(fecha, zona, turno_id),
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bloqueos de reservas
CREATE TABLE bloqueos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    motivo TEXT,
    UNIQUE(fecha, zona, turno_id),
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración general
CREATE TABLE configuracion (
    id INT PRIMARY KEY,
    max_personas_sin_aprobacion INT NOT NULL DEFAULT 4,
    email_activo BOOLEAN DEFAULT FALSE,
    notificar_admin ENUM('todas', 'pendientes', 'ninguna') DEFAULT 'pendientes',
    notificaciones_admin ENUM('todas', 'pendientes', 'ninguna') DEFAULT 'pendientes',
    -- Campos para capacidad
    capacidad_dentro_mediodia INT NOT NULL DEFAULT 30,
    capacidad_fuera_mediodia INT NOT NULL DEFAULT 20,
    capacidad_dentro_noche INT NOT NULL DEFAULT 35,
    capacidad_fuera_noche INT NOT NULL DEFAULT 25,
    -- Campos para configuración de correo
    email_remitente VARCHAR(100),
    email_password VARCHAR(255),
    email_host VARCHAR(100) DEFAULT 'smtp.gmail.com',
    email_puerto INT DEFAULT 587,
    email_seguridad ENUM('tls', 'ssl') DEFAULT 'tls',
    email_nombre_remitente VARCHAR(100),
    -- Campos para personalización
    logo_path VARCHAR(255),
    titulo_principal VARCHAR(100) DEFAULT 'Sistema de Reservas',
    subtitulo VARCHAR(200) DEFAULT 'Reserve su mesa fácilmente',
    color_principal VARCHAR(20) DEFAULT '#4A5568',
    color_secundario VARCHAR(20) DEFAULT '#38B2AC',
    mostrar_confeti BOOLEAN DEFAULT TRUE,
    tipo_letra VARCHAR(255) DEFAULT 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial',
    -- Campo para redirección tras reserva
    url_redireccion_reserva VARCHAR(255) DEFAULT 'reserva_exitosa.php' COMMENT 'URL a la que redirigir tras una reserva exitosa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de días disponibles
CREATE TABLE dias_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    disponible BOOLEAN NOT NULL DEFAULT FALSE,
    UNIQUE(fecha, zona, turno_id),
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de avisos de reserva
CREATE TABLE avisos_reserva (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    texto TEXT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    orden INT NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuración por defecto
INSERT INTO configuracion (id, max_personas_sin_aprobacion, email_activo, notificar_admin, notificaciones_admin, capacidad_dentro_mediodia, capacidad_fuera_mediodia, capacidad_dentro_noche, capacidad_fuera_noche, email_remitente, email_password, email_host, email_puerto, email_seguridad, email_nombre_remitente, logo_path, titulo_principal, subtitulo, color_principal, color_secundario, mostrar_confeti, tipo_letra, url_redireccion_reserva) 
VALUES (1, 4, FALSE, 'pendientes', 'pendientes', 30, 20, 35, 25, '', '', 'smtp.gmail.com', 587, 'tls', '', '', 'Sistema de Reservas', 'Reserve su mesa fácilmente', '#4A5568', '#38B2AC', TRUE, 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial', 'reserva_exitosa.php');

-- Insertar turnos por defecto
INSERT INTO turnos (nombre, hora_inicio, hora_fin, activo) VALUES 
('mediodia', '13:00:00', '16:00:00', TRUE),
('noche', '20:00:00', '23:00:00', TRUE);

-- Insertar avisos de ejemplo
INSERT INTO avisos_reserva (texto, orden, activo) VALUES 
('Debes llegar con al menos 10 minutos de antelación o podrías perder tu reserva.', 1, 1),
('Si necesitas cancelar tu reserva, por favor hazlo con al menos 2 horas de antelación.', 2, 1),
('Para grupos de más de 8 personas, se requiere un depósito del 20% que será descontado de la cuenta final.', 3, 1);

-- Tabla para los checkboxes personalizados
CREATE TABLE checkboxes_personalizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto VARCHAR(255) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    orden INT NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para almacenar las respuestas de los checkboxes en las reservas
CREATE TABLE reservas_checkboxes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    checkbox_id INT NOT NULL,
    valor BOOLEAN DEFAULT FALSE,
    texto_respuesta TEXT NULL DEFAULT NULL COMMENT 'Almacena el texto introducido en el textarea asociado al checkbox',
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (checkbox_id) REFERENCES checkboxes_personalizados(id) ON DELETE CASCADE,
    UNIQUE(reserva_id, checkbox_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar algunos checkboxes de ejemplo
INSERT INTO checkboxes_personalizados (texto, descripcion, activo, orden) VALUES 
('Acepto la política de privacidad', 'El cliente acepta nuestra política de privacidad y tratamiento de datos', TRUE, 1),
('Deseo recibir ofertas y promociones', 'El cliente desea recibir información sobre ofertas y promociones', TRUE, 2);
