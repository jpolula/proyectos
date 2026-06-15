<?php
// Script para modificar el control de aforo en el sistema de reservas
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Modificar control de aforo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #333;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Modificar control de aforo</h1>";

// 1. Verificar el archivo reserva.php
echo "<h2>1. Verificando reserva.php</h2>";

$archivo_reserva = 'reserva.php';
if (!file_exists($archivo_reserva)) {
    echo "<div class='error'>❌ No se encontró el archivo $archivo_reserva</div>";
} else {
    echo "<div class='success'>✅ El archivo $archivo_reserva existe</div>";
    
    // Crear una copia de seguridad
    if (copy($archivo_reserva, $archivo_reserva . '.bak.aforo')) {
        echo "<div class='info'>✓ Se ha creado una copia de seguridad en $archivo_reserva.bak.aforo</div>";
    } else {
        echo "<div class='error'>❌ No se pudo crear una copia de seguridad del archivo</div>";
    }
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($archivo_reserva);
    
    // Verificar si existe la sección de verificación de aforo
    if (strpos($contenido, 'Calcular si hay suficiente aforo disponible') !== false) {
        echo "<div class='info'>✓ Se encontró la sección de verificación de aforo</div>";
        
        // Buscar y reemplazar la sección de verificación de aforo
        $patron_aforo = "/\/\/ Calcular si hay suficiente aforo disponible.*?if \(\\\$aforo_disponible < \\\$num_personas\) \{.*?\}/s";
        if (preg_match($patron_aforo, $contenido, $coincidencias)) {
            $codigo_original = $coincidencias[0];
            
            // Nuevo código que respeta estrictamente el límite de aforo
            $codigo_nuevo = "// Calcular si hay suficiente aforo disponible
                        \$num_personas = isset(\$_SESSION['num_personas']) ? (int)\$_SESSION['num_personas'] : 0;
                        \$aforo_disponible = \$aforo_maximo - \$total_reservado;
                        
                        if (\$aforo_disponible < \$num_personas) {
                            \$errores['disponibilidad'] = \"Lo sentimos, no hay suficiente aforo disponible para {\$num_personas} personas en la zona y turno seleccionados. Aforo disponible: {\$aforo_disponible} personas.\";
                            error_log(\"Aforo insuficiente: \$fecha, turno_id: \$turno_id, zona: \$zona, aforo_disponible: \$aforo_disponible, personas_solicitadas: \$num_personas\");
                        }";
            
            // Reemplazar el código
            $contenido = str_replace($codigo_original, $codigo_nuevo, $contenido);
            echo "<div class='success'>✅ Se ha modificado la sección de verificación de aforo para respetar estrictamente el límite de aforo</div>";
        } else {
            echo "<div class='warning'>⚠️ No se pudo encontrar la sección exacta de verificación de aforo</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ No se encontró la sección de verificación de aforo</div>";
    }
    
    // Guardar los cambios
    if (file_put_contents($archivo_reserva, $contenido)) {
        echo "<div class='success'>✅ Se han guardado los cambios en $archivo_reserva</div>";
    } else {
        echo "<div class='error'>❌ No se pudieron guardar los cambios en $archivo_reserva</div>";
    }
}

// 2. Verificar el archivo verificar_aforo.php
echo "<h2>2. Verificando verificar_aforo.php</h2>";

$archivo_aforo = 'verificar_aforo.php';
if (!file_exists($archivo_aforo)) {
    echo "<div class='error'>❌ No se encontró el archivo $archivo_aforo</div>";
} else {
    echo "<div class='success'>✅ El archivo $archivo_aforo existe</div>";
    
    // Crear una copia de seguridad
    if (copy($archivo_aforo, $archivo_aforo . '.bak')) {
        echo "<div class='info'>✓ Se ha creado una copia de seguridad en $archivo_aforo.bak</div>";
    } else {
        echo "<div class='error'>❌ No se pudo crear una copia de seguridad del archivo</div>";
    }
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($archivo_aforo);
    
    // Verificar si existe la sección de verificación de aforo
    if (strpos($contenido, 'Calcular si hay suficiente aforo disponible') !== false) {
        echo "<div class='info'>✓ Se encontró la sección de verificación de aforo</div>";
        
        // Buscar y reemplazar la sección de verificación de aforo
        $patron_aforo = "/\/\/ Calcular si hay suficiente aforo disponible.*?\\\$hay_disponibilidad = \\\$aforo_disponible >= \\\$num_personas;/s";
        if (preg_match($patron_aforo, $contenido, $coincidencias)) {
            $codigo_original = $coincidencias[0];
            
            // Nuevo código que respeta estrictamente el límite de aforo
            $codigo_nuevo = "// Calcular si hay suficiente aforo disponible
    \$aforo_disponible = \$aforo_maximo - \$total_reservado;
    \$hay_disponibilidad = \$aforo_disponible >= \$num_personas;";
            
            // Reemplazar el código
            $contenido = str_replace($codigo_original, $codigo_nuevo, $contenido);
            echo "<div class='success'>✅ Se ha modificado la sección de verificación de aforo en verificar_aforo.php</div>";
        } else {
            echo "<div class='warning'>⚠️ No se pudo encontrar la sección exacta de verificación de aforo en verificar_aforo.php</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ No se encontró la sección de verificación de aforo en verificar_aforo.php</div>";
    }
    
    // Modificar el mensaje de error para que sea más informativo
    $patron_mensaje = "/\\\$mensaje = \\\$hay_disponibilidad.*?:/s";
    if (preg_match($patron_mensaje, $contenido, $coincidencias)) {
        $codigo_original = $coincidencias[0];
        
        // Nuevo código con mensaje más informativo
        $codigo_nuevo = "\$mensaje = \$hay_disponibilidad 
            ? 'Hay disponibilidad para ' . \$num_personas . ' personas' 
            : 'No hay suficiente aforo disponible para ' . \$num_personas . ' personas. Aforo disponible: ' . \$aforo_disponible . ' personas'";
        
        // Reemplazar el código
        $contenido = str_replace($codigo_original, $codigo_nuevo, $contenido);
        echo "<div class='success'>✅ Se ha mejorado el mensaje de error en verificar_aforo.php</div>";
    } else {
        echo "<div class='warning'>⚠️ No se pudo encontrar la sección del mensaje de error en verificar_aforo.php</div>";
    }
    
    // Guardar los cambios
    if (file_put_contents($archivo_aforo, $contenido)) {
        echo "<div class='success'>✅ Se han guardado los cambios en $archivo_aforo</div>";
    } else {
        echo "<div class='error'>❌ No se pudieron guardar los cambios en $archivo_aforo</div>";
    }
}

// 3. Crear un script para probar el control de aforo
echo "<h2>3. Creando script de prueba</h2>";

$archivo_prueba = 'test_aforo_estricto.php';
$contenido_prueba = '<?php
// Script para probar el control estricto de aforo
ini_set(\'display_errors\', 1);
ini_set(\'display_startup_errors\', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
$host = \'localhost\';
$db = \'restaurante_reservas\';
$user = \'root\';
$pass = \'\';
$charset = \'utf8mb4\';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Función para verificar disponibilidad
function verificarDisponibilidad($fecha, $zona, $turno_id, $num_personas) {
    global $pdo;
    
    try {
        // Verificar si hay bloqueos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bloqueos WHERE fecha = ? AND zona = ? AND turno_id = ?");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $bloqueado = ($stmt->fetchColumn() > 0);
        
        if ($bloqueado) {
            return [
                \'disponible\' => false,
                \'mensaje\' => \'La fecha seleccionada está bloqueada para reservas\'
            ];
        }
        
        // Obtener la capacidad máxima para esa fecha, zona y turno
        $stmt = $pdo->prepare("SELECT aforo_maximo FROM capacidad WHERE fecha = ? AND zona = ? AND turno_id = ?");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $aforo_maximo = $stmt->fetchColumn();
        
        // Si no hay configuración específica, obtener la capacidad por defecto
        if ($aforo_maximo === false) {
            $campo_capacidad = \'capacidad_\' . $zona . \'_\' . $turno_id;
            $stmt = $pdo->prepare("SELECT " . $campo_capacidad . " FROM configuracion WHERE id = 1");
            $stmt->execute();
            $aforo_maximo = $stmt->fetchColumn();
            
            // Si aún no hay valor, usar un valor por defecto
            if ($aforo_maximo === false) {
                $aforo_maximo = ($zona == \'dentro\') ? 30 : 20;
            }
        }
        
        // Obtener el número de personas ya reservadas
        $stmt = $pdo->prepare("SELECT SUM(cantidad_personas) as total_reservado FROM reservas WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = \'confirmada\'");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $resultado = $stmt->fetch();
        $total_reservado = $resultado[\'total_reservado\'] ?: 0;
        
        // Calcular si hay suficiente aforo disponible
        $aforo_disponible = $aforo_maximo - $total_reservado;
        $hay_disponibilidad = $aforo_disponible >= $num_personas;
        
        return [
            \'disponible\' => $hay_disponibilidad,
            \'mensaje\' => $hay_disponibilidad 
                ? "Hay disponibilidad para $num_personas personas" 
                : "No hay suficiente aforo disponible para $num_personas personas. Aforo disponible: $aforo_disponible personas",
            \'aforo_maximo\' => $aforo_maximo,
            \'aforo_ocupado\' => $total_reservado,
            \'aforo_disponible\' => $aforo_disponible
        ];
    } catch (PDOException $e) {
        return [
            \'disponible\' => false,
            \'mensaje\' => \'Error al verificar disponibilidad: \' . $e->getMessage(),
            \'error\' => $e->getMessage()
        ];
    }
}

// Procesar el formulario si se envió
$resultado = null;
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && !empty($_POST[\'fecha\']) && !empty($_POST[\'zona\']) && !empty($_POST[\'turno_id\']) && !empty($_POST[\'num_personas\'])) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        $fecha = $_POST[\'fecha\'];
        $zona = $_POST[\'zona\'];
        $turno_id = $_POST[\'turno_id\'];
        $num_personas = (int)$_POST[\'num_personas\'];
        
        $resultado = verificarDisponibilidad($fecha, $zona, $turno_id, $num_personas);
    } catch (PDOException $e) {
        $resultado = [
            \'disponible\' => false,
            \'mensaje\' => \'Error de conexión a la base de datos: \' . $e->getMessage(),
            \'error\' => $e->getMessage()
        ];
    }
}

// Obtener turnos disponibles
$turnos = [];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
    $turnos = $stmt->fetchAll();
} catch (PDOException $e) {
    // Usar valores por defecto si hay error
    $turnos = [
        [\'id\' => 1, \'nombre\' => \'mediodia\'],
        [\'id\' => 2, \'nombre\' => \'noche\']
    ];
}

// Obtener configuración actual
$configuracion = [];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT capacidad_dentro_1, capacidad_fuera_1, capacidad_dentro_2, capacidad_fuera_2 FROM configuracion WHERE id = 1");
    $configuracion = $stmt->fetch();
} catch (PDOException $e) {
    // Ignorar error
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Prueba de control estricto de aforo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Prueba de control estricto de aforo</h1>
            
            <?php if ($configuracion): ?>
            <div class="mb-6 p-4 bg-blue-100 text-blue-800 rounded-md">
                <h2 class="text-lg font-semibold mb-2">Configuración actual de aforo:</h2>
                <ul class="list-disc pl-5">
                    <li>Interior (mediodía): <?php echo $configuracion[\'capacidad_dentro_1\']; ?> personas</li>
                    <li>Terraza (mediodía): <?php echo $configuracion[\'capacidad_fuera_1\']; ?> personas</li>
                    <li>Interior (noche): <?php echo $configuracion[\'capacidad_dentro_2\']; ?> personas</li>
                    <li>Terraza (noche): <?php echo $configuracion[\'capacidad_fuera_2\']; ?> personas</li>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if ($resultado !== null): ?>
                <div class="mb-6 p-4 rounded-md <?php echo $resultado[\'disponible\'] ? \'bg-green-100 text-green-800\' : \'bg-red-100 text-red-800\'; ?>">
                    <p class="font-medium"><?php echo $resultado[\'mensaje\']; ?></p>
                </div>
                
                <?php if (isset($resultado[\'aforo_maximo\'])): ?>
                <div class="mt-4 p-4 bg-gray-100 rounded">
                    <h3 class="text-lg font-semibold mb-2">Detalles del aforo:</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Aforo máximo: <?php echo $resultado[\'aforo_maximo\']; ?> personas</li>
                        <li>Aforo ocupado: <?php echo $resultado[\'aforo_ocupado\']; ?> personas</li>
                        <li>Aforo disponible: <?php echo $resultado[\'aforo_disponible\']; ?> personas</li>
                        <li>Personas solicitadas: <?php echo $num_personas; ?> personas</li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (isset($resultado[\'error\'])): ?>
                <div class="mt-4 p-4 bg-yellow-100 text-yellow-800 rounded">
                    <h3 class="text-lg font-semibold mb-2">Detalles del error:</h3>
                    <pre class="text-sm bg-gray-800 text-white p-3 rounded overflow-x-auto"><?php echo $resultado[\'error\']; ?></pre>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <form method="post" action="" class="space-y-6">
                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha (YYYY-MM-DD)</label>
                    <input type="date" id="fecha" name="fecha" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="turno_id" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                    <select id="turno_id" name="turno_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selecciona un turno</option>
                        <?php foreach ($turnos as $turno): ?>
                            <option value="<?php echo $turno[\'id\']; ?>"><?php echo ucfirst($turno[\'nombre\']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="zona" class="block text-sm font-medium text-gray-700 mb-1">Zona</label>
                    <select id="zona" name="zona" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Selecciona una zona</option>
                        <option value="dentro">Interior</option>
                        <option value="fuera">Terraza</option>
                    </select>
                </div>
                
                <div>
                    <label for="num_personas" class="block text-sm font-medium text-gray-700 mb-1">Número de personas</label>
                    <input type="number" id="num_personas" name="num_personas" min="1" max="100" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Verificar disponibilidad
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <a href="index.php" class="text-indigo-600 hover:text-indigo-500">Volver al inicio</a>
            </div>
        </div>
    </div>
</body>
</html>';

if (file_put_contents($archivo_prueba, $contenido_prueba)) {
    echo "<div class='success'>✅ Se ha creado el archivo de prueba $archivo_prueba</div>";
} else {
    echo "<div class='error'>❌ No se pudo crear el archivo de prueba $archivo_prueba</div>";
}

// Resumen y próximos pasos
echo "<h2>Resumen</h2>";
echo "<div class='success'>
    <p>✅ Se han realizado las siguientes modificaciones:</p>
    <ol>
        <li>Modificado el código en reserva.php para que respete estrictamente el límite de aforo</li>
        <li>Modificado el código en verificar_aforo.php para que respete estrictamente el límite de aforo</li>
        <li>Mejorado los mensajes de error para que sean más informativos</li>
        <li>Creado un script de prueba para verificar el control estricto de aforo</li>
    </ol>
</div>";

echo "<h2>Próximos pasos</h2>";
echo "<div class='info'>
    <p>Para probar el control estricto de aforo:</p>
    <ol>
        <li>Utiliza el archivo de prueba <a href='test_aforo_estricto.php'>test_aforo_estricto.php</a> para verificar que el aforo se controla correctamente</li>
        <li>Luego, prueba el proceso de reserva completo desde la página principal</li>
        <li>Verifica que no se permitan reservas que excedan el aforo disponible</li>
    </ol>
</div>";

echo "<p><a href='index.php' class='btn'>Volver al inicio</a> <a href='test_aforo_estricto.php' class='btn' style='background-color:#007bff;'>Probar control de aforo</a></p>
</body>
</html>";
?>
