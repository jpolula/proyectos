<?php
  //require 'sesiones.php';
  //comprobar_rol(["administrador"]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=devicrequire_oncee-width, initial-scale=1.0">
  <link href="src/output.css" rel="stylesheet">
  <title>Panel de Administración</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body class="bg-gradient-to-br from-[#E0FAF4] via-[#72E8D4] to-[#51B2E0] flex flex-col items-center px-2 text-2xl min-h-screen justify-center">
  <div class="w-full max-w-3xl bg-white/95 border-2 border-[#51B2E0] rounded-2xl shadow-xl p-8 flex flex-col items-center">
    <div class="flex flex-col items-center mb-8 w-full">
      <div class="flex flex-row items-center justify-center gap-8 sm:gap-12 mb-8 w-full flex-wrap">
        <img src="img/barMinero.png" alt="Logo del restaurante" class="h-36 sm:h-52 w-auto max-w-[80vw] sm:max-w-[240px] drop-shadow-xl">
        <img src="img/logo-proyecto.png" alt="Logo del proyecto" class="h-44 sm:h-60 w-auto max-w-[85vw] sm:max-w-[280px] drop-shadow-xl">
      </div>
    </div>
    <div class="w-full max-w-md flex flex-col gap-4 sm:gap-6 items-center">
      <a href="categorias.php" class="w-full flex flex-col items-center justify-center gap-2 bg-[#72E8AC] hover:bg-[#72E884] text-[#256353] hover:text-white transition-all duration-200 px-6 py-5 sm:px-10 sm:py-7 rounded-2xl shadow-xl font-bold text-xl sm:text-2xl text-center border-2 border-[#72E884] group">
        <span class="material-symbols-outlined scale-150 group-hover:text-white transition-all duration-200">category</span>
        <span class="w-full text-center group-hover:text-white transition-all duration-200">Categorías</span>
      </a>
      <a href="empleados.php" class="w-full flex flex-col items-center justify-center gap-2 bg-[#72B0E8] hover:bg-[#72E8AC] text-[#21476B] hover:text-white transition-all duration-200 px-6 py-5 sm:px-10 sm:py-7 rounded-2xl shadow-xl font-bold text-xl sm:text-2xl text-center border-2 border-[#72B0E8] group">
        <span class="material-symbols-outlined scale-150 group-hover:text-white transition-all duration-200">group</span>
        <span class="w-full text-center group-hover:text-white transition-all duration-200">Empleados</span>
      </a>
      <a href="productos.php" class="w-full flex flex-col items-center justify-center gap-2 bg-[#72E8D4] hover:bg-[#72D4BA] text-[#21706B] hover:text-white transition-all duration-200 px-6 py-5 sm:px-10 sm:py-7 rounded-2xl shadow-xl font-bold text-xl sm:text-2xl text-center border-2 border-[#72D4BA] group">
        <span class="material-symbols-outlined scale-150 group-hover:text-white transition-all duration-200">restaurant</span>
        <span class="w-full text-center group-hover:text-white transition-all duration-200">Productos</span>
      </a>
      <a href="menu.html" class="w-full flex flex-col items-center justify-center gap-2 bg-[#72B0E8] hover:bg-[#72E8AC] text-[#21476B] hover:text-white transition-all duration-200 px-6 py-5 sm:px-10 sm:py-7 rounded-2xl shadow-xl font-bold text-xl sm:text-2xl text-center border-2 border-[#72E8AC] group">
        <span class="material-symbols-outlined scale-150 group-hover:text-white transition-all duration-200">menu_book</span>
        <span class="w-full text-center group-hover:text-white transition-all duration-200">Ver Menú</span>
      </a>
      <a href="logout.php" class="w-full flex flex-col items-center justify-center gap-2 bg-[#72E8AC] hover:bg-[#72B0E8] text-[#256353] hover:text-white transition-all duration-200 px-6 py-5 sm:px-10 sm:py-7 rounded-2xl shadow-xl font-bold text-xl sm:text-2xl text-center border-2 border-[#72B0E8] mt-2 focus:outline-none focus:ring-4 focus:ring-[#72B0E8]/50 group">
        <span class="material-symbols-outlined scale-150 group-hover:text-white transition-all duration-200">logout</span>
        <span class="w-full text-center group-hover:text-white transition-all duration-200">Cerrar sesión</span>
      </a>
    </div>
  </div>
</body>
<?php
?>
</html>
