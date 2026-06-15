<?php
// Incluir archivo de autenticación
require_once 'auth.php';

// Incluir sistema de notificaciones
require_once 'notificaciones.php';

// Título de la página
$pageTitle = 'Personalización';

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Actualizar color principal, color secundario, tipo de letra y textos
        $color_principal = $_POST['color_principal'] ?? '#3B82F6';
        $color_secundario = $_POST['color_secundario'] ?? '#FF9800';
        $mostrar_confeti = isset($_POST['mostrar_confeti']) ? 1 : 0;
        $tipo_letra = $_POST['tipo_letra'] ?? 'system-ui';
        $titulo_principal = $_POST['titulo_principal'] ?? 'Sistema de Reservas de Restaurantes';
        $subtitulo = $_POST['subtitulo'] ?? 'Introduce tus datos para comenzar la reserva';
        
        $stmt = $pdo->prepare("UPDATE configuracion SET color_principal = ?, color_secundario = ?, mostrar_confeti = ?, tipo_letra = ?, titulo_principal = ?, subtitulo = ? WHERE id = 1");
        $stmt->execute([$color_principal, $color_secundario, $mostrar_confeti, $tipo_letra, $titulo_principal, $subtitulo]);
        
        // Procesar el logo si se ha subido uno nuevo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/';
            
            // Crear el directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Obtener la extensión del archivo
            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            
            // Verificar que sea una imagen válida
            if (in_array($file_extension, $allowed_extensions)) {
                // Generar un nombre único para el archivo
                $new_filename = 'logo_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Mover el archivo subido al directorio de destino
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                    // Eliminar el logo anterior si existe
                    $stmt = $pdo->query("SELECT logo_path FROM configuracion WHERE id = 1");
                    $old_logo = $stmt->fetchColumn();
                    
                    if ($old_logo && file_exists('../' . $old_logo)) {
                        unlink('../' . $old_logo);
                    }
                    
                    // Actualizar la ruta del logo en la base de datos
                    $logo_path = 'uploads/' . $new_filename;
                    $stmt = $pdo->prepare("UPDATE configuracion SET logo_path = ? WHERE id = 1");
                    $stmt->execute([$logo_path]);
                    
                    agregar_notificacion('success', 'Logo actualizado correctamente.');
                } else {
                    agregar_notificacion('error', 'Error al subir el logo.');
                }
            } else {
                agregar_notificacion('error', 'Formato de imagen no válido. Se permiten: jpg, jpeg, png, gif, svg.');
            }
        }
        
        agregar_notificacion('success', 'Configuración de personalización actualizada correctamente.');
        
    } catch (PDOException $e) {
        agregar_notificacion('error', 'Error al actualizar la configuración: ' . $e->getMessage());
    }
    
    // Redireccionar para evitar reenvío del formulario
    header('Location: personalizacion.php');
    exit;
}

