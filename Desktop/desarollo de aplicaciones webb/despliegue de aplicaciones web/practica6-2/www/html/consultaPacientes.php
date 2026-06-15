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
$anioSeleccionado = "";
$mesSeleccionado = "";
$fechaPrimeraConsulta = "";
// ---
$siNombre = false;
$siApellido1 = false;
$siFechaPrimeraConsulta = false;
// --
if (isset($_GET['checkboxNombre']) && $_GET['checkboxNombre'] == 'valorDelCheckbox') {
    $nombre = $_GET['Nombre'];  
    $siNombre = true;
}
if (isset($_GET['checkboxApellido1']) && $_GET['checkboxApellido1'] == 'valorDelCheckbox') {
    $apellido1 = $_GET['Apellido1'];  
    $siApellido1 = true;
}

if (isset($_GET['checkboxPrimeraConsulta']) && $_GET['checkboxPrimeraConsulta'] == 'valorDelCheckbox') {
    $anioSeleccionado = $_GET['AnioSeleccionado'];
    $mesSeleccionado = $_GET['MesSeleccionado'];
    $mesSeleccionado++;
    $siFechaPrimeraConsulta = true;

    if($mesSeleccionado<10){
        //añadirle un 0 antes del mes
        $fechaPrimeraConsulta = $anioSeleccionado. "-0". $mesSeleccionado."%";
    }
    else{
        $fechaPrimeraConsulta = $anioSeleccionado. "-". $mesSeleccionado."%";
    }
    


}



if ($siNombre) {
    $consultaSQL = "SELECT * FROM pacientes WHERE Nombre = '$nombre';";
    if ($siApellido1) {
        $consultaSQL = "SELECT * FROM pacientes WHERE Nombre = '$nombre' AND Apellido1= '$apellido1';";
        if ($siFechaPrimeraConsulta) {
            $consultaSQL = "SELECT * FROM pacientes WHERE Nombre = '$nombre' AND Apellido1= '$apellido1' AND FechaPrimeraConsulta LIKE '$fechaPrimeraConsulta';";
        }
    } elseif ($siFechaPrimeraConsulta) {
        $consultaSQL = "SELECT * FROM pacientes WHERE Nombre = '$nombre' AND FechaPrimeraConsulta LIKE '$fechaPrimeraConsulta';";
    }
} elseif ($siApellido1) {
    $consultaSQL = "SELECT * FROM pacientes WHERE Apellido1= '$apellido1';";
    if ($siFechaPrimeraConsulta) {
        $consultaSQL = "SELECT * FROM pacientes WHERE Apellido1= '$apellido1' AND FechaPrimeraConsulta LIKE '$fechaPrimeraConsulta';";
    }
} elseif ($siFechaPrimeraConsulta) {
    $consultaSQL = "SELECT * FROM pacientes WHERE FechaPrimeraConsulta LIKE '$fechaPrimeraConsulta';";
} else {
    $consultaSQL = "SELECT * FROM pacientes";
}





include ('conexion.php');

        
// --------------------------------------------------------------------------
//    $consulta = "SELECT * FROM psicologos;";
    $res=mysqli_query($conexion,$consultaSQL) or die("consulta incorrecta");
        
    $n_filas = mysqli_num_rows ($res);
    
    echo "<center><h1> Listado de pacientes </h1></center>";

    echo "<table align=center>\n";
    echo "<tr bgcolor=#ffffaa>\n
        <th>DNI</th>\n
        <th>Nombre</th>\n
        <th>Primer Apellido</th>\n
        <th>Segundo Apellido</th>\n
        <th>Fecha de nacimiento</th>\n
        <th>Fecha Primera Consulta</th>\n
        <th>Fecha Última Consulta</th>\n
        <th>Nº Colegiado Psicólogo</th>\n
    </tr>\n";

    for($i=1; $i<=$n_filas; $i++)
    {
        $fila = mysqli_fetch_array($res);
        echo "<div class='contenedorGlobal'>";
        echo "<div class='tablaPsicologos'>";
        echo "<tr>\n";
        echo "  <td>".$fila['DNI']."</td>\n";
        echo "  <td>".$fila['Nombre']."</td>\n";
        echo "  <td>".$fila['Apellido1']."</td>\n";
        echo "  <td>".$fila['Apellido2']."</td>\n";
        echo "  <td>".$fila['FechaNacimiento']."</td>\n";
        echo "  <td>".$fila['FechaPrimeraConsulta']."</td>\n";
        echo "  <td>".$fila['FechaUltimaConsulta']."</td>\n";
        echo "  <td>".$fila['NumColegiado']."</td>\n";
        
    echo "</tr>\n";
    }
    echo "<tr><td class='ultimaFila' colspan=6>";
    echo "<a class='botonAnadir' href=consultar.php>Volver a consultas</a>";
    echo "<br>";
    echo "<a class='botonAnadir' href=index.php>Volver a inicio</a>";
    echo "</td></tr></table>";
    echo "</div>";
    echo "</div>";