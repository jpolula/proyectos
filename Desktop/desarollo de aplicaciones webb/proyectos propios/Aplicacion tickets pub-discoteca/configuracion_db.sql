-- Script para crear una nueva base de datos para la aplicación de tickets
-- Usuario: root (sin contraseña)

-- Crear la nueva base de datos
CREATE DATABASE IF NOT EXISTS tikets_db;

-- Usar la nueva base de datos
USE tikets_db;

-- Crear la tabla principal de configuración
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_club VARCHAR(100) DEFAULT 'Club de Eventos',
    cif VARCHAR(20) DEFAULT '',
    direccion1 VARCHAR(100) DEFAULT '',
    direccion2 VARCHAR(100) DEFAULT '',
    precio_copa DECIMAL(10,2) DEFAULT 5.00,
    precio_cerveza DECIMAL(10,2) DEFAULT 3.50,
    precio_sin_consumicion DECIMAL(10,2) DEFAULT 10.00,
    precio_solo_ticket DECIMAL(10,2) DEFAULT 3.00,
    ruta_logo VARCHAR(255) DEFAULT '',
    frase_del_dia TEXT,
    condiciones_entrada TEXT DEFAULT 'Prohibida la entrada a menores de 18 años',
    condiciones_consumicion TEXT DEFAULT 'Válido para una consumición',
    imprimir_ticket BOOLEAN DEFAULT TRUE,
    mostrar_precio BOOLEAN DEFAULT TRUE,
    imprimir_vale BOOLEAN DEFAULT TRUE,
    impresora VARCHAR(100) DEFAULT '',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crear tabla para tickets generados
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_ticket VARCHAR(20) NOT NULL,
    tipo_consumicion ENUM('COPA', 'CERVEZA', 'SIN_CONSUMICION', 'SOLO_TICKET') NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    impreso BOOLEAN DEFAULT FALSE,
    anulado BOOLEAN DEFAULT FALSE
);

-- Insertar un registro inicial en la tabla de configuración
INSERT INTO configuracion (id) 
SELECT 1 FROM dual 
WHERE NOT EXISTS (SELECT * FROM configuracion);
