<?php
// Script para solucionar rápidamente el problema de confirmar_reserva.php

// Crear una versión simplificada del archivo
$nuevo_contenido = '<?php
// Archivo confirmar_reserva.php - Página de confirmación intermedia
session_start();

// Verificar si hay datos de reserva en la sesión
if (!isset($_SESSION["fecha"]) || !isset($_SESSION["zona"]) || !isset($_SESSION["turno_id"])) {
    // Si no hay datos, redirigir al inicio
    header("Location: index.php");
    exit;
}

// Procesar la confirmación final
if (isset($_POST["confirmar"]) && $_POST["confirmar"] === "true" && isset($_POST["confirmar_datos"]) && $_POST["confirmar_datos"] === "on") {
    // Recoger todos los datos del formulario
    $fecha = $_POST["fecha"];
    $zona = $_POST["zona"];
    $turno_id = $_POST["turno_id"];
    $nombre = $_SESSION["nombre"];
    $email = $_SESSION["email"];
    $telefono = $_SESSION["telefono"];
    $num_personas = $_SESSION["num_personas"];
    $tiene_alergenos = isset($_POST["tiene_alergenos"]) ? $_POST["tiene_alergenos"] : 0;
    $alergenos = isset($_POST["alergenos"]) ? $_POST["alergenos"] : "";
    $tiene_necesidades = isset($_POST["tiene_necesidades"]) ? $_POST["tiene_necesidades"] : 0;
    $necesidades = isset($_POST["necesidades"]) ? $_POST["necesidades"] : "";
    
    try {
        // Conectar a la base de datos
        $pdo = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Verificar disponibilidad
        $hay_disponibilidad = true;
        
        // Verificar si el día está disponible para reservas
        $stmt = $pdo->prepare("SELECT * FROM dias_disponibles WHERE fecha = ? AND turno_id = ? AND zona = ?");
        $stmt->execute([$fecha, $turno_id, $zona]);
        $disponible = $stmt->fetch();
        
        if (!$disponible) {
            $_SESSION["error_reserva"] = "Lo sentimos, no hay disponibilidad para la fecha, zona y turno seleccionados.";
            header("Location: reserva.php");
            exit;
        }
        
        // Verificar aforo disponible
        $stmt = $pdo->prepare("SELECT * FROM capacidad WHERE fecha = ? AND turno_id = ? AND zona = ?");
        $stmt->execute([$fecha, $turno_id, $zona]);
        $capacidad = $stmt->fetch();
        
        if (!$capacidad) {
            $_SESSION["error_reserva"] = "No se ha configurado el aforo para la fecha seleccionada.";
            header("Location: reserva.php");
            exit;
        }
        
        // Contar personas ya reservadas
        $stmt = $pdo->prepare("SELECT SUM(num_personas) as total FROM reservas WHERE fecha = ? AND turno_id = ? AND zona = ? AND estado = \'confirmada\'");
        $stmt->execute([$fecha, $turno_id, $zona]);
        $resultado = $stmt->fetch();
        $personas_reservadas = $resultado["total"] ? (int)$resultado["total"] : 0;
        
        // Calcular aforo disponible
        $aforo_disponible = $capacidad["aforo_maximo"] - $personas_reservadas;
        
        if ($aforo_disponible < $num_personas) {
            $_SESSION["error_reserva"] = "Lo sentimos, no hay suficiente aforo disponible para el número de personas indicado.";
            header("Location: reserva.php");
            exit;
        }
        
        // Guardar la reserva
        $stmt = $pdo->prepare("INSERT INTO reservas (fecha, turno_id, zona, nombre, email, telefono, num_personas, observaciones, alergenos, necesidades_especiales, estado, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        // Determinar estado de la reserva
        $config_stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
        $config_stmt->execute();
        $config = $config_stmt->fetch();
        $max_personas_sin_aprobacion = $config["max_personas_sin_aprobacion"];
        
        $estado = ($num_personas > $max_personas_sin_aprobacion) ? "pendiente" : "confirmada";
        $observaciones = $tiene_alergenos ? "Alérgenos: " . $alergenos : "";
        
        $stmt->execute([
            $fecha,
            $turno_id,
            $zona,
            $nombre,
            $email,
            $telefono,
            $num_personas,
            $observaciones,
            $alergenos,
            $necesidades,
            $estado
        ]);
        
        // Limpiar variables de sesión relacionadas con la reserva
        unset($_SESSION["fecha"]);
        unset($_SESSION["zona"]);
        unset($_SESSION["turno_id"]);
        unset($_SESSION["nombre"]);
        unset($_SESSION["email"]);
        unset($_SESSION["telefono"]);
        unset($_SESSION["num_personas"]);
        unset($_SESSION["tiene_alergenos"]);
        unset($_SESSION["alergenos"]);
        unset($_SESSION["tiene_necesidades"]);
        unset($_SESSION["necesidades"]);
        
        // Redirigir a la página de confirmación
        $_SESSION["reserva_exitosa"] = true;
        header("Location: index.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION["error_reserva"] = "Error al procesar la reserva: " . $e->getMessage();
        header("Location: reserva.php");
        exit;
    }
}

// Obtener datos de la sesión
$fecha = $_SESSION["fecha"];
$zona = $_SESSION["zona"];
$turno_id = $_SESSION["turno_id"];
$nombre = $_SESSION["nombre"];
$email = $_SESSION["email"];
$telefono = $_SESSION["telefono"];
$num_personas = $_SESSION["num_personas"];
$tiene_alergenos = isset($_SESSION["tiene_alergenos"]) ? $_SESSION["tiene_alergenos"] : false;
$alergenos = isset($_SESSION["alergenos"]) ? $_SESSION["alergenos"] : "";
$tiene_necesidades = isset($_SESSION["tiene_necesidades"]) ? $_SESSION["tiene_necesidades"] : false;
$necesidades = isset($_SESSION["necesidades"]) ? $_SESSION["necesidades"] : "";

// Formatear la zona para mostrar
$zona_texto = $zona === "dentro" ? "Interior" : "Terraza";

// Obtener información del turno
try {
    $pdo = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->prepare("SELECT * FROM turnos WHERE id = ?");
    $stmt->execute([$turno_id]);
    $turno = $stmt->fetch();
    
    $turno_nombre = $turno["nombre"];
    $hora_inicio = $turno["hora_inicio"];
    $hora_fin = $turno["hora_fin"];
    
    // Formatear la fecha
    $fecha_obj = DateTime::createFromFormat("Y-m-d", $fecha);
    $fecha_formateada = $fecha_obj ? $fecha_obj->format("d/m/Y") : $fecha;
    
    // Obtener la hora seleccionada de la sesión
    $hora_seleccionada = isset($_SESSION["hora_seleccionada"]) ? $_SESSION["hora_seleccionada"] : $hora_inicio;
    
    // Formatear el horario
    $horario_especifico = substr($hora_seleccionada, 0, 5);
    $horario_turno = substr($hora_inicio, 0, 5) . " - " . substr($hora_fin, 0, 5);
    $horario = $horario_especifico . " (" . $horario_turno . ")";
    
} catch (Exception $e) {
    $turno_nombre = "No disponible";
    $horario = "No disponible";
    $fecha_formateada = $fecha;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Encabezado -->
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Confirmar Reserva</h1>
            <p class="text-gray-600">Por favor, revise los detalles de su reserva antes de confirmar</p>
        </header>
        
        <main class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Resumen de su reserva</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Datos del cliente -->
                    <div>
                        <h3 class="text-lg font-medium text-blue-700 mb-4 border-b border-gray-200 pb-2">Datos del cliente</h3>
                        <div class="space-y-3">
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
                            <p><strong>Número de personas:</strong> <?php echo htmlspecialchars($num_personas); ?></p>
                            
                            <?php if ($tiene_alergenos): ?>
                            <div class="mt-2">
                                <p><strong>Alérgenos:</strong></p>
                                <p class="text-sm bg-yellow-50 p-2 rounded border border-yellow-200"><?php echo nl2br(htmlspecialchars($alergenos)); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($tiene_necesidades): ?>
                            <div class="mt-2">
                                <p><strong>Necesidades especiales:</strong></p>
                                <p class="text-sm bg-blue-50 p-2 rounded border border-blue-200"><?php echo nl2br(htmlspecialchars($necesidades)); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Detalles de la reserva -->
                    <div>
                        <h3 class="text-lg font-medium text-blue-700 mb-4 border-b border-gray-200 pb-2">Detalles de la reserva</h3>
                        <div class="space-y-3">
                            <p><strong>Fecha:</strong> <?php echo htmlspecialchars($fecha_formateada); ?></p>
                            <p><strong>Turno:</strong> <?php echo htmlspecialchars($turno_nombre); ?></p>
                            <p><strong>Horario:</strong> <?php echo htmlspecialchars($horario); ?></p>
                            <p><strong>Zona:</strong> <?php echo htmlspecialchars($zona_texto); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-4">
                        <!-- Botón para volver atrás y modificar los datos -->
                        <form action="reserva.php" method="get" class="flex-1 max-w-xs mx-auto md:mx-0">
                            <button type="submit" class="w-full py-3 px-6 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-arrow-left mr-2"></i> Modificar datos
                            </button>
                        </form>
                        
                        <!-- Formulario para confirmar la reserva -->
                        <form action="confirmar_reserva.php" method="post" id="form_confirmar" class="flex-1 max-w-xs mx-auto md:mx-0">
                            <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">
                            <input type="hidden" name="zona" value="<?php echo htmlspecialchars($zona); ?>">
                            <input type="hidden" name="turno_id" value="<?php echo htmlspecialchars($turno_id); ?>">
                            <input type="hidden" name="tiene_alergenos" value="<?php echo $tiene_alergenos ? \'1\' : \'0\'; ?>">
                            <input type="hidden" name="alergenos" value="<?php echo htmlspecialchars($alergenos); ?>">
                            <input type="hidden" name="tiene_necesidades" value="<?php echo $tiene_necesidades ? \'1\' : \'0\'; ?>">
                            <input type="hidden" name="necesidades" value="<?php echo htmlspecialchars($necesidades); ?>">
                            <input type="hidden" name="confirmar" value="true">
                            
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="confirmar_datos" name="confirmar_datos" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="confirmar_datos" class="ml-2 block text-sm text-gray-900">
                                        Confirmo que todos los datos son correctos
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" id="btn_confirmar" class="w-full py-3 px-6 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Confirmar reserva <i class="fas fa-check ml-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Pie de página -->
        <footer class="mt-12 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date(\'Y\'); ?> Sistema de Reservas de Restaurantes</p>
        </footer>
    </div>
</body>
<script>
    // Script para habilitar/deshabilitar el botón de confirmar según el estado del checkbox
    document.addEventListener(\'DOMContentLoaded\', function() {
        const checkbox = document.getElementById(\'confirmar_datos\');
        const btnConfirmar = document.getElementById(\'btn_confirmar\');
        
        checkbox.addEventListener(\'change\', function() {
            btnConfirmar.disabled = !this.checked;
        });
        
        // Prevenir múltiples envíos del formulario
        const form = document.getElementById(\'form_confirmar\');
        form.addEventListener(\'submit\', function() {
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = \'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>\';
        });
    });
</script>
</html>';

// Hacer una copia de seguridad del archivo original
$backup_file = 'confirmar_reserva_backup_' . date('Y-m-d_H-i-s') . '.php';
copy('confirmar_reserva.php', $backup_file);

// Reemplazar el archivo con la nueva versión
file_put_contents('confirmar_reserva.php', $nuevo_contenido);

echo "<h1>Solución aplicada</h1>";
echo "<p>Se ha creado una copia de seguridad del archivo original en: $backup_file</p>";
echo "<p>Se ha reemplazado el archivo confirmar_reserva.php con una versión simplificada y funcional.</p>";
echo "<p><a href='confirmar_reserva.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Probar confirmar_reserva.php</a></p>";
?>
