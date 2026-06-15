<?php
// Funcion que me comprueba si hemos iniciado sesión como administrador. SI no somos administrador nos redirige al login
function comprobar_rol($rolesPermitidos = []) {
    session_start();
    if (!isset($_SESSION["usuario"]) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header("Location: login.php?redirigido=true");
        exit();
    }
}

