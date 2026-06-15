<?php
// Archivo index.php con formulario para datos del usuario
session_start();

// Procesar el formulario de confirmación de reserva
if (isset($_POST["confirmar"]) && $_POST["confirmar"] === "true") {
    // Incluir el archivo que procesa la reserva
    include "confirmar_reserva.php";
    exit;
}

// Mostrar mensaje de éxito si la reserva fue confirmada
if (isset($_SESSION['reserva_exitosa']) && $_SESSION['reserva_exitosa'] === true) {
    $mensaje_correo = "";
    
    // Verificar si el correo se envió correctamente
    if (isset($_SESSION['correo_enviado']) && $_SESSION['correo_enviado'] === true) {
        $mensaje_correo = "<p>Hemos enviado un correo con los detalles a tu dirección de email.</p>
                          <p class=\"text-sm mt-2\">Recuerda revisar también tu carpeta de spam o correo no deseado.</p>";
        unset($_SESSION['correo_enviado']);
    } else if (isset($_SESSION['error_correo']) && $_SESSION['error_correo'] === true) {
        $mensaje_correo = "<p class=\"text-yellow-700 bg-yellow-50 p-2 mt-2 rounded\">No pudimos enviar el correo de confirmación. Por favor, guarda esta página como referencia de tu reserva.</p>";
        unset($_SESSION['error_correo']);
    } else {
        $mensaje_correo = "<p>Se ha registrado tu reserva correctamente.</p>";
    }
    
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">¡Reserva confirmada!</p>
            ' . $mensaje_correo . '
          </div>';
    
    // Limpiar la variable de sesión
    unset($_SESSION['reserva_exitosa']);
}

// Obtener el número máximo de personas sin aprobación desde la base de datos
$max_personas_sin_aprobacion = 4; // Valor por defecto

