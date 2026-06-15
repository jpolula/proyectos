-- Script de inicialización de la base de datos para Baru Summer Club

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS baru_summer_club;

-- Usar la base de datos
USE baru_summer_club;

-- Crear tabla de tickets
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_consumicion VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    frase_del_dia VARCHAR(255),
    condiciones_entrada TEXT,
    condiciones_consumicion TEXT,
    icono VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de configuración
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    valor TEXT,
    UNIQUE KEY (nombre)
);

-- Insertar configuración por defecto
INSERT IGNORE INTO configuracion (nombre, valor) VALUES 
    ('precio_copa', '5.00'),
    ('precio_cerveza', '3.50'),
    ('precio_sin_consumicion', '12.50'),
    ('nombre_club', 'BARU SUMMER CLUB');
