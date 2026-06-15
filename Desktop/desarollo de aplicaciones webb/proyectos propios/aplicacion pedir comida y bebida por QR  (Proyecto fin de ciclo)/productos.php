<?php
    require_once "bd.php";
    require_once "funciones.php";
    //require "sesiones.php";
    //comprobar_rol(["administrador"]); 
    $todosProductos = cargarProductos($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Productos</title> 
    <link href="src/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body class="bg-gradient-to-br from-[#E0FAF4] via-[#72E8D4] to-[#51B2E0] flex flex-col items-center px-2 text-2xl min-h-screen justify-center">
    <div class="w-full flex flex-row items-start max-w-6xl mb-8">
    <a href="admin.php" class="w-auto flex flex-row items-center gap-2 bg-[#72B0E8] hover:bg-[#72E8AC] text-[#21476B] hover:text-white transition-all duration-200 px-8 py-4 rounded-2xl shadow-xl font-bold text-xl text-center border-2 border-[#72E8AC] group mt-8">
        <span class="material-symbols-outlined scale-150 group-hover:text-white transition-all duration-200">arrow_back</span>
        <span class="text-center group-hover:text-white transition-all duration-200">Volver</span>
    </a>
</div>
    <div class="w-full max-w-6xl bg-[#E0FAF4]/95 border-4 border-[#72E8AC] rounded-[2.5rem] shadow-[0_8px_40px_0_rgba(81,178,224,0.18)] p-2 flex flex-col items-center backdrop-blur-xl">
        <h1 class="text-4xl font-bold text-[#256353] mb-12 w-full text-center">Gestión de Productos</h1>
        <?php 
            if (count($todosProductos) > 0) { 
        ?>
        <table class="w-full text-left rounded-3xl overflow-hidden text-2xl shadow-xl border-separate border-spacing-0 mt-12">
            <thead>
                <tr class="bg-gradient-to-r from-[#72E8AC]/90 to-[#51E080]/80 text-[#256353]">
                    <th class="py-7 px-10 font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Nombre</th>
                    <th class="py-7 px-10 font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Descripción</th>
                    <th class="py-7 px-10 font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Precio</th>
                    <th class="py-7 px-10 font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Categoría</th>
                    <th class="py-7 px-10 text-center font-bold uppercase tracking-widest border-b-4 border-[#51E080]">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    foreach ($todosProductos as $prod): 
                ?>
                <tr class="border-b last:border-b-0 hover:bg-[#72E8AC]/40 transition duration-200 group">
                    <td class="py-7 px-10 font-semibold text-[#256353] text-2xl group-hover:scale-105 group-hover:text-[#51E080] transition-transform duration-200 bg-[#E0FAF4]/80">
                        <?= htmlspecialchars($prod['Nombre']) ?>
                    </td>
                    <td class="py-7 px-10 text-[#21476B] text-2xl group-hover:scale-105 group-hover:text-[#51E080] transition-transform duration-200 bg-[#E0FAF4]/80">
                        <?= htmlspecialchars($prod['Descripcion']) ?>
                    </td>
                    <td class="py-7 px-10 text-[#21476B] text-2xl group-hover:scale-105 group-hover:text-[#51E080] transition-transform duration-200 bg-[#E0FAF4]/80">
                        <?= htmlspecialchars($prod['Precio']) ?> €
                    </td>
                    <td class="py-7 px-10 text-[#21476B] text-2xl group-hover:scale-105 group-hover:text-[#51E080] transition-transform duration-200 bg-[#E0FAF4]/80">
                        <?= htmlspecialchars($prod['NombreCategoria']) ?>
                    </td>
                    <td class="py-7 px-10 flex flex-row gap-4 items-center justify-center text-3xl bg-[#E0FAF4]/80">
                        <a href="editarProducto.php?id=<?= $prod['IdProducto'] ?>" title="Editar" class="flex flex-col items-center justify-center gap-2 bg-[#72E8AC] hover:bg-[#72E884] text-[#256353] hover:text-white transition-all duration-200 px-4 py-3 rounded-2xl font-bold text-xl text-center border-2 border-[#72E884] group">
                          <span class="material-symbols-outlined scale-125 group-hover:text-white transition-all duration-200">edit</span>
                        
                        <a href="eliminarProducto.php?id=<?= $prod['IdProducto'] ?>" title="Borrar" class="flex flex-col items-center justify-center gap-2 bg-[#72B0E8] hover:bg-[#72E8AC] text-[#21476B] hover:text-white transition-all duration-200 px-4 py-3 rounded-2xl font-bold text-xl text-center border-2 border-[#72E8AC] group">
                          <span class="material-symbols-outlined scale-125 group-hover:text-white transition-all duration-200">delete</span>
                        
                    </td>
                </tr>
                <?php 
                    endforeach; 
                ?>
            </tbody>
        </table>
        <?php
            } else { 
        ?>
            <p class="text-[#256353] text-2xl mt-8">No hay productos registrados.</p>
        <?php 
            } 
        ?>
        <div class="w-full flex justify-center mt-12">
            <a href="anadirProducto.php" class="w-auto flex flex-row items-center justify-center gap-2 bg-[#72E8AC] hover:bg-[#72E884] text-[#256353] hover:text-white transition-all duration-200 px-8 py-4 rounded-2xl font-bold text-xl text-center border-2 border-[#72E884] group">
                <span class="material-symbols-outlined scale-125 group-hover:text-white transition-all duration-200">add_circle</span>
                <span class="text-center group-hover:text-white transition-all duration-200">Añadir producto</span>
            </a>
        </div>
    </div>
</body>
</html>
