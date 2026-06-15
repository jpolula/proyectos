-- Crear base de datos
CREATE DATABASE IF NOT EXISTS restaurante_reservas;
USE restaurante_reservas;

-- Tabla de administrador
CREATE TABLE administrador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- Tabla de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL
);

-- Tabla de turnos (editable por el administrador)
CREATE TABLE turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre ENUM('mediodia', 'noche') NOT NULL UNIQUE,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL
);

-- Tabla de reservas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    hora TIME NOT NULL,
    cantidad_personas INT NOT NULL,
    observaciones TEXT,
    necesidades_especiales TEXT,
    tiene_alergenos BOOLEAN DEFAULT FALSE,
    estado ENUM('pendiente', 'confirmada', 'rechazada') DEFAULT 'pendiente',
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (turno_id) REFERENCES turnos(id)
);

-- Capacidad por fecha + zona + turno
CREATE TABLE capacidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    aforo_maximo INT NOT NULL,
    UNIQUE(fecha, zona, turno_id),
    FOREIGN KEY (turno_id) REFERENCES turnos(id)
);

-- Bloqueos de reservas
CREATE TABLE bloqueos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    motivo TEXT,
    UNIQUE(fecha, zona, turno_id),
    FOREIGN KEY (turno_id) REFERENCES turnos(id)
);

-- Configuración general
CREATE TABLE configuracion (
    id INT PRIMARY KEY,
    max_personas_sin_aprobacion INT NOT NULL DEFAULT 4,
    email_remitente VARCHAR(100),
    email_password VARCHAR(255),
    email_host VARCHAR(100) DEFAULT 'smtp.gmail.com',
    email_puerto INT DEFAULT 587,
    email_seguridad ENUM('tls', 'ssl') DEFAULT 'tls',
    email_nombre_remitente VARCHAR(100),
    email_activo BOOLEAN DEFAULT FALSE
);

-- Insertar configuración por defecto
INSERT INTO configuracion (id, max_personas_sin_aprobacion) VALUES (1, 4);

-- Insertar turnos por defecto
INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
('mediodia', '13:00:00', '16:00:00'),
('noche', '20:00:00', '23:00:00');

CREATE TABLE dias_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    zona ENUM('dentro', 'fuera') NOT NULL,
    turno_id INT NOT NULL,
    disponible BOOLEAN NOT NULL DEFAULT FALSE,
    UNIQUE(fecha, zona, turno_id),
    FOREIGN KEY (turno_id) REFERENCES turnos(id)
);