<?php
    require_once "bd.php";
    require_once "funciones.php";
    //require "sesiones.php";
    //comprobar_rol(['administrador']);
    $todasCategorias = cargarCategorias($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Categorías</title> 
    <link href="src/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body class="bg-gradient-to-br from-[#E0FAF4] via-[#72E8D4] to-[#51B2E0] flex flex-col items-center px-2 text-2xl">
    <div class="w-full flex flex-row items-start max-w-6xl">
        <a href="admin.php" class="w-auto flex flex-row items-center gap-2 bg-[#72B0E8] hover:bg-[#72E8AC] text-[#21476B] hover:text-white transition-all duration-200 px-8 py-4 rounded-2xl shadow-xl font-bold text-xl text-center border-2 border-[#72E8AC] group mt-8">
            <span class="material-symbols-outlined scale-150 group-hover:text-white transition-all duration-200">arrow_back</span>
            <span class="text-center group-hover:text-white transition-all duration-200">Volver</span>
        </a>
    </div>
    <div class="w-full max-w-6xl bg-[#E0FAF4]/95 border-4 border-[#72E8AC] rounded-[2.5rem] shadow-[0_8px_40px_0_rgba(81,178,224,0.18)] p-2 flex flex-col items-center backdrop-blur-xl">
        <h1 class="text-4xl font-bold text-[#256353] mb-12 w-full text-center">Gestión de Categorías</h1>
        <?php
            if (count($todasCategorias) > 0) 
            {
        ?>
        <table class="w-full text-left rounded-3xl overflow-hidden text-2xl shadow-xl border-separate border-spacing-0 mt-12">
            <tr class="bg-gradient-to-r from-[#72E8AC]/90 to-[#51E080]/80 text-[#256353]">
                <th class="py-7 px-10 font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Nombre</th>
                <th class="py-7 px-10 font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Descripción</th>
                <th class="py-7 px-10 text-center font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Acciones</th>
            </tr>
            <?php 
                foreach ($todasCategorias as $cat): 
            ?>
            <tr class="border-b last:border-b-0 hover:bg-[#72E8AC]/40 transition duration-200 group">
                <td class="py-7 px-10 font-semibold text-[#256353] text-2xl group-hover:scale-105 group-hover:text-[#51E080] transition-transform duration-200 bg-[#E0FAF4]/80">
                    <?= htmlspecialchars($cat['Nombre']) ?>
                </td>
                <td class="py-7 px-10 text-[#21476B] text-2xl group-hover:scale-105 group-hover:text-[#51E080] transition-transform duration-200 bg-[#E0FAF4]/80">
                    <?= 
                        htmlspecialchars($cat['Descripcion'])
                    ?>
                </td>
                <td class="py-7 px-10 flex flex-row gap-4 items-center justify-center text-3xl bg-[#E0FAF4]/80">
                    <a href="editarCategoria.php?id=<?= $cat['IdCategoria'] ?>" title="Editar" class="flex flex-col items-center justify-center gap-2 bg-[#72E8AC] hover:bg-[#72E884] text-[#256353] hover:text-white transition-all duration-200 px-4 py-3 rounded-2xl font-bold text-xl text-center border-2 border-[#72E884] group">
                      <span class="material-symbols-outlined scale-125 group-hover:text-white transition-all duration-200">edit</span>
                    
                    <a href="eliminarCategoria.php?id=<?= $cat['IdCategoria'] ?>" title="Borrar" class="flex flex-col items-center justify-center gap-2 bg-[#72B0E8] hover:bg-[#72E8AC] text-[#21476B] hover:text-white transition-all duration-200 px-4 py-3 rounded-2xl font-bold text-xl text-center border-2 border-[#72E8AC] group">
                      <span class="material-symbols-outlined scale-125 group-hover:text-white transition-all duration-200">delete</span>
                    
                </td>
            </tr>
            <?php 
                endforeach; 
            ?>
        </table>
        <div class="w-full flex justify-center mt-12">
            <a href="anadirCategoria.php" class="w-auto flex flex-row items-center justify-center gap-2 bg-[#72E8AC] hover:bg-[#72E884] text-[#256353] hover:text-white transition-all duration-200 px-8 py-4 rounded-2xl font-bold text-xl text-center border-2 border-[#72E884] group">
                <span class="material-symbols-outlined scale-125 group-hover:text-white transition-all duration-200">add_circle</span>
                <span class="text-center group-hover:text-white transition-all duration-200">Añadir categoría</span>
            </a>
        </div>
    </div>
        <?php 
            } else { 
        ?>
            <p class="text-center text-red-600 text-xl">No se pudieron cargar las categorías.</p>
        <?php 
        } 
        ?>
    </div>
</body>
</html>
<?php 
    $conexion->close(); 
?>
