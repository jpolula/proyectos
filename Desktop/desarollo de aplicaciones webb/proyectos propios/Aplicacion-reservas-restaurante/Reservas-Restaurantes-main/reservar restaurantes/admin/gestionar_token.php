<?php
session_start();

// Verificar si el usuario está autenticado como administrador
// Ajusta esta verificación según tu sistema de autenticación actual
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Ruta al archivo de configuración
$configFile = '../api/config.php';
$configContent = file_get_contents($configFile);

// Mensaje de éxito/error
$message = '';

// Si se envió el formulario para actualizar el token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_token'])) {
    $nuevoToken = trim($_POST['nuevo_token']);
    
    // Validar que el token no esté vacío
    if (empty($nuevoToken)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>El token no puede estar vacío.</p>
                    </div>';
    } else {
        // Actualizar el token en el archivo de configuración
        $pattern = "/define\('API_TOKEN',\s*'.*?'\);/";
        $replacement = "define('API_TOKEN', '$nuevoToken');";
        $newContent = preg_replace($pattern, $replacement, $configContent);
        
        if (file_put_contents($configFile, $newContent)) {
            $message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                            <p>Token actualizado correctamente.</p>
                        </div>';
            $configContent = $newContent; // Actualizar el contenido mostrado
        } else {
            $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                            <p>Error al actualizar el token. Verifica los permisos de escritura.</p>
                        </div>';
        }
    }
}

// Extraer el token actual
preg_match("/define\('API_TOKEN',\s*'(.*?)'\);/", $configContent, $matches);
$tokenActual = isset($matches[1]) ? $matches[1] : 'No se pudo determinar';

// Generar un token aleatorio para sugerir
function generarTokenAleatorio($longitud = 32) {
    return bin2hex(random_bytes($longitud / 2));
}
$tokenSugerido = generarTokenAleatorio();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Token API</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-blue-600">
                <h1 class="text-white font-bold text-xl">Gestionar Token API</h1>
            </div>
            
            <div class="p-6">
                <?php echo $message; ?>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2">Token Actual</h2>
                    <div class="bg-gray-100 p-3 rounded flex items-center">
                        <span class="flex-grow font-mono text-sm break-all"><?php echo htmlspecialchars($tokenActual); ?></span>
                        <button onclick="copiarToken('token-actual')" class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-1 px-2 rounded text-xs">
                            Copiar
                        </button>
                    </div>
                    <input type="hidden" id="token-actual" value="<?php echo htmlspecialchars($tokenActual); ?>">
                </div>
                
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="nuevo_token" class="block text-gray-700 text-sm font-bold mb-2">Nuevo Token</label>
                        <input type="text" id="nuevo_token" name="nuevo_token" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                               value="<?php echo htmlspecialchars($tokenSugerido); ?>">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="button" onclick="generarNuevoToken()" 
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Generar Nuevo
                        </button>
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Actualizar Token
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 border-t pt-4">
                    <h2 class="text-lg font-semibold mb-2">Cómo usar el token</h2>
                    <p class="text-gray-700 text-sm mb-2">Incluye el token en las cabeceras HTTP de tus peticiones:</p>
                    <div class="bg-gray-100 p-3 rounded">
                        <code class="font-mono text-sm">
                            Authorization: Bearer <?php echo htmlspecialchars($tokenActual); ?>
                        </code>
                    </div>
                    
                    <p class="text-gray-700 text-sm mt-4 mb-2">Ejemplo con cURL:</p>
                    <div class="bg-gray-100 p-3 rounded">
                        <code class="font-mono text-sm break-all">
                            curl -X GET "http://localhost/reservar%20restaurantes/api/reservas" -H "Authorization: Bearer <?php echo htmlspecialchars($tokenActual); ?>"
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function copiarToken(id) {
            const tokenInput = document.getElementById(id);
            tokenInput.select();
            document.execCommand('copy');
            alert('Token copiado al portapapeles');
        }
        
        function generarNuevoToken() {
            // Generar un token aleatorio de 32 caracteres
            const caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let token = '';
            for (let i = 0; i < 32; i++) {
                token += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
            }
            document.getElementById('nuevo_token').value = token;
        }
    </script>
</body>
</html>
