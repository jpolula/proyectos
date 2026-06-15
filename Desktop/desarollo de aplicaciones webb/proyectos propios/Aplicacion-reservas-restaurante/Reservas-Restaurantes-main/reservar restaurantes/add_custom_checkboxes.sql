-- Script para añadir la tabla de checkboxes personalizados
USE restaurante_reservas;

-- Tabla para los checkboxes personalizados
CREATE TABLE IF NOT EXISTS checkboxes_personalizados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto VARCHAR(255) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    orden INT NOT NULL DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para almacenar las respuestas de los checkboxes en las reservas
CREATE TABLE IF NOT EXISTS reservas_checkboxes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    checkbox_id INT NOT NULL,
    valor BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (checkbox_id) REFERENCES checkboxes_personalizados(id) ON DELETE CASCADE,
    UNIQUE(reserva_id, checkbox_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar algunos checkboxes de ejemplo
INSERT INTO checkboxes_personalizados (texto, descripcion, activo, orden) VALUES 
('Acepto la política de privacidad', 'El cliente acepta nuestra política de privacidad y tratamiento de datos', TRUE, 1),
('Deseo recibir ofertas y promociones', 'El cliente desea recibir información sobre ofertas y promociones', TRUE, 2);
