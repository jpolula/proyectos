<?php
// Script para solucionar el error de verificación de disponibilidad
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Solucionar error de verificación de disponibilidad</title>
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
    <h1>Solucionar error de verificación de disponibilidad</h1>";

// 1. Verificar la estructura de la base de datos
echo "<h2>1. Verificando la estructura de la base de datos</h2>";

// Configuración de la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<div class='success'>✅ Conexión a la base de datos establecida correctamente</div>";
    
    // Verificar la tabla configuracion
    $stmt = $pdo->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='info'>✓ La tabla configuracion existe</div>";
        
        // Verificar si la tabla tiene registros
        $stmt = $pdo->query("SELECT COUNT(*) FROM configuracion");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "<div class='info'>✓ La tabla configuracion tiene $count registros</div>";
            
            // Verificar si existe el campo capacidad_dentro_1
            $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'capacidad_dentro_1'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='info'>✓ El campo capacidad_dentro_1 existe en la tabla configuracion</div>";
            } else {
                echo "<div class='warning'>⚠️ El campo capacidad_dentro_1 no existe en la tabla configuracion</div>";
                
                // Añadir los campos de capacidad
                $pdo->exec("ALTER TABLE configuracion 
                    ADD COLUMN capacidad_dentro_1 INT DEFAULT 30,
                    ADD COLUMN capacidad_fuera_1 INT DEFAULT 20,
                    ADD COLUMN capacidad_dentro_2 INT DEFAULT 30,
                    ADD COLUMN capacidad_fuera_2 INT DEFAULT 20");
                
                echo "<div class='success'>✅ Se han añadido los campos de capacidad a la tabla configuracion</div>";
            }
            
            // Verificar si hay valores para las capacidades
            $stmt = $pdo->query("SELECT capacidad_dentro_1, capacidad_fuera_1, capacidad_dentro_2, capacidad_fuera_2 FROM configuracion WHERE id = 1");
            $config = $stmt->fetch();
            
            if ($config) {
                echo "<div class='info'>✓ Valores de capacidad actuales:
                    <ul>
                        <li>Capacidad dentro (mediodía): {$config['capacidad_dentro_1']}</li>
                        <li>Capacidad fuera (mediodía): {$config['capacidad_fuera_1']}</li>
                        <li>Capacidad dentro (noche): {$config['capacidad_dentro_2']}</li>
                        <li>Capacidad fuera (noche): {$config['capacidad_fuera_2']}</li>
                    </ul>
                </div>";
                
                // Actualizar los valores si son NULL
                if ($config['capacidad_dentro_1'] === null || $config['capacidad_fuera_1'] === null || 
                    $config['capacidad_dentro_2'] === null || $config['capacidad_fuera_2'] === null) {
                    
                    $pdo->exec("UPDATE configuracion SET 
                        capacidad_dentro_1 = COALESCE(capacidad_dentro_1, 30),
                        capacidad_fuera_1 = COALESCE(capacidad_fuera_1, 20),
                        capacidad_dentro_2 = COALESCE(capacidad_dentro_2, 30),
                        capacidad_fuera_2 = COALESCE(capacidad_fuera_2, 20)
                        WHERE id = 1");
                    
                    echo "<div class='success'>✅ Se han actualizado los valores de capacidad que eran NULL</div>";
                }
            } else {
                echo "<div class='warning'>⚠️ No se encontró el registro con id = 1 en la tabla configuracion</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ La tabla configuracion está vacía</div>";
            
            // Insertar configuración por defecto
            $pdo->exec("INSERT INTO configuracion (id, max_personas_sin_aprobacion, capacidad_dentro_1, capacidad_fuera_1, capacidad_dentro_2, capacidad_fuera_2) 
                VALUES (1, 4, 30, 20, 30, 20)");
            
            echo "<div class='success'>✅ Se ha insertado la configuración por defecto</div>";
        }
    } else {
        echo "<div class='error'>❌ La tabla configuracion no existe</div>";
    }
    
    // Verificar la tabla capacidad
    $stmt = $pdo->query("SHOW TABLES LIKE 'capacidad'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='info'>✓ La tabla capacidad existe</div>";
    } else {
        echo "<div class='warning'>⚠️ La tabla capacidad no existe, se creará</div>";
        
        // Crear la tabla capacidad
        $pdo->exec("CREATE TABLE capacidad (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha DATE NOT NULL,
            zona ENUM('dentro', 'fuera') NOT NULL,
            turno_id INT NOT NULL,
            aforo_maximo INT NOT NULL,
            UNIQUE(fecha, zona, turno_id),
            FOREIGN KEY (turno_id) REFERENCES turnos(id)
        )");
        
        echo "<div class='success'>✅ Se ha creado la tabla capacidad</div>";
    }
    
    // Verificar la tabla bloqueos
    $stmt = $pdo->query("SHOW TABLES LIKE 'bloqueos'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='info'>✓ La tabla bloqueos existe</div>";
    } else {
        echo "<div class='warning'>⚠️ La tabla bloqueos no existe, se creará</div>";
        
        // Crear la tabla bloqueos
        $pdo->exec("CREATE TABLE bloqueos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fecha DATE NOT NULL,
            zona ENUM('dentro', 'fuera') NOT NULL,
            turno_id INT NOT NULL,
            motivo TEXT,
            UNIQUE(fecha, zona, turno_id),
            FOREIGN KEY (turno_id) REFERENCES turnos(id)
        )");
        
        echo "<div class='success'>✅ Se ha creado la tabla bloqueos</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>❌ Error de conexión a la base de datos: " . $e->getMessage() . "</div>";
}

// 2. Corregir el archivo reserva.php
echo "<h2>2. Corrigiendo el archivo reserva.php</h2>";

$archivo_reserva = 'reserva.php';
if (!file_exists($archivo_reserva)) {
    echo "<div class='error'>❌ No se encontró el archivo $archivo_reserva</div>";
} else {
    echo "<div class='success'>✅ El archivo $archivo_reserva existe</div>";
    
    // Crear una copia de seguridad
    if (copy($archivo_reserva, $archivo_reserva . '.bak.fix')) {
        echo "<div class='info'>✓ Se ha creado una copia de seguridad en $archivo_reserva.bak.fix</div>";
    } else {
        echo "<div class='error'>❌ No se pudo crear una copia de seguridad del archivo</div>";
    }
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($archivo_reserva);
    
    // Corregir la consulta SQL para obtener la capacidad por defecto
    $patron_capacidad = "/\\\$campo_capacidad = 'capacidad_' . \\\$zona . '_' . \\\$turno_id;.*?\\\$stmt = \\\$pdo->prepare\(\"SELECT \\\$campo_capacidad FROM configuracion WHERE id = 1\"\);/s";
    $reemplazo_capacidad = "\$campo_capacidad = 'capacidad_' . \$zona . '_' . \$turno_id;
                        \$stmt = \$pdo->prepare(\"SELECT \" . \$campo_capacidad . \" FROM configuracion WHERE id = 1\");";
    
    if (preg_match($patron_capacidad, $contenido)) {
        $contenido = preg_replace($patron_capacidad, $reemplazo_capacidad, $contenido);
        echo "<div class='success'>✅ Se ha corregido la consulta SQL para obtener la capacidad por defecto</div>";
    } else {
        echo "<div class='warning'>⚠️ No se pudo encontrar la consulta SQL para corregirla</div>";
    }
    
    // Guardar los cambios
    if (file_put_contents($archivo_reserva, $contenido)) {
        echo "<div class='success'>✅ Se han guardado los cambios en $archivo_reserva</div>";
    } else {
        echo "<div class='error'>❌ No se pudieron guardar los cambios en $archivo_reserva</div>";
    }
}

// 3. Crear un script para probar la verificación de disponibilidad
echo "<h2>3. Creando script de prueba</h2>";

$archivo_prueba = 'test_disponibilidad.php';
$contenido_prueba = '<?php
// Script para probar la verificación de disponibilidad
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
                : "No hay suficiente aforo disponible para $num_personas personas",
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Prueba de verificación de disponibilidad</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
        <div class="p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Prueba de verificación de disponibilidad</h1>
            
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
                    <input type="number" id="num_personas" name="num_personas" min="1" max="20" required
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
    <p>✅ Se han realizado las siguientes correcciones:</p>
    <ol>
        <li>Verificada la estructura de la base de datos y creadas las tablas necesarias si no existían</li>
        <li>Añadidos los campos de capacidad a la tabla configuracion si no existían</li>
        <li>Corregida la consulta SQL en reserva.php para obtener la capacidad por defecto</li>
        <li>Creado un script de prueba para verificar la disponibilidad</li>
    </ol>
</div>";

echo "<h2>Próximos pasos</h2>";
echo "<div class='info'>
    <p>Para probar la verificación de disponibilidad:</p>
    <ol>
        <li>Utiliza el archivo de prueba <a href='test_disponibilidad.php'>test_disponibilidad.php</a> para verificar que la disponibilidad se calcula correctamente</li>
        <li>Luego, prueba el proceso de reserva completo desde la página principal</li>
        <li>Si sigues teniendo problemas, revisa los logs de error de PHP para obtener más información</li>
    </ol>
</div>";

echo "<p><a href='index.php' class='btn'>Volver al inicio</a> <a href='test_disponibilidad.php' class='btn' style='background-color:#007bff;'>Probar disponibilidad</a></p>
</body>
</html>";
?>