// Obtener la configuración actual
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->query("SELECT logo_path, color_principal, color_secundario, mostrar_confeti, tipo_letra, titulo_principal, subtitulo FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    agregar_notificacion('error', 'Error al obtener la configuración: ' . $e->getMessage());
    $config = [
        'logo_path' => null,
        'color_principal' => '#3B82F6',
        'color_secundario' => '#FF9800',
        'mostrar_confeti' => true,
        'tipo_letra' => 'system-ui',
        'titulo_principal' => 'Sistema de Reservas de Restaurantes',
        'subtitulo' => 'Introduce tus datos para comenzar la reserva'
    ];
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
    <style>
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-block;
            margin-left: 10px;
            border: 1px solid #ccc;
        }
    </style>
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
                <h1 class="text-2xl font-semibold text-gray-900">Personalización</h1>
                <div>
                    <a href="checkboxes_simple.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300 mr-2">
                        <i class="fas fa-check-square mr-2"></i> Gestionar Checkboxes Personalizados
                    </a>
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al Panel
                    </a>
                </div>
            </div>
            
            <!-- Mostrar notificaciones -->
            <?php echo mostrar_notificaciones(); ?>
            
            <!-- Formulario de personalización -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Logo -->
                        <div class="mb-6">
                            <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">Logo del Restaurante</label>
                            
                            <?php if (!empty($config['logo_path']) && file_exists('../' . $config['logo_path'])): ?>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 mb-2">Logo actual:</p>
                                    <img src="../<?php echo htmlspecialchars($config['logo_path']); ?>" alt="Logo actual" class="max-h-32 border p-2 rounded">
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-1 flex items-center">
                                <input type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png,.gif,.svg" class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Formatos permitidos: JPG, JPEG, PNG, GIF, SVG. Tamaño recomendado: 200x100 píxeles.</p>
                        </div>
                        
                        <!-- Color Principal -->
                        <div class="mb-6">
                            <label for="color_principal" class="block text-sm font-medium text-gray-700 mb-2">Color Principal</label>
                            <div class="mt-1 flex items-center">
                                <input type="color" id="color_principal" name="color_principal" value="<?php echo $config['color_principal']; ?>" class="h-10 w-20 border-gray-300 rounded-md shadow-sm">
                                <span class="ml-3 text-gray-500 text-sm"><?php echo $config['color_principal']; ?></span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Este color se utilizará para los elementos principales como encabezados y botones.</p>
                        </div>
                        
                        <!-- Color Secundario -->
                        <div class="mb-6">
                            <label for="color_secundario" class="block text-sm font-medium text-gray-700 mb-2">Color Secundario</label>
                            <div class="mt-1 flex items-center">
                                <input type="color" id="color_secundario" name="color_secundario" value="<?php echo $config['color_secundario'] ?? '#FF9800'; ?>" class="h-10 w-20 border-gray-300 rounded-md shadow-sm">
                                <span class="ml-3 text-gray-500 text-sm" id="color_secundario_text"><?php echo $config['color_secundario'] ?? '#FF9800'; ?></span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Este color se utilizará para elementos seleccionables como el calendario, botones de selección, etc.</p>
                        </div>
                        
                        <!-- Confeti en Reserva Exitosa -->
                        <div class="mb-6">
                            <label for="mostrar_confeti" class="block text-sm font-medium text-gray-700 mb-2">Confeti en Reserva Exitosa</label>
                            <div class="mt-1">
                                <div class="flex items-center">
                                    <input type="checkbox" id="mostrar_confeti" name="mostrar_confeti" value="1" <?php echo (isset($config['mostrar_confeti']) && $config['mostrar_confeti']) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="mostrar_confeti" class="ml-2 block text-sm text-gray-900">Mostrar animación de confeti cuando se completa una reserva</label>
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Activa esta opción para mostrar una animación festiva de confeti cuando un cliente completa una reserva exitosamente.</p>
                        </div>
                        
                        <!-- Tipo de letra -->
                        <div class="mb-6">
                            <label for="tipo_letra" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Letra</label>
                            <div class="mt-1">
                                <select id="tipo_letra" name="tipo_letra" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial" <?php echo ($config['tipo_letra'] === 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial') ? 'selected' : ''; ?>>Sistema (por defecto)</option>
                                    <option value="'Roboto', sans-serif" <?php echo ($config['tipo_letra'] === "'Roboto', sans-serif") ? 'selected' : ''; ?>>Roboto</option>
                                    <option value="'Open Sans', sans-serif" <?php echo ($config['tipo_letra'] === "'Open Sans', sans-serif") ? 'selected' : ''; ?>>Open Sans</option>
                                    <option value="'Lato', sans-serif" <?php echo ($config['tipo_letra'] === "'Lato', sans-serif") ? 'selected' : ''; ?>>Lato</option>
                                    <option value="'Montserrat', sans-serif" <?php echo ($config['tipo_letra'] === "'Montserrat', sans-serif") ? 'selected' : ''; ?>>Montserrat</option>
                                    <option value="'Poppins', sans-serif" <?php echo ($config['tipo_letra'] === "'Poppins', sans-serif") ? 'selected' : ''; ?>>Poppins</option>
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Este tipo de letra se utilizará en toda la aplicación.</p>
                            
                            <!-- Vista previa del tipo de letra -->
                            <div class="mt-4 p-4 border rounded-md">
                                <p class="text-sm text-gray-500 mb-2">Vista previa:</p>
                                <p id="preview_text" class="text-lg" style="font-family: <?php echo htmlspecialchars($config['tipo_letra']); ?>">
                                    Este es un ejemplo de texto con el tipo de letra seleccionado.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Título principal -->
                        <div class="mb-6">
                            <label for="titulo_principal" class="block text-sm font-medium text-gray-700 mb-2">Título Principal</label>
                            <div class="mt-1">
                                <input type="text" id="titulo_principal" name="titulo_principal" value="<?php echo htmlspecialchars($config['titulo_principal']); ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Este título aparecerá en el encabezado de la página principal.</p>
                        </div>
                        
                        <!-- Subtítulo -->
                        <div class="mb-6">
                            <label for="subtitulo" class="block text-sm font-medium text-gray-700 mb-2">Subtítulo</label>
                            <div class="mt-1">
                                <input type="text" id="subtitulo" name="subtitulo" value="<?php echo htmlspecialchars($config['subtitulo']); ?>" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Este subtítulo aparecerá debajo del título principal en el encabezado.</p>
                        </div>
                        
                        <!-- Botón de guardar -->
                        <div class="mt-6">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:border-blue-500">
                                <i class="fas fa-save mr-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; <?php echo date('Y'); ?> Sistema de Reservas. Todos los derechos reservados.
            </p>
        </div>
    </footer>

    <!-- JavaScript para la vista previa -->
    <script>
        // Actualizar el valor hexadecimal y la vista previa cuando cambie el color principal
        const colorPicker = document.getElementById('color_principal');
        
        // Actualizar el valor hexadecimal y la vista previa cuando cambie el color secundario
        const colorSecundarioPicker = document.getElementById('color_secundario');
        const colorSecundarioText = document.getElementById('color_secundario_text');
        
        colorSecundarioPicker.addEventListener('input', function() {
            const color = this.value;
            colorSecundarioText.textContent = color;
            
            // Aplicar el color secundario a elementos de ejemplo
            document.documentElement.style.setProperty('--color-secondary', color);
            
            // Calcular variantes del color secundario
            const colorSecundarioDark = adjustBrightness(color, -20);
            const colorSecundarioLight = adjustBrightness(color, 40);
            
            document.documentElement.style.setProperty('--color-secondary-dark', colorSecundarioDark);
            document.documentElement.style.setProperty('--color-secondary-light', colorSecundarioLight);
        });
        
        colorPicker.addEventListener('input', function() {
            const color = this.value;
            
            // Aplicar el color principal a elementos de ejemplo
            document.documentElement.style.setProperty('--color-primary', color);
            
            // Calcular variantes del color principal
            const colorDark = adjustBrightness(color, -20);
            const colorLight = adjustBrightness(color, 40);
            
            document.documentElement.style.setProperty('--color-primary-dark', colorDark);
            document.documentElement.style.setProperty('--color-primary-light', colorLight);
        });
        
        // Función para ajustar el brillo de un color hexadecimal
        function adjustBrightness(color, percent) {
            let R = parseInt(color.substring(1,3), 16);
            let G = parseInt(color.substring(3,5), 16);
            let B = parseInt(color.substring(5,7), 16);

            R = parseInt(R * (100 + percent) / 100);
            G = parseInt(G * (100 + percent) / 100);
            B = parseInt(B * (100 + percent) / 100);

            R = (R < 255) ? R : 255;  
            G = (G < 255) ? G : 255;  
            B = (B < 255) ? B : 255;  

            R = Math.max(0, R).toString(16).padStart(2, '0');
            G = Math.max(0, G).toString(16).padStart(2, '0');
            B = Math.max(0, B).toString(16).padStart(2, '0');

            return `#${R}${G}${B}`;
        }
        
        // Actualizar la vista previa del tipo de letra
        const tipoLetraSelect = document.getElementById('tipo_letra');
        const previewText = document.getElementById('preview_text');
        
        tipoLetraSelect.addEventListener('change', function() {
            previewText.style.fontFamily = this.value;
            
            // Cargar las fuentes de Google si es necesario
            if (this.value !== 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial') {
                const fontName = this.value.replace(/[']/g, '').split(',')[0].trim();
                
                // Verificar si ya existe el enlace de la fuente
                const existingLink = document.querySelector(`link[href*="${fontName}"]`);
                if (!existingLink) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@400;700&display=swap`;
                    document.head.appendChild(link);
                }
            }
        });
        
        // Cargar las fuentes de Google al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const currentFont = tipoLetraSelect.value;
            if (currentFont !== 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial') {
                const fontName = currentFont.replace(/[']/g, '').split(',')[0].trim();
                
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = `https://fonts.googleapis.com/css2?family=${fontName}:wght@400;700&display=swap`;
                document.head.appendChild(link);
            }
        });
    </script>
</body>
</html>
