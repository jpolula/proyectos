<HTML>
  <HEAD>
   <TITLE>Web de consultas de psicologia</TITLE>
  </HEAD>
  <BODY>

<?php
include ('conexion.php');

   // Obtengo las variables
	$DNI = $_REQUEST["DNI"];
	$Nombre = $_REQUEST["Nombre"];
	$Apellido1 = $_REQUEST["Apellido1"];
	$Apellido2 = $_REQUEST["Apellido2"];
	$FechaNacimiento = $_REQUEST["FechaNacimiento"];
  $FechaPrimeraConsulta = $_REQUEST["FechaPrimeraConsulta"];
  $FechaUltimaConsulta = $_REQUEST["FechaUltimaConsulta"];
  $NumColegiado =$_REQUEST["NumColegiado"];



    $consulta = "INSERT INTO pacientes (DNI, Nombre, Apellido1, Apellido2, FechaNacimiento, FechaPrimeraConsulta, FechaUltimaConsulta, NumColegiado) VALUES ('$DNI', '$Nombre', '$Apellido1', '$Apellido2', '$FechaNacimiento', '$FechaPrimeraConsulta', '$FechaUltimaConsulta', '$NumColegiado');";
    $res=mysqli_query($conexion, $consulta) or die("consulta incorrecta");

    header("Location:index.php");
		
?>
