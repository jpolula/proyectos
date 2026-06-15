<HTML>
  <HEAD>
   <TITLE>Web de consultas de psicologia</TITLE>
   <!-- <link rel="stylesheet" href="estilos.css"> -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<style>
    body{
        background-image: url('fondo.jpg'); 
                background-size: cover; 
                background-repeat: no-repeat; 
                background-position: center; 
                background-attachment: fixed; 
                font-family: Vegur, 'PT Sans', Verdana, sans-serif;
    }
        th {
        background-color: #683475; 
        color: white;
        padding: 15px;
        text-align: left;
    }
    h1{
        color:#4e3355;
    }
    .botonAnadir {
        display: inline-block;
        padding: 10px 20px;
        margin-top: 20px; 
        color: white;
        text-decoration: none;
        border-radius: 5px;
        background-color:#683475;
    }
    .botonAnadir:hover{
        background-color: #835d8c; 
    }
    .tablaPsicologos a,
            .tablaPacientes a {
                color: #683475 !important;
            }

            .tablaPsicologos a:hover,
            .tablaPacientes a:hover {
                color: #835d8c !important;
            }
            .tablaDivisoria {
                border-top: 2px solid #683475;
                margin-top: 20px;
                padding-top: 20px;
            }
</style>
  </HEAD>
  <BODY>

<?php
$consultaSQL = "";
$nombre = "";
$apellido1 = "";
$apellido2 = "";
$anioTitulacion = "";
// ---
$siNombre = false;
$siApellido1 = false;
$siApellido2 = false;
$siAnioTitulacion = false;
// --
if (isset($_GET['checkboxNombre']) && $_GET['checkboxNombre'] == 'valorDelCheckbox') {
    $nombre = $_GET['Nombre'];  
    $siNombre = true;
}
if (isset($_GET['checkboxApellido1']) && $_GET['checkboxApellido1'] == 'valorDelCheckbox') {
    $apellido1 = $_GET['Apellido1'];  
    $siApellido1 = true;
}
if (isset($_GET['checkboxApellido2']) && $_GET['checkboxApellido2'] == 'valorDelCheckbox') {
    $apellido2 = $_GET['Apellido2'];  
    $siApellido2 = true;
}
if (isset($_GET['checkboxAnioTitulacion']) && $_GET['checkboxAnioTitulacion'] == 'valorDelCheckbox') {
    $anioTitulacion = $_GET['AnioTitulacion'];  
    $siAnioTitulacion = true;
}

if ($siNombre) {
    $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre';";
    if ($siApellido1) {
        $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre' AND Apellido1= '$apellido1';";
        if ($siApellido2) {
            $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre' AND Apellido1= '$apellido1' AND Apellido2= '$apellido2';";
            if ($siAnioTitulacion) {
                $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre' AND Apellido1= '$apellido1' AND Apellido2= '$apellido2' AND AnioTitulacion= '$anioTitulacion';";
            }
        } elseif ($siAnioTitulacion) {
            $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre' AND Apellido1= '$apellido1' AND AnioTitulacion= '$anioTitulacion';";
        }
    } elseif ($siApellido2) {
        $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre' AND Apellido2= '$apellido2';";
        if ($siAnioTitulacion) {
            $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre' AND Apellido2= '$apellido2' AND AnioTitulacion= '$anioTitulacion';";
        }
    } elseif ($siAnioTitulacion) {
        $consultaSQL = "SELECT * FROM psicologos WHERE Nombre = '$nombre' AND AnioTitulacion= '$anioTitulacion';";
    }
} elseif ($siApellido1) {
    $consultaSQL = "SELECT * FROM psicologos WHERE Apellido1= '$apellido1';";
    if ($siApellido2) {
        $consultaSQL = "SELECT * FROM psicologos WHERE Apellido1= '$apellido1' AND Apellido2= '$apellido2';";
        if ($siAnioTitulacion) {
            $consultaSQL = "SELECT * FROM psicologos WHERE Apellido1= '$apellido1' AND Apellido2= '$apellido2' AND AnioTitulacion= '$anioTitulacion';";
        }
    } elseif ($siAnioTitulacion) {
        $consultaSQL = "SELECT * FROM psicologos WHERE Apellido1= '$apellido1' AND AnioTitulacion= '$anioTitulacion';";
    }
} elseif ($siApellido2) {
    $consultaSQL = "SELECT * FROM psicologos WHERE Apellido2= '$apellido2';";
    if ($siAnioTitulacion) {
        $consultaSQL = "SELECT * FROM psicologos WHERE Apellido2= '$apellido2' AND AnioTitulacion= '$anioTitulacion';";
    }
} elseif ($siAnioTitulacion) {
    $consultaSQL = "SELECT * FROM psicologos WHERE AnioTitulacion= '$anioTitulacion';";
} else {
    $consultaSQL = "SELECT * FROM psicologos";
}




include ('conexion.php');

        
// --------------------------------------------------------------------------
//    $consulta = "SELECT * FROM psicologos;";
    $res=mysqli_query($conexion,$consultaSQL) or die("consulta incorrecta");
        
    $n_filas = mysqli_num_rows ($res);
    
    echo "<center><h1> Listado de psicologos </h1></center>";

    echo "<table align=center>\n";
    echo "<tr bgcolor=#ffffaa>\n
        <th>Nº Colegiado</th>\n
        <th>DNI</th>\n
        <th>Nombre</th>\n
        <th>Primer Apellido</th>\n
        <th>Segundo Apellido</th>\n
        <th>Año titulación</th>\n
    </tr>\n";

    for($i=1; $i<=$n_filas; $i++)
    {
        $fila = mysqli_fetch_array($res);
        echo "<div class='contenedorGlobal'>";
        echo "<div class='tablaPsicologos'>";
        echo "<tr>\n";
        echo "  <td>".$fila['NumColegiado']."</td>\n";
        echo "  <td>".$fila['DNI']."</td>\n";
        echo "  <td>".$fila['Nombre']."</td>\n";
        echo "  <td>".$fila['Apellido1']."</td>\n";
        echo "  <td>".$fila['Apellido2']."</td>\n";
        echo "  <td>".$fila['AnioTitulacion']."</td>\n";
    echo "</tr>\n";
    }
    echo "<tr><td class='ultimaFila' colspan=6>";
    echo "<a class='botonAnadir' href=consultar.php>Volver a consultas</a>";
    echo "<br>";
    echo "<a class='botonAnadir' href=index.php>Volver a inicio</a>";
    echo "</td></tr></table>";
    echo "</div>";
    echo "</div>";
   

