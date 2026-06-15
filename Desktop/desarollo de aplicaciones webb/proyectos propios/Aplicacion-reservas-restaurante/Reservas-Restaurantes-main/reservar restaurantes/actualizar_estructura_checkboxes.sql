-- Añadir campos a la tabla checkboxes_personalizados si no existen
ALTER TABLE checkboxes_personalizados 
ADD COLUMN IF NOT EXISTS es_obligatorio TINYINT(1) NOT NULL DEFAULT 0 AFTER activo,
ADD COLUMN IF NOT EXISTS tiene_textarea TINYINT(1) NOT NULL DEFAULT 0 AFTER es_obligatorio,
ADD COLUMN IF NOT EXISTS placeholder_textarea VARCHAR(255) DEFAULT NULL AFTER tiene_textarea;

-- Añadir campo para almacenar el texto del textarea en la tabla de relación
ALTER TABLE reservas_checkboxes 
ADD COLUMN IF NOT EXISTS texto_respuesta TEXT DEFAULT NULL AFTER valor;
