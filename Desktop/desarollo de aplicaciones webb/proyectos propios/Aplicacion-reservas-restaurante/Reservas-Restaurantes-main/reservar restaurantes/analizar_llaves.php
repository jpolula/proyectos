<?php
// Script para analizar el equilibrio de llaves en confirmar_reserva.php

// Leer el archivo original
$contenido = file_get_contents('confirmar_reserva.php');

// Hacer una copia de seguridad
$backup_file = 'confirmar_reserva_backup_' . date('Y-m-d_H-i-s') . '.php';
file_put_contents($backup_file, $contenido);
echo "Se ha creado una copia de seguridad en $backup_file<br>";

// Analizar el equilibrio de llaves
$lineas = explode("\n", $contenido);
$pila_llaves = [];
$problemas = [];

foreach ($lineas as $num_linea => $linea) {
    $num_linea++; // Ajustar para que empiece en 1 en lugar de 0
    
    // Contar llaves de apertura
    $apertura_count = substr_count($linea, '{');
    for ($i = 0; $i < $apertura_count; $i++) {
        array_push($pila_llaves, $num_linea);
    }
    
    // Contar llaves de cierre
    $cierre_count = substr_count($linea, '}');
    for ($i = 0; $i < $cierre_count; $i++) {
        if (count($pila_llaves) > 0) {
            array_pop($pila_llaves);
        } else {
            $problemas[] = "Llave de cierre sin apertura correspondiente en línea $num_linea";
        }
    }
}

// Verificar si quedaron llaves sin cerrar
foreach ($pila_llaves as $linea) {
    $problemas[] = "Llave de apertura sin cierre correspondiente en línea $linea";
}

// Mostrar resultados del análisis
echo "<h2>Análisis de equilibrio de llaves</h2>";

if (count($problemas) > 0) {
    echo "<p>Se encontraron los siguientes problemas:</p>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    
    // Intentar corregir el problema específico de la línea 286
    if (isset($lineas[285]) && strpos($lineas[285], 'if ($fechaObj) {') !== false) {
        echo "<p>Intentando corregir el problema en la línea 286...</p>";
        
        // Buscar dónde debería cerrarse esta llave
        $encontrado = false;
        for ($i = 286; $i < count($lineas); $i++) {
            if (isset($lineas[$i]) && strpos($lineas[$i], '// Fin del if de fechaObj') !== false) {
                $encontrado = true;
                if (strpos($lineas[$i], '}') === false) {
                    $lineas[$i] = '} ' . $lineas[$i]; // Añadir llave de cierre
                    echo "<p>Se añadió una llave de cierre en la línea " . ($i + 1) . "</p>";
                }
                break;
            }
        }
        
        if (!$encontrado) {
            // Buscar el final del bloque de código
            for ($i = 800; $i < 820; $i++) {
                if (isset($lineas[$i]) && strpos($lineas[$i], '?>') !== false) {
                    // Añadir llave de cierre justo antes del cierre de PHP
                    $lineas[$i] = "} // Cierre añadido para el if de la línea 286\n" . $lineas[$i];
                    $encontrado = true;
                    echo "<p>Se añadió una llave de cierre antes del cierre de PHP en la línea " . ($i + 1) . "</p>";
                    break;
                }
            }
        }
        
        if (!$encontrado) {
            // Si no encontramos un lugar adecuado, añadir al final del archivo
            $lineas[] = "} // Cierre añadido para el if de la línea 286";
            echo "<p>Se añadió una llave de cierre al final del archivo</p>";
        }
        
        // Guardar el archivo corregido
        $contenido_corregido = implode("\n", $lineas);
        file_put_contents('confirmar_reserva.php', $contenido_corregido);
        
        echo "<p><strong>Se ha intentado corregir el problema. Por favor, verifica si el error ha sido solucionado.</strong></p>";
    } else {
        echo "<p>No se pudo identificar el patrón esperado en la línea 286.</p>";
    }
} else {
    echo "<p>No se encontraron problemas de equilibrio de llaves.</p>";
}

echo "<p><a href='confirmar_reserva.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Probar confirmar_reserva.php</a></p>";
?>
