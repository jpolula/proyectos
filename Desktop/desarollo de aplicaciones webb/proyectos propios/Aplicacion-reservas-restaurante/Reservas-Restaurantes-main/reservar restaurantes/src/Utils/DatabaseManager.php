<?php
namespace App\Utils;

use App\Config\Database;
use PDO;
use PDOException;

class DatabaseManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Ejecuta el script SQL para crear la base de datos
     * 
     * @param string $scriptPath Ruta al archivo SQL
     * @return array Resultado de la operación
     */
    public function executeScript($scriptPath) {
        $result = [
            'success' => false,
            'message' => '',
            'errors' => []
        ];
        
        if (!file_exists($scriptPath)) {
            $result['message'] = 'El archivo de script SQL no existe';
            return $result;
        }
        
        try {
            // Leer el contenido del script
            $sql = file_get_contents($scriptPath);
            
            // Dividir el script en consultas individuales
            $queries = $this->splitSqlQueries($sql);
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            foreach ($queries as $query) {
                if (trim($query) !== '') {
                    $this->db->exec($query);
                }
            }
            
            // Confirmar transacción
            $this->db->commit();
            
            $result['success'] = true;
            $result['message'] = 'Script SQL ejecutado correctamente';
            
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            $this->db->rollBack();
            
            $result['message'] = 'Error al ejecutar el script SQL';
            $result['errors'][] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Divide un script SQL en consultas individuales
     * 
     * @param string $sql Contenido del script SQL
     * @return array Array de consultas individuales
     */
    private function splitSqlQueries($sql) {
        // Eliminar comentarios
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Manejar delimitadores personalizados (para procedimientos almacenados)
        $pattern = '/DELIMITER\s+([^\s]+)\s+/i';
        preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE);
        
        if (!empty($matches[0])) {
            $queries = [];
            $lastPos = 0;
            $delimiter = ';';
            
            foreach ($matches[0] as $index => $match) {
                $pos = $match[1];
                $length = strlen($match[0]);
                $newDelimiter = $matches[1][$index][0];
                
                // Extraer consultas hasta este DELIMITER
                $segment = substr($sql, $lastPos, $pos - $lastPos);
                $segmentQueries = explode($delimiter, $segment);
                foreach ($segmentQueries as $query) {
                    if (trim($query) !== '') {
                        $queries[] = trim($query);
                    }
                }
                
                // Actualizar posición y delimitador
                $lastPos = $pos + $length;
                $delimiter = $newDelimiter;
                
                // Buscar el próximo DELIMITER o el final del script
                $nextPos = isset($matches[0][$index + 1]) ? $matches[0][$index + 1][1] : strlen($sql);
                
                // Extraer consultas con el nuevo delimitador
                $segment = substr($sql, $lastPos, $nextPos - $lastPos);
                $parts = explode($delimiter, $segment);
                
                // El último elemento puede estar incompleto si hay otro DELIMITER
                $lastPart = array_pop($parts);
                
                foreach ($parts as $part) {
                    if (trim($part) !== '') {
                        $queries[] = trim($part);
                    }
                }
                
                // Restaurar el delimitador predeterminado
                $delimiter = ';';
                $lastPos = $nextPos;
                
                // Añadir la última parte si es el final del script
                if ($index == count($matches[0]) - 1) {
                    if (trim($lastPart) !== '') {
                        $queries[] = trim($lastPart);
                    }
                }
            }
            
            // Procesar el resto del script si queda algo
            if ($lastPos < strlen($sql)) {
                $segment = substr($sql, $lastPos);
                $segmentQueries = explode(';', $segment);
                foreach ($segmentQueries as $query) {
                    if (trim($query) !== '') {
                        $queries[] = trim($query);
                    }
                }
            }
            
            return $queries;
        } else {
            // Si no hay DELIMITER personalizados, simplemente dividir por punto y coma
            $queries = explode(';', $sql);
            $result = [];
            
            foreach ($queries as $query) {
                if (trim($query) !== '') {
                    $result[] = trim($query);
                }
            }
            
            return $result;
        }
    }
    
    /**
     * Verifica si una tabla existe en la base de datos
     * 
     * @param string $tableName Nombre de la tabla
     * @return bool True si la tabla existe, false en caso contrario
     */
    public function tableExists($tableName) {
        try {
            $result = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Obtiene el nombre de la base de datos actual
     * 
     * @return string Nombre de la base de datos
     */
    public function getDatabaseName() {
        try {
            $stmt = $this->db->query('SELECT DATABASE()');
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return '';
        }
    }
}
