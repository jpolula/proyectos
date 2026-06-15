<?php
// Script para implementar la visualización de avisos de administración en confirmar_reserva.php
// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Archivo a modificar
$archivo = 'confirmar_reserva.php';

// Verificar si el archivo existe
if (!file_exists($archivo)) {
    die("El archivo $archivo no existe.");
}

// Crear la tabla de avisos si no existe
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'avisos_reserva'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    if (!$tabla_existe) {
        // Crear la tabla de avisos
        $sql = "CREATE TABLE avisos_reserva (
            id INT AUTO_INCREMENT PRIMARY KEY,
            texto TEXT NOT NULL,
            activo BOOLEAN DEFAULT TRUE,
            orden INT DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        echo "<p style='color:green;'>✅ Se ha creado la tabla 'avisos_reserva' en la base de datos.</p>";
        
        // Insertar avisos predeterminados
        $avisos_predeterminados = [
            "Debes llegar con al menos 10 minutos de antelación o podrías perder tu reserva.",
            "Si necesitas cancelar tu reserva, por favor hazlo con al menos 2 horas de antelación.",
            "Para grupos de más de 8 personas, se requiere un depósito del 20% que será descontado de la cuenta final."
        ];
        
        $stmt = $pdo->prepare("INSERT INTO avisos_reserva (texto, activo, orden) VALUES (?, 1, ?)");
        foreach ($avisos_predeterminados as $indice => $aviso) {
            $stmt->execute([$aviso, $indice + 1]);
        }
        echo "<p style='color:green;'>✅ Se han insertado avisos predeterminados en la tabla.</p>";
    } else {
        echo "<p>La tabla 'avisos_reserva' ya existe en la base de datos.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error al crear la tabla de avisos: " . $e->getMessage() . "</p>";
}

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Buscar la sección donde se debe insertar el código para mostrar los avisos
$patron_seccion = "/<div class=\"mt-3 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 text-sm\">(.*?)<\/div>/s";
preg_match($patron_seccion, $contenido, $coincidencias);

if (!empty($coincidencias)) {
    $seccion_original = $coincidencias[0];
    
    // Código para mostrar los avisos desde la base de datos
    $codigo_avisos = '<div class="mt-3 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 text-sm">
                                        <p class="font-medium">⚠️ Importante:</p>
                                        <p>Al confirmar la reserva, recibirás un correo electrónico con los detalles. Por favor, revisa también tu carpeta de spam o correo no deseado, ya que en ocasiones nuestros correos pueden ser filtrados por tu proveedor de correo electrónico.</p>
                                    </div>
                                    
                                    <!-- Avisos personalizados desde la base de datos -->
                                    <?php
                                    try {
                                        // Obtener avisos activos ordenados
                                        $stmt = $pdo->prepare("SELECT texto FROM avisos_reserva WHERE activo = 1 ORDER BY orden ASC");
                                        $stmt->execute();
                                        $avisos = $stmt->fetchAll();
                                        
                                        if (count($avisos) > 0) {
                                            echo \'<div class="mt-4 space-y-3">\';
                                            foreach ($avisos as $aviso) {
                                                echo \'<div class="p-3 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 text-sm">\';
                                                echo \'<p>\' . htmlspecialchars($aviso[\'texto\']) . \'</p>\';
                                                echo \'</div>\';
                                            }
                                            echo \'</div>\';
                                        }
                                    } catch (\Exception $e) {
                                        // Silenciar errores para no interrumpir el proceso
                                        error_log("Error al obtener avisos personalizados: " . $e->getMessage());
                                    }
                                    ?>';
    
    // Reemplazar la sección original con la nueva que incluye los avisos
    $contenido_modificado = str_replace($seccion_original, $codigo_avisos, $contenido);
    
    // Guardar el archivo modificado
    file_put_contents($archivo, $contenido_modificado);
    echo "<p style='color:green;'>✅ Se ha modificado el archivo para mostrar los avisos desde la base de datos.</p>";
} else {
    echo "<p style='color:red;'>❌ No se encontró la sección donde insertar el código para mostrar los avisos.</p>";
}

