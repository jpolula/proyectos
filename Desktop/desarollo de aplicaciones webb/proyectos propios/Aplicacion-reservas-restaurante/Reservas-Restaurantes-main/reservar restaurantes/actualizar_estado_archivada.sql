-- Script para actualizar la tabla de reservas y añadir el estado 'archivada'
-- Fecha: 14/05/2025

-- Modificar la tabla de reservas para añadir el estado 'archivada'
ALTER TABLE reservas 
MODIFY COLUMN estado ENUM('pendiente', 'confirmada', 'rechazada', 'cancelada', 'archivada') DEFAULT 'pendiente';

-- Actualizar el script de configuración de la base de datos
-- Nota: Este cambio debe aplicarse también al archivo setup_database.sql
