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


include ('conexion.php');

   $consulta = "SELECT * FROM psicologos;";
    $res=mysqli_query($conexion,$consulta) or die("consulta incorrecta");
        
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
        echo "  <td><a  style='color: black !important' href=modificar.php?id=".$fila['NumColegiado']."><span class='material-symbols-outlined'>edit_document</span></a>";
        echo "  <td><a  style='color: black !important' href=eliminar.php?id=".$fila['NumColegiado']."><span class='material-symbols-outlined'>delete</span></a>";
    echo "</tr>\n";
    }
    echo "<tr><td class='ultimaFila' colspan=6>";
    echo "<a class='botonAnadir' href=anadir.php>A&ntilde;adir psicologo </a>";
    echo "</td></tr></table>";
    echo "</div>";
    echo "</div>";

    //-----------------------------------------------------------------------------------------------------------------------------
    echo "<div class='tablaDivisoria'></div>";
    $consulta = "SELECT * FROM pacientes;";
    $res=mysqli_query($conexion,$consulta) or die("consulta incorrecta");
        
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
        echo "  <td><a  style='color: black !important' href=modificarPaciente.php?id=".$fila['DNI']."><span class='material-symbols-outlined'>edit_document</span></a>";
        echo "  <td><a  style='color: black !important' href=eliminarPaciente.php?id=".$fila['DNI']."><span class='material-symbols-outlined'>delete</span></a>";
    echo "</tr>\n";
    }
    echo "<tr><td class='ultimaFila' colspan=6>";
    echo "<a class='botonAnadir' href=anadirPaciente.php>A&ntilde;adir paciente </a>";
    echo "</td></tr></table>";
    echo "</div>";
    echo "</div>";

    //-----------------------------------------------------------------------------------------------------------------------------
    echo "<div class='tablaDivisoria'></div>";
    echo "<div style='text-align: center;'><a class='botonAnadir' href=consultar.php>Consultar</a></div>";
?>