// Crear la página de administración de avisos
$archivo_admin_avisos = 'admin/gestionar_avisos.php';
if (!file_exists($archivo_admin_avisos)) {
    $contenido_admin_avisos = '<?php
// Página de administración de avisos para las reservas
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION[\'admin_id\'])) {
    header("Location: login.php");
    exit;
}

// Incluir conexión a la base de datos
require_once "../conexion.php";

// Manejar la creación de un nuevo aviso
if (isset($_POST[\'crear_aviso\'])) {
    $texto = trim($_POST[\'texto\']);
    $activo = isset($_POST[\'activo\']) ? 1 : 0;
    $orden = (int)$_POST[\'orden\'];
    
    if (!empty($texto)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO avisos_reserva (texto, activo, orden) VALUES (?, ?, ?)");
            $stmt->execute([$texto, $activo, $orden]);
            $mensaje_exito = "Aviso creado correctamente.";
        } catch (PDOException $e) {
            $error = "Error al crear el aviso: " . $e->getMessage();
        }
    } else {
        $error = "El texto del aviso no puede estar vacío.";
    }
}

// Manejar la actualización de un aviso
if (isset($_POST[\'actualizar_aviso\'])) {
    $id = (int)$_POST[\'id\'];
    $texto = trim($_POST[\'texto\']);
    $activo = isset($_POST[\'activo\']) ? 1 : 0;
    $orden = (int)$_POST[\'orden\'];
    
    if (!empty($texto)) {
        try {
            $stmt = $pdo->prepare("UPDATE avisos_reserva SET texto = ?, activo = ?, orden = ? WHERE id = ?");
            $stmt->execute([$texto, $activo, $orden, $id]);
            $mensaje_exito = "Aviso actualizado correctamente.";
        } catch (PDOException $e) {
            $error = "Error al actualizar el aviso: " . $e->getMessage();
        }
    } else {
        $error = "El texto del aviso no puede estar vacío.";
    }
}

// Manejar la eliminación de un aviso
if (isset($_POST[\'eliminar_aviso\'])) {
    $id = (int)$_POST[\'id\'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM avisos_reserva WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje_exito = "Aviso eliminado correctamente.";
    } catch (PDOException $e) {
        $error = "Error al eliminar el aviso: " . $e->getMessage();
    }
}

// Obtener todos los avisos
try {
    $stmt = $pdo->query("SELECT * FROM avisos_reserva ORDER BY orden ASC");
    $avisos = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error al obtener los avisos: " . $e->getMessage();
    $avisos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Avisos - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Encabezado -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Gestión de Avisos</h1>
            <p class="text-gray-600">Administra los avisos que se muestran en la página de confirmación de reservas</p>
            
            <!-- Menú de navegación -->
            <nav class="mt-4">
                <ul class="flex space-x-4">
                    <li><a href="index.php" class="text-blue-600 hover:text-blue-800"><i class="fas fa-home mr-1"></i> Inicio</a></li>
                    <li><a href="reservas.php" class="text-blue-600 hover:text-blue-800"><i class="fas fa-calendar-alt mr-1"></i> Reservas</a></li>
                </ul>
            </nav>
        </header>
        
        <!-- Mensajes de éxito o error -->
        <?php if (isset($mensaje_exito)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $mensaje_exito; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Formulario para crear/editar avisos -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4" id="formTitle">Crear Nuevo Aviso</h2>
                    
                    <form id="avisoForm" method="POST" action="">
                        <input type="hidden" id="aviso_id" name="id" value="">
                        
                        <div class="mb-4">
                            <label for="texto" class="block text-sm font-medium text-gray-700 mb-1">Texto del aviso</label>
                            <textarea id="texto" name="texto" rows="4" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border" required></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="orden" class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                            <input type="number" id="orden" name="orden" min="1" value="1" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            <p class="text-xs text-gray-500 mt-1">Los avisos se mostrarán ordenados de menor a mayor.</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="activo" name="activo" class="form-checkbox h-4 w-4 text-blue-600" checked>
                                <span class="ml-2 text-sm text-gray-700">Aviso activo</span>
                            </label>
                        </div>
                        
                        <div class="flex justify-between">
                            <button type="submit" id="btnCrear" name="crear_aviso" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Crear Aviso
                            </button>
                            
                            <button type="submit" id="btnActualizar" name="actualizar_aviso" class="hidden py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Actualizar Aviso
                            </button>
                            
                            <button type="button" id="btnCancelar" class="hidden py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Vista previa -->
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Vista Previa</h2>
                    
                    <div id="vistaPrevia" class="p-3 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 text-sm">
                        <p>El texto del aviso aparecerá aquí...</p>
                    </div>
                </div>
            </div>
            
            <!-- Lista de avisos existentes -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Avisos Existentes</h2>
                    
                    <?php if (count($avisos) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Texto</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Orden</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($avisos as $aviso): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo $aviso[\'id\']; ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <div class="max-w-xs truncate"><?php echo htmlspecialchars($aviso[\'texto\']); ?></div>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo $aviso[\'orden\']; ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <?php if ($aviso[\'activo\']): ?>
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Activo</span>
                                        <?php else: ?>
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <button type="button" class="text-blue-600 hover:text-blue-900 mr-2" 
                                            onclick="editarAviso(
                                                \'<?php echo $aviso[\'id\']; ?>\', 
                                                \'<?php echo addslashes($aviso[\'texto\']); ?>\', 
                                                \'<?php echo $aviso[\'orden\']; ?>\', 
                                                \'<?php echo $aviso[\'activo\']; ?>\'
                                            )">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form method="POST" action="" class="inline" onsubmit="return confirm(\'¿Estás seguro de que deseas eliminar este aviso?\');">
                                            <input type="hidden" name="id" value="<?php echo $aviso[\'id\']; ?>">
                                            <button type="submit" name="eliminar_aviso" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="bg-gray-50 p-4 text-center text-gray-500">
                        <p>No hay avisos registrados. Crea el primero utilizando el formulario.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Función para editar un aviso
        function editarAviso(id, texto, orden, activo) {
            document.getElementById(\'formTitle\').innerText = \'Editar Aviso\';
            document.getElementById(\'aviso_id\').value = id;
            document.getElementById(\'texto\').value = texto;
            document.getElementById(\'orden\').value = orden;
            document.getElementById(\'activo\').checked = activo === \'1\';
            
            document.getElementById(\'btnCrear\').classList.add(\'hidden\');
            document.getElementById(\'btnActualizar\').classList.remove(\'hidden\');
            document.getElementById(\'btnCancelar\').classList.remove(\'hidden\');
            
            // Desplazar al formulario
            document.getElementById(\'avisoForm\').scrollIntoView({ behavior: \'smooth\' });
        }
        
        // Función para cancelar la edición
        document.getElementById(\'btnCancelar\').addEventListener(\'click\', function() {
            document.getElementById(\'formTitle\').innerText = \'Crear Nuevo Aviso\';
            document.getElementById(\'avisoForm\').reset();
            document.getElementById(\'aviso_id\').value = \'\';
            
            document.getElementById(\'btnCrear\').classList.remove(\'hidden\');
            document.getElementById(\'btnActualizar\').classList.add(\'hidden\');
            document.getElementById(\'btnCancelar\').classList.add(\'hidden\');
        });
        
        // Vista previa en tiempo real
        document.getElementById(\'texto\').addEventListener(\'input\', function() {
            document.getElementById(\'vistaPrevia\').innerHTML = \'<p>\' + this.value + \'</p>\';
        });
    </script>
</body>
</html>';
    
    // Crear el directorio si no existe
    if (!is_dir('admin')) {
        mkdir('admin', 0755);
    }
    
    // Guardar el archivo
    file_put_contents($archivo_admin_avisos, $contenido_admin_avisos);
    echo "<p style='color:green;'>✅ Se ha creado la página de administración de avisos.</p>";
} else {
    echo "<p>La página de administración de avisos ya existe.</p>";
}

// Añadir enlace al menú de administración
$archivo_admin_index = 'admin/index.php';
if (file_exists($archivo_admin_index)) {
    $contenido_admin = file_get_contents($archivo_admin_index);
    
    // Verificar si ya existe el enlace
    if (strpos($contenido_admin, 'gestionar_avisos.php') === false) {
        // Buscar la sección del menú
        $patron_menu = "/<ul[^>]*class=\"[^\"]*space-y-2[^\"]*\"[^>]*>(.*?)<\/ul>/s";
        if (preg_match($patron_menu, $contenido_admin, $coincidencias)) {
            $menu_original = $coincidencias[0];
            
            // Añadir el enlace a gestionar avisos
            $enlace_avisos = '<li>
                <a href="gestionar_avisos.php" class="block py-2 px-4 text-gray-700 hover:bg-gray-100 rounded transition duration-150 ease-in-out">
                    <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i> Gestionar Avisos
                </a>
            </li>';
            
            $menu_modificado = str_replace('</ul>', $enlace_avisos . "\n            </ul>", $menu_original);
            $contenido_admin_modificado = str_replace($menu_original, $menu_modificado, $contenido_admin);
            
            // Guardar el archivo modificado
            file_put_contents($archivo_admin_index, $contenido_admin_modificado);
            echo "<p style='color:green;'>✅ Se ha añadido el enlace a la gestión de avisos en el menú de administración.</p>";
        } else {
            echo "<p style='color:orange;'>⚠️ No se encontró la sección del menú en el archivo de administración.</p>";
        }
    } else {
        echo "<p>El enlace a la gestión de avisos ya existe en el menú de administración.</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠️ No se encontró el archivo index.php de administración.</p>";
}

echo "<h1>Implementación completada</h1>";
echo "<p>Se ha implementado la funcionalidad para mostrar los avisos configurados en la administración en la página de confirmación de reserva.</p>";
echo "<p>Los cambios realizados incluyen:</p>";
echo "<ul>";
echo "<li>Creación de la tabla 'avisos_reserva' en la base de datos (si no existía)</li>";
echo "<li>Modificación del archivo confirmar_reserva.php para mostrar los avisos desde la base de datos</li>";
echo "<li>Creación de una página de administración para gestionar los avisos</li>";
echo "<li>Adición de un enlace a la gestión de avisos en el menú de administración</li>";
echo "</ul>";
echo "<p><a href='confirmar_reserva.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Ver página de confirmación</a></p>";
echo "<p><a href='admin/gestionar_avisos.php' style='display:inline-block;background-color:#2196F3;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:10px;'>Ir a la gestión de avisos</a></p>";
?>
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
    }
    h1 {
        color: #333;
        margin-top: 30px;
    }
    p {
        margin-bottom: 15px;
    }
    ul {
        margin-left: 20px;
        margin-bottom: 20px;
    }
    li {
        margin-bottom: 8px;
    }
</style>
