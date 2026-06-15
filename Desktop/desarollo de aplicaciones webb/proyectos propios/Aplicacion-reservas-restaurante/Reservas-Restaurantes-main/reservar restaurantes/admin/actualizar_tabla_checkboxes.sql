-- Añadir columna para almacenar el texto de respuesta de los checkboxes
ALTER TABLE reservas_checkboxes 
ADD COLUMN texto_respuesta TEXT NULL DEFAULT NULL 
COMMENT 'Almacena el texto introducido en el textarea asociado al checkbox' 
AFTER valor;
