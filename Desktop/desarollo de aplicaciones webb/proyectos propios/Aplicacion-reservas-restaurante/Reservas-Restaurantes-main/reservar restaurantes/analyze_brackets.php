<?php
/**
 * Script para analizar la estructura de llaves en reservas.php
 * Este script identifica exactamente dónde hay llaves no cerradas
 */

// Ruta al archivo
$archivo = __DIR__ . '/admin/reservas.php';

// Verificar que el archivo existe
if (!file_exists($archivo)) {
    die("Error: No se encontró el archivo reservas.php\n");
}

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);
if ($contenido === false) {
    die("Error: No se pudo leer el contenido del archivo\n");
}

// Leer el archivo línea por línea
$lineas = file($archivo);
if ($lineas === false) {
    die("Error: No se pudo leer el archivo línea por línea\n");
}

// Analizar la estructura de llaves
$pila_llaves = [];
$nivel_actual = 0;
$errores = [];

foreach ($lineas as $num_linea => $linea) {
    $num_linea++; // Ajustar para que empiece en 1 en lugar de 0
    
    // Contar llaves de apertura
    $llaves_apertura = substr_count($linea, '{');
    // Contar llaves de cierre
    $llaves_cierre = substr_count($linea, '}');
    
    // Actualizar nivel
    $nivel_anterior = $nivel_actual;
    $nivel_actual += $llaves_apertura - $llaves_cierre;
    
    // Registrar cada llave de apertura
    for ($i = 0; $i < $llaves_apertura; $i++) {
        array_push($pila_llaves, $num_linea);
    }
    
    // Registrar cada llave de cierre
    for ($i = 0; $i < $llaves_cierre; $i++) {
        if (count($pila_llaves) > 0) {
            $linea_apertura = array_pop($pila_llaves);
        } else {
            // Llave de cierre sin apertura correspondiente
            $errores[] = "Llave de cierre sin apertura correspondiente en la línea $num_linea";
        }
    }
    
    // Mostrar información de depuración
    if ($llaves_apertura > 0 || $llaves_cierre > 0) {
        echo "Línea $num_linea: " . trim($linea) . "\n";
        echo "  Apertura: $llaves_apertura, Cierre: $llaves_cierre, Nivel: $nivel_actual\n";
    }
}

// Verificar si quedaron llaves sin cerrar
if (count($pila_llaves) > 0) {
    echo "\nLlaves sin cerrar:\n";
    foreach ($pila_llaves as $linea) {
        echo "- Llave abierta en la línea $linea sin cerrar\n";
        $errores[] = "Llave abierta en la línea $linea sin cerrar";
    }
}

// Mostrar errores
if (count($errores) > 0) {
    echo "\nErrores encontrados:\n";
    foreach ($errores as $error) {
        echo "- $error\n";
    }
} else {
    echo "\nNo se encontraron errores en la estructura de llaves\n";
}

// Sugerencias para corregir el problema
echo "\nSugerencias para corregir el problema:\n";
echo "1. Revise las líneas identificadas con llaves sin cerrar\n";
echo "2. Verifique la indentación del código para identificar bloques mal cerrados\n";
echo "3. Preste especial atención a las estructuras if-elseif-else y a los bloques try-catch\n";

// Mostrar las líneas alrededor de la línea 37 (mencionada en el error)
echo "\nAnálisis de la línea 37 y alrededores:\n";
$inicio = max(1, 37 - 5);
$fin = min(count($lineas), 37 + 5);

for ($i = $inicio - 1; $i < $fin; $i++) {
    $num = $i + 1;
    $linea = $lineas[$i];
    $marcador = ($num == 37) ? " <-- AQUÍ" : "";
    echo "$num: " . rtrim($linea) . "$marcador\n";
}