try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $pdo->query("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        $max_personas_sin_aprobacion = $config['max_personas_sin_aprobacion'];
    }
} catch (PDOException $e) {
    // Silenciar error y usar valor por defecto
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["confirmar"])) {
    // Validar y recoger los datos del usuario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $num_personas = isset($_POST['num_personas']) ? (int)$_POST['num_personas'] : 0;
    $tiene_alergenos = isset($_POST['tiene_alergenos']) ? true : false;
    $alergenos = isset($_POST['alergenos']) ? trim($_POST['alergenos']) : '';
    $tiene_necesidades = isset($_POST['tiene_necesidades']) ? true : false;
    $necesidades_especiales = isset($_POST['necesidades_especiales']) ? trim($_POST['necesidades_especiales']) : '';

    // Validación básica
    $errores = [];
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre es obligatorio';
    }
    if (empty($email)) {
        $errores['email'] = 'El email es obligatorio';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El formato del email no es válido';
    }
    if (empty($telefono)) {
        $errores['telefono'] = 'El teléfono es obligatorio';
    } elseif (!preg_match('/^[0-9]{9}$/', $telefono)) {
        $errores['telefono'] = 'El teléfono debe tener 9 dígitos';
    }
    if ($num_personas <= 0) {
        $errores['num_personas'] = 'El número de personas debe ser al menos 1';
    }

    // Si no hay errores, guardar los datos en la sesión y redirigir a la página de reserva
    if (empty($errores)) {
        // Guardar datos en variables de sesión individuales para compatibilidad con reserva.php
        $_SESSION['nombre'] = $nombre;
        $_SESSION['email'] = $email;
        $_SESSION['telefono'] = $telefono;
        $_SESSION['num_personas'] = $num_personas;
        $_SESSION['tiene_alergenos'] = $tiene_alergenos;
        $_SESSION['alergenos'] = $alergenos;
        $_SESSION['tiene_necesidades'] = $tiene_necesidades;
        $_SESSION['necesidades_especiales'] = $necesidades_especiales;
        
        // Guardar valores de checkboxes personalizados y sus respuestas
        $_SESSION['checkboxes_personalizados'] = [];
        $_SESSION['respuestas_checkboxes'] = [];
        
        if (isset($_POST['checkboxes_personalizados']) && is_array($_POST['checkboxes_personalizados'])) {
            foreach ($_POST['checkboxes_personalizados'] as $id => $value) {
                $_SESSION['checkboxes_personalizados'][$id] = true;
                
                // Guardar la respuesta del textarea si existe
                if (isset($_POST['checkboxes_respuestas'][$id]) && !empty(trim($_POST['checkboxes_respuestas'][$id]))) {
                    $_SESSION['respuestas_checkboxes'][$id] = trim($_POST['checkboxes_respuestas'][$id]);
                }
            }
        }

        // Redirigir a la página de reserva
        header('Location: reserva.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de Usuario - Sistema de Reservas de Restaurantes</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include_once 'includes/header.php'; ?>
    <style>
        .error-message {
            color: #b91c1c;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
    <script>
        // Función para mostrar/ocultar el área de texto de alérgenos
        function toggleAlergenos() {
            const checkbox = document.getElementById('tiene_alergenos');
            const container = document.getElementById('alergenos_container');
            
            if (checkbox.checked) {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
        
        // Función para mostrar/ocultar el área de texto de necesidades especiales
        function toggleNecesidades() {
            const checkbox = document.getElementById('tiene_necesidades');
            const container = document.getElementById('necesidades_container');
            
            if (checkbox.checked) {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
        
        // Inicializar los estados cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            // Asegurar que los contenedores estén en el estado correcto al cargar la página
            toggleAlergenos();
            toggleNecesidades();
        });
    </script>
</head>
<body class="bg-gray-100 min-h-screen">

    <?php
    // Mostrar mensaje de éxito si la reserva fue confirmada
    if (isset($_SESSION['reserva_exitosa']) && $_SESSION['reserva_exitosa'] === true) {
        $mensaje_correo = "";
        
        // Verificar si el correo se envió correctamente
        if (isset($_SESSION['correo_enviado']) && $_SESSION['correo_enviado'] === true) {
            $mensaje_correo = "<p>Hemos enviado un correo con los detalles a tu dirección de email.</p>
                              <p class=\"text-sm mt-2\">Recuerda revisar también tu carpeta de spam o correo no deseado.</p>";
            unset($_SESSION['correo_enviado']);
        } else if (isset($_SESSION['error_correo']) && $_SESSION['error_correo'] === true) {
            $mensaje_correo = "<p class=\"text-yellow-700 bg-yellow-50 p-2 mt-2 rounded\">No pudimos enviar el correo de confirmación. Por favor, guarda esta página como referencia de tu reserva.</p>";
            unset($_SESSION['error_correo']);
        } else {
            $mensaje_correo = "<p>Se ha registrado tu reserva correctamente.</p>";
        }
        
        echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p class="font-bold">¡Reserva confirmada!</p>
                ' . $mensaje_correo . '
              </div>';
        
        // Limpiar la variable de sesión
        unset($_SESSION['reserva_exitosa']);
    }
    ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center text-center mb-4">
            <div style="max-width: 250px;">
                <?php echo obtener_logo('w-full h-auto'); ?>
            </div>
        </div>
        
        <!-- Texto personalizado en caja separada con color principal como fondo -->
        <div class="rounded-lg shadow-md p-6 text-white text-center mb-8" style="background-color: var(--color-primary);">
            <h2 class="text-2xl font-bold"><?php echo htmlspecialchars(obtener_titulo_principal()); ?></h2>
            <p class="mt-2"><?php echo htmlspecialchars(obtener_subtitulo()); ?></p>
        </div>
        
        <main class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-2" style="border-color: var(--color-primary);">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Datos del Cliente</h2>
                
                <?php if (isset($errores) && !empty($errores)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                    <p class="font-bold">Por favor, corrige los siguientes errores:</p>
                    <ul class="list-disc pl-5">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-6">
                    <div class="form-group">
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">Nombre y apellidos *</label>
                        <input type="text" id="nombre" name="nombre" 
                               class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 text-black" style="--tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5; border-color: var(--color-secondary);" 
                               placeholder="Introduce tu nombre y apellidos" 
                               value="<?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : (isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''); ?>">
                        <?php if (isset($errores['nombre'])): ?>
                            <p class="text-red-500 text-sm mt-1"><?php echo $errores['nombre']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="email" name="email" 
                               class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 text-black" style="--tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5; border-color: var(--color-secondary);" 
                               placeholder="ejemplo@correo.com" 
                               value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>">
                        <?php if (isset($errores['email'])): ?>
                            <p class="error-message"><?php echo $errores['email']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                        <input type="tel" id="telefono" name="telefono" value="<?php echo isset($_SESSION['telefono']) ? htmlspecialchars($_SESSION['telefono']) : (isset($telefono) ? htmlspecialchars($telefono) : ''); ?>" 
                               class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 text-black" style="--tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5; border-color: var(--color-secondary);" 
                               placeholder="Introduce tu número de teléfono" required>
                        <?php if (isset($errores['telefono'])): ?>
                            <p class="error-message"><?php echo $errores['telefono']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="num_personas" class="block text-sm font-medium text-gray-700 mb-1">Número de personas *</label>
                        <input type="number" id="num_personas" name="num_personas" value="<?php echo isset($_SESSION['num_personas']) ? htmlspecialchars($_SESSION['num_personas']) : (isset($num_personas) ? htmlspecialchars($num_personas) : ''); ?>" 
                               class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 text-black" style="--tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5; border-color: var(--color-secondary);" 
                               placeholder="Número de personas" min="1" max="30" required>
                        <?php if (isset($errores['num_personas'])): ?>
                            <p class="error-message"><?php echo $errores['num_personas']; ?></p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-500 mt-1">Nota: Las reservas para más de <?php echo $max_personas_sin_aprobacion; ?> personas requieren confirmación por parte del restaurante.</p>
                    </div>
                    
                    <div class="form-group">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="tiene_alergenos" name="tiene_alergenos" type="checkbox" 
                       class="h-4 w-4 border-gray-300 rounded" style="color: var(--color-secondary); --tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5;"
                       <?php echo (isset($_SESSION['tiene_alergenos']) && $_SESSION['tiene_alergenos']) ? 'checked' : ((isset($tiene_alergenos) && $tiene_alergenos) ? 'checked' : ''); ?>
                       onchange="toggleAlergenos()">
            </div>
            <div class="ml-3 text-sm">
                <label for="tiene_alergenos" class="font-medium text-gray-700">Tengo alérgenos o intolerancias alimentarias</label>
                <p class="text-gray-500">Marca esta casilla si alguno de los comensales tiene alguna alergia o intolerancia alimentaria.</p>
            </div>
        </div>
        
        <!-- Área de texto para alérgenos (inicialmente oculta) -->
        <div id="alergenos_container" class="mt-3" style="display: <?php echo (isset($_SESSION['tiene_alergenos']) && $_SESSION['tiene_alergenos']) ? 'block' : ((isset($tiene_alergenos) && $tiene_alergenos) ? 'block' : 'none'); ?>">
            <textarea id="alergenos" name="alergenos" rows="3" 
                      class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2" style="--tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5; border-color: var(--color-secondary);" 
                      placeholder="Describe los alérgenos o intolerancias alimentarias"><?php echo isset($_SESSION['alergenos']) ? htmlspecialchars($_SESSION['alergenos']) : (isset($_POST['alergenos']) ? htmlspecialchars($_POST['alergenos']) : ''); ?></textarea>
            <p class="text-sm text-gray-500 mt-1">Ejemplo: Alergia a frutos secos, intolerancia a la lactosa, celiaquía, etc.</p>
        </div>
    </div>
    
    <div class="form-group">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="tiene_necesidades" name="tiene_necesidades" type="checkbox" 
                       class="h-4 w-4 border-gray-300 rounded" style="color: var(--color-secondary); --tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5;"
                       <?php echo (isset($_SESSION['tiene_necesidades']) && $_SESSION['tiene_necesidades']) ? 'checked' : ((isset($tiene_necesidades) && $tiene_necesidades) ? 'checked' : ''); ?>
                       onchange="toggleNecesidades()">
            </div>
            <div class="ml-3 text-sm">
                <label for="tiene_necesidades" class="font-medium text-gray-700">Tengo necesidades especiales</label>
                <p class="text-gray-500">Marca esta casilla si alguno de los comensales tiene alguna necesidad especial.</p>
            </div>
        </div>
        
        <!-- Área de texto para necesidades especiales (inicialmente oculta) -->
        <div id="necesidades_container" class="mt-3" style="display: <?php echo (isset($_SESSION['tiene_necesidades']) && $_SESSION['tiene_necesidades']) ? 'block' : ((isset($tiene_necesidades) && $tiene_necesidades) ? 'block' : 'none'); ?>">
            <textarea id="necesidades_especiales" name="necesidades_especiales" rows="3" 
                      class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2" style="--tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5; border-color: var(--color-secondary);" 
                      placeholder="Describe las necesidades especiales"><?php echo isset($_SESSION['necesidades_especiales']) ? htmlspecialchars($_SESSION['necesidades_especiales']) : (isset($necesidades_especiales) ? htmlspecialchars($necesidades_especiales) : ''); ?></textarea>
            <p class="text-sm text-gray-500 mt-1">Ejemplo: Necesidad de silla para bebé, acceso para silla de ruedas, etc.</p>
        </div>
    </div>
                
                    <?php
                    // Obtener checkboxes personalizados
                    try {
                        $pdo_checkboxes = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        ]);
                        
                        $stmt_checkboxes = $pdo_checkboxes->query("SELECT id, texto, descripcion, activo, es_obligatorio, tiene_textarea, placeholder_textarea, orden FROM checkboxes_personalizados WHERE activo = 1 ORDER BY orden ASC");
                        $checkboxes_personalizados = $stmt_checkboxes->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($checkboxes_personalizados)) {
                            echo '<div class="form-group border-t border-gray-200 pt-6 mt-6">';
                            echo '<h3 class="text-lg font-medium text-gray-900 mb-4">Información adicional</h3>';
                            
                            foreach ($checkboxes_personalizados as $checkbox) {
                                $checkbox_id = 'checkbox_personalizado_' . $checkbox['id'];
                                $is_checked = isset($_SESSION['checkboxes_personalizados'][$checkbox['id']]) && 
                                              $_SESSION['checkboxes_personalizados'][$checkbox['id']] === true;
                                
                                // Determinar si el checkbox es obligatorio
                                $required_attr = $checkbox['es_obligatorio'] ? 'required' : '';
                                $required_text = $checkbox['es_obligatorio'] ? ' *' : '';
                                
                                echo '<div class="flex flex-col mb-4 p-4 border border-gray-200 rounded-lg">';
                                echo '    <div class="flex items-start">';
                                echo '        <div class="flex items-center h-5">';
                                echo '            <input id="' . $checkbox_id . '" name="checkboxes_personalizados[' . $checkbox['id'] . ']" type="checkbox" ' . 
                                          ($is_checked ? 'checked' : '') . ' ' . $required_attr . ' class="h-4 w-4 border-gray-300 rounded" style="color: var(--color-secondary); --tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5;" data-has-textarea="' . ($checkbox['tiene_textarea'] ? 'true' : 'false') . '">';
                                echo '        </div>';
                                echo '        <div class="ml-3 text-sm">';
                                echo '            <label for="' . $checkbox_id . '" class="font-medium text-gray-700">' . htmlspecialchars($checkbox['texto']) . $required_text . '</label>';
                                
                                if (!empty($checkbox['descripcion'])) {
                                    echo '        <p class="text-gray-500">' . htmlspecialchars($checkbox['descripcion']) . '</p>';
                                }
                                
                                echo '        </div>';
                                echo '    </div>'; // Cierre del flex items-start
                                
                                // Mostrar área de texto si el checkbox la tiene habilitada
                                if ($checkbox['tiene_textarea']) {
                                    $texto_respuesta = isset($_SESSION['checkboxes_respuestas'][$checkbox['id']]) ? 
                                        htmlspecialchars($_SESSION['checkboxes_respuestas'][$checkbox['id']]) : '';
                                    $placeholder = !empty($checkbox['placeholder_textarea']) ? 
                                        htmlspecialchars($checkbox['placeholder_textarea']) : 'Por favor, proporcione más detalles';
                                    
                                    echo '    <div id="textarea_container_' . $checkbox['id'] . '" class="mt-3 ml-7 ' . ($is_checked ? '' : 'hidden') . '">';
                                    echo '        <textarea name="checkboxes_respuestas[' . $checkbox['id'] . ']" ';
                                    echo '                  class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"';
                                    echo '                  rows="3" placeholder="' . $placeholder . '"' . ($checkbox['es_obligatorio'] ? ' required' : '') . '>' . $texto_respuesta . '</textarea>';
                                    echo '    </div>';
                                }
                                
                                echo '</div>'; // Cierre del contenedor del checkbox
                            }
                            
                            echo '</div>'; // Cierre del form-group
                            
                            // Añadir JavaScript para manejar la visibilidad de las áreas de texto
                            ?>
                            <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                // Función para mostrar/ocultar áreas de texto
                                function toggleTextarea(checkbox) {
                                    const hasTextarea = checkbox.dataset.hasTextarea === "true";
                                    if (hasTextarea) {
                                        const textareaContainer = document.getElementById("textarea_container_" + checkbox.id.replace("checkbox_personalizado_", ""));
                                        if (!textareaContainer) return;
                                        
                                        if (checkbox.checked) {
                                            textareaContainer.classList.remove("hidden");
                                            const textarea = textareaContainer.querySelector("textarea");
                                            if (textarea) {
                                                textarea.required = checkbox.required;
                                                textarea.disabled = false;
                                            }
                                        } else {
                                            textareaContainer.classList.add("hidden");
                                            const textarea = textareaContainer.querySelector("textarea");
                                            if (textarea) {
                                                textarea.required = false;
                                                textarea.disabled = true;
                                            }
                                        }
                                    }
                                }
                                
                                // Aplicar a todos los checkboxes con áreas de texto
                                const checkboxes = document.querySelectorAll("input[type='checkbox'][data-has-textarea='true']");
                                checkboxes.forEach(checkbox => {
                                    // Configurar el estado inicial
                                    toggleTextarea(checkbox);
                                    
                                    // Añadir event listener para cambios
                                    checkbox.addEventListener("change", function() {
                                        toggleTextarea(this);
                                    });
                                });
                                
                                // Validación de formulario
                                const form = document.querySelector("form");
                                if (form) {
                                    form.addEventListener("submit", function(e) {
                                        let isValid = true;
                                        
                                        // Validar checkboxes obligatorios
                                        const requiredCheckboxes = form.querySelectorAll("input[type='checkbox'][required]");
                                        requiredCheckboxes.forEach(checkbox => {
                                            if (!checkbox.checked) {
                                                isValid = false;
                                                checkbox.closest(".flex.flex-col").classList.add("border-red-500", "bg-red-50");
                                            } else {
                                                checkbox.closest(".flex.flex-col").classList.remove("border-red-500", "bg-red-50");
                                            }
                                        });
                                        
                                        // Validar textareas visibles y obligatorios
                                        const requiredTextareas = form.querySelectorAll("textarea[required]");
                                        requiredTextareas.forEach(textarea => {
                                            // Solo validar textareas visibles
                                            if (textarea.offsetParent !== null && textarea.value.trim() === "") {
                                                isValid = false;
                                                textarea.classList.add("border-red-500", "bg-red-50");
                                            } else {
                                                textarea.classList.remove("border-red-500", "bg-red-50");
                                            }
                                        });
                                        
                                        if (!isValid) {
                                            e.preventDefault();
                                            // Desplazarse al primer error
                                            const firstError = form.querySelector(".border-red-500");
                                            if (firstError) {
                                                firstError.scrollIntoView({ behavior: "smooth", block: "center" });
                                            }
                                            alert("Por favor, complete todos los campos obligatorios marcados con *");
                                        }
                                    });
                                }
                            });
                            </script>
                            <?php
                        }
                    } catch (PDOException $e) {
                        // Silenciar errores para no interrumpir el flujo del usuario
                        error_log("Error al cargar checkboxes personalizados: " . $e->getMessage());
                    }  
                    ?>
                </div>
                
                <div class="text-center mt-6">
                    <button type="submit" class="text-white font-medium py-3 px-8 rounded-lg transition duration-300" style="background-color: var(--color-primary); border: none;">
                        Continuar con la reserva
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <footer class="text-center text-gray-500 text-sm py-4">
        <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
        <p class="mt-2">Realizada con ❤️ por <a href="https://impulsatelecom.com/" target="_blank" class="hover:text-gray-700 transition-colors duration-300">Impulsa Telecom</a></p>
    </footer>
    </div>
</body>
</html>
