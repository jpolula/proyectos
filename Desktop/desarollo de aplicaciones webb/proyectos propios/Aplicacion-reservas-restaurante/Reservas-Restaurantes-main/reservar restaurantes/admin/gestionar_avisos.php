<?php
// Página de administración de avisos para las reservas
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Incluir conexión a la base de datos
require_once "../conexion.php";

// Manejar la creación de un nuevo aviso
if (isset($_POST['crear_aviso'])) {
    $texto = trim($_POST['texto']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $orden = (int)$_POST['orden'];
    
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
if (isset($_POST['actualizar_aviso'])) {
    $id = (int)$_POST['id'];
    $texto = trim($_POST['texto']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $orden = (int)$_POST['orden'];
    
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
if (isset($_POST['eliminar_aviso'])) {
    $id = (int)$_POST['id'];
    
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
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo $aviso['id']; ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <div class="max-w-xs truncate"><?php echo htmlspecialchars($aviso['texto']); ?></div>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo $aviso['orden']; ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <?php if ($aviso['activo']): ?>
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Activo</span>
                                        <?php else: ?>
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <button type="button" class="text-blue-600 hover:text-blue-900 mr-2" 
                                            onclick="editarAviso(
                                                '<?php echo $aviso['id']; ?>', 
                                                '<?php echo addslashes($aviso['texto']); ?>', 
                                                '<?php echo $aviso['orden']; ?>', 
                                                '<?php echo $aviso['activo']; ?>'
                                            )">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form method="POST" action="" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este aviso?');">
                                            <input type="hidden" name="id" value="<?php echo $aviso['id']; ?>">
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
            document.getElementById('formTitle').innerText = 'Editar Aviso';
            document.getElementById('aviso_id').value = id;
            document.getElementById('texto').value = texto;
            document.getElementById('orden').value = orden;
            document.getElementById('activo').checked = activo === '1';
            
            document.getElementById('btnCrear').classList.add('hidden');
            document.getElementById('btnActualizar').classList.remove('hidden');
            document.getElementById('btnCancelar').classList.remove('hidden');
            
            // Desplazar al formulario
            document.getElementById('avisoForm').scrollIntoView({ behavior: 'smooth' });
        }
        
        // Función para cancelar la edición
        document.getElementById('btnCancelar').addEventListener('click', function() {
            document.getElementById('formTitle').innerText = 'Crear Nuevo Aviso';
            document.getElementById('avisoForm').reset();
            document.getElementById('aviso_id').value = '';
            
            document.getElementById('btnCrear').classList.remove('hidden');
            document.getElementById('btnActualizar').classList.add('hidden');
            document.getElementById('btnCancelar').classList.add('hidden');
        });
        
        // Vista previa en tiempo real
        document.getElementById('texto').addEventListener('input', function() {
            document.getElementById('vistaPrevia').innerHTML = '<p>' + this.value + '</p>';
        });
    </script>
</body>
</html>