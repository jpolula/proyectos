<?php
// Incluir archivo de autenticación
require_once 'auth.php';

// Incluir sistema de notificaciones
require_once 'notificaciones.php';

// Título de la página
$pageTitle = 'Checkboxes Personalizados';

// Procesar formulario de creación/edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Acción: Crear nuevo checkbox
        if ($_POST['action'] === 'crear') {
            $texto = trim($_POST['texto']);
            $descripcion = trim($_POST['descripcion']);
            $activo = isset($_POST['activo']) ? 1 : 0;
            $orden = (int)$_POST['orden'];
            
            if (empty($texto)) {
                agregar_notificacion('error', 'El texto del checkbox es obligatorio.');
            } else {
                $es_obligatorio = isset($_POST['es_obligatorio']) ? 1 : 0;
                $tiene_textarea = isset($_POST['tiene_textarea']) ? 1 : 0;
                $placeholder_textarea = trim($_POST['placeholder_textarea'] ?? '');
                
                $stmt = $pdo->prepare("INSERT INTO checkboxes_personalizados 
                    (texto, descripcion, activo, es_obligatorio, tiene_textarea, placeholder_textarea, orden) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$texto, $descripcion, $activo, $es_obligatorio, $tiene_textarea, $placeholder_textarea, $orden]);
                
                agregar_notificacion('success', 'Checkbox personalizado creado correctamente.');
            }
        }
        
        // Acción: Actualizar checkbox existente
        else if ($_POST['action'] === 'actualizar' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $texto = trim($_POST['texto']);
            $descripcion = trim($_POST['descripcion']);
            $activo = isset($_POST['activo']) ? 1 : 0;
            $orden = (int)$_POST['orden'];
            
            if (empty($texto)) {
                agregar_notificacion('error', 'El texto del checkbox es obligatorio.');
            } else {
                $es_obligatorio = isset($_POST['es_obligatorio']) ? 1 : 0;
                $tiene_textarea = isset($_POST['tiene_textarea']) ? 1 : 0;
                $placeholder_textarea = trim($_POST['placeholder_textarea'] ?? '');
                
                $stmt = $pdo->prepare("UPDATE checkboxes_personalizados SET 
                    texto = ?, 
                    descripcion = ?, 
                    activo = ?,
                    es_obligatorio = ?,
                    tiene_textarea = ?,
                    placeholder_textarea = ?,
                    orden = ? 
                    WHERE id = ?");
                $stmt->execute([$texto, $descripcion, $activo, $es_obligatorio, $tiene_textarea, $placeholder_textarea, $orden, $id]);
                
                agregar_notificacion('success', 'Checkbox personalizado actualizado correctamente.');
            }
        }
        
        // Acción: Eliminar checkbox
        else if ($_POST['action'] === 'eliminar' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM checkboxes_personalizados WHERE id = ?");
            $stmt->execute([$id]);
            
            agregar_notificacion('success', 'Checkbox personalizado eliminado correctamente.');
        }
        
    } catch (PDOException $e) {
        agregar_notificacion('error', 'Error en la base de datos: ' . $e->getMessage());
    }
    
    // Redireccionar para evitar reenvío del formulario
    header('Location: checkboxes_personalizados.php');
    exit;
}

// Obtener todos los checkboxes personalizados
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->query("SELECT *, 
        COALESCE(es_obligatorio, 0) as es_obligatorio,
        COALESCE(tiene_textarea, 0) as tiene_textarea,
        COALESCE(placeholder_textarea, '') as placeholder_textarea
        FROM checkboxes_personalizados ORDER BY orden ASC");
    $checkboxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    agregar_notificacion('error', 'Error al obtener los checkboxes personalizados: ' . $e->getMessage());
    $checkboxes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistema de Reservas</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Barra de navegación superior -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold">Sistema de Reservas</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-300">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Checkboxes Personalizados</h1>
                <div>
                    <a href="personalizacion.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300 mr-2">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Personalización
                    </a>
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                        <i class="fas fa-home mr-2"></i> Panel Principal
                    </a>
                </div>
            </div>
            
            <!-- Mostrar notificaciones -->
            <?php echo mostrar_notificaciones(); ?>
            
            <!-- Formulario para crear nuevo checkbox -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Crear Nuevo Checkbox</h2>
                    <form method="POST" action="checkboxes_personalizados.php">
                        <input type="hidden" name="action" value="crear">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="texto" class="block text-sm font-medium text-gray-700 mb-2">Texto del Checkbox *</label>
                                <input type="text" id="texto" name="texto" required
                                       class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Este texto se mostrará junto al checkbox en el formulario.</p>
                            </div>
                            
                            <div>
                                <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="2"
                                          class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Texto explicativo que aparecerá debajo del checkbox.</p>
                            </div>
                            
                            <div>
                                <label for="orden" class="block text-sm font-medium text-gray-700 mb-2">Orden</label>
                                <input type="number" id="orden" name="orden" value="0" min="0"
                                       class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-sm text-gray-500">Posición en la que aparecerá este checkbox (menor número = más arriba).</p>
                            </div>
                            
                            <div class="flex items-start pt-6">
                                <div class="flex items-center h-5">
                                    <input id="activo" name="activo" type="checkbox" checked
                                           class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="activo" class="font-medium text-gray-700">Activo</label>
                                    <p class="text-gray-500">Si está marcado, este checkbox se mostrará en el formulario.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="es_obligatorio" name="es_obligatorio" type="checkbox"
                                           class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="es_obligatorio" class="font-medium text-gray-700">Obligatorio</label>
                                    <p class="text-gray-500">El usuario debe marcar esta opción</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="tiene_textarea" name="tiene_textarea" type="checkbox"
                                           class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="tiene_textarea" class="font-medium text-gray-700">Incluir área de texto</label>
                                    <p class="text-gray-500">Mostrar campo de texto adicional</p>
                                </div>
                            </div>
                            
                            <div id="textarea_container" class="hidden">
                                <label for="placeholder_textarea" class="block text-sm font-medium text-gray-700 mb-1">Texto de ayuda</label>
                                <input type="text" id="placeholder_textarea" name="placeholder_textarea" 
                                       class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Especifique sus necesidades...">
                                <p class="mt-1 text-xs text-gray-500">Texto que aparecerá como ayuda en el área de texto</p>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-300">
                                <i class="fas fa-plus mr-2"></i> Crear Checkbox
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Lista de checkboxes existentes -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Checkboxes Existentes</h2>
                    
                    <?php if (empty($checkboxes)): ?>
                        <p class="text-gray-500 italic">No hay checkboxes personalizados creados.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Texto</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orden</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($checkboxes as $checkbox): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $checkbox['id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($checkbox['texto']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($checkbox['descripcion']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $checkbox['orden']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $checkbox['activo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> mb-1">
                                                    <?php echo $checkbox['activo'] ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                                <?php if ($checkbox['es_obligatorio']): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 mb-1">
                                                    Obligatorio
                                                </span>
                                                <?php endif; ?>
                                                <?php if ($checkbox['tiene_textarea']): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800" title="<?php echo htmlspecialchars($checkbox['placeholder_textarea']); ?>">
                                                    Con Texto
                                                </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button" 
                                                    onclick="editarCheckbox(
                                                        <?php echo $checkbox['id']; ?>, 
                                                        '<?php echo addslashes(htmlspecialchars($checkbox['texto'])); ?>', 
                                                        '<?php echo addslashes(htmlspecialchars($checkbox['descripcion'])); ?>', 
                                                        <?php echo $checkbox['orden']; ?>, 
                                                        <?php echo $checkbox['activo']; ?>, 
                                                        <?php echo $checkbox['es_obligatorio']; ?>, 
                                                        <?php echo $checkbox['tiene_textarea']; ?>, 
                                                        '<?php echo addslashes(htmlspecialchars($checkbox['placeholder_textarea'])); ?>')" 
                                                    class="text-blue-600 hover:text-blue-900 mr-3">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button type="button" onclick="confirmarEliminar(<?php echo $checkbox['id']; ?>, '<?php echo addslashes(htmlspecialchars($checkbox['texto'])); ?>')" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar checkbox -->
    <div id="modalEditar" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Editar Checkbox</h3>
                <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="checkboxes_personalizados.php" id="formEditar">
                <input type="hidden" name="action" value="actualizar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="edit_texto" class="block text-sm font-medium text-gray-700 mb-2">Texto del Checkbox *</label>
                        <input type="text" id="edit_texto" name="texto" required
                               class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="edit_descripcion" class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                        <textarea id="edit_descripcion" name="descripcion" rows="2"
                                  class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    
                    <div>
                        <label for="edit_orden" class="block text-sm font-medium text-gray-700 mb-2">Orden</label>
                        <input type="number" id="edit_orden" name="orden" min="0"
                               class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="edit_activo" name="activo" type="checkbox"
                                       class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="edit_activo" class="font-medium text-gray-700">Activo</label>
                                <p class="text-gray-500">Mostrar en el formulario de reserva</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="edit_es_obligatorio" name="es_obligatorio" type="checkbox"
                                       class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="edit_es_obligatorio" class="font-medium text-gray-700">Obligatorio</label>
                                <p class="text-gray-500">El usuario debe marcar esta opción</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="edit_tiene_textarea" name="tiene_textarea" type="checkbox"
                                       class="h-4 w-4 border-gray-300 rounded text-blue-600 focus:ring-blue-500">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="edit_tiene_textarea" class="font-medium text-gray-700">Incluir área de texto</label>
                                <p class="text-gray-500">Mostrar campo de texto adicional</p>
                            </div>
                        </div>
                        
                        <div id="edit_textarea_container" class="hidden">
                            <label for="edit_placeholder_textarea" class="block text-sm font-medium text-gray-700 mb-1">Texto de ayuda</label>
                            <input type="text" id="edit_placeholder_textarea" name="placeholder_textarea" 
                                   class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ej: Especifique sus necesidades...">
                            <p class="mt-1 text-xs text-gray-500">Texto que aparecerá como ayuda en el área de texto</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="cerrarModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-300 mr-2">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-300">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para confirmar eliminación -->
    <div id="modalEliminar" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Confirmar Eliminación</h3>
                <button type="button" onclick="cerrarModalEliminar()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <p class="text-gray-700 mb-4">¿Estás seguro de que deseas eliminar el checkbox "<span id="eliminar_texto"></span>"?</p>
            <p class="text-red-600 text-sm mb-6">Esta acción no se puede deshacer.</p>
            
            <form method="POST" action="checkboxes_personalizados.php" id="formEliminar">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="id" id="eliminar_id">
                
                <div class="flex justify-end">
                    <button type="button" onclick="cerrarModalEliminar()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-300 mr-2">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-300">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes
            </p>
            <p class="text-center text-gray-500 text-sm mt-1">
                Realizada con ❤️ por <a href="https://impulsatelecom.com/" target="_blank" class="hover:text-gray-700 transition-colors duration-300">Impulsa Telecom</a>
            </p>
        </div>
    </footer>
    
    <script>
        // Mostrar/ocultar textarea en el formulario de edición
        function toggleTextareaEdit(mostrar) {
            const container = document.getElementById('edit_textarea_container');
            if (mostrar) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
        
        // Mostrar/ocultar textarea en el formulario de creación
        document.getElementById('tiene_textarea').addEventListener('change', function() {
            const container = document.getElementById('textarea_container');
            if (this.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        });
        
        // Mostrar/ocultar textarea en el formulario de edición al cambiar el checkbox
        document.getElementById('edit_tiene_textarea').addEventListener('change', function() {
            toggleTextareaEdit(this.checked);
        });
        
        // Funciones para el modal de edición
        function editarCheckbox(id, texto, descripcion, orden, activo, es_obligatorio, tiene_textarea, placeholder_textarea) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_texto').value = texto;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_orden').value = orden;
            document.getElementById('edit_activo').checked = activo === 1;
            document.getElementById('edit_es_obligatorio').checked = es_obligatorio === 1;
            document.getElementById('edit_tiene_textarea').checked = tiene_textarea === 1;
            document.getElementById('edit_placeholder_textarea').value = placeholder_textarea || '';
            
            // Mostrar/ocultar textarea según corresponda
            toggleTextareaEdit(tiene_textarea === 1);
            
            document.getElementById('modalEditar').classList.remove('hidden');
        }
        
        function cerrarModal() {
            document.getElementById('modalEditar').classList.add('hidden');
        }
        
        // Funciones para el modal de eliminación
        function confirmarEliminar(id, texto) {
            document.getElementById('eliminar_id').value = id;
            document.getElementById('eliminar_texto').textContent = texto;
            
            document.getElementById('modalEliminar').classList.remove('hidden');
        }
        
        function cerrarModalEliminar() {
            document.getElementById('modalEliminar').classList.add('hidden');
        }
        
        // Cerrar modales al hacer clic fuera de ellos
        window.addEventListener('click', function(event) {
            const modalEditar = document.getElementById('modalEditar');
            const modalEliminar = document.getElementById('modalEliminar');
            
            if (event.target === modalEditar) {
                cerrarModal();
            }
            
            if (event.target === modalEliminar) {
                cerrarModalEliminar();
            }
        });
    </script>
</body>
</html>
