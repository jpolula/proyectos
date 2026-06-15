<HTML>
  <HEAD>
   <TITLE>Web de consultas de psicologia</TITLE>
  </HEAD>
  <BODY>

<?php
include ('conexion.php');

   // Obtengo las variables
	$NumColegiado =$_REQUEST["NumColegiado"];
	$DNI = $_REQUEST["DNI"];
	$Nombre = $_REQUEST["Nombre"];
	$Apellido1 = $_REQUEST["Apellido1"];
	$Apellido2 = $_REQUEST["Apellido2"];
	$AnioTitulacion = $_REQUEST["AnioTitulacion"];



    $consulta = "INSERT INTO psicologos (NumColegiado, DNI, Nombre, Apellido1, Apellido2, AnioTitulacion) VALUES ('$NumColegiado', '$DNI', '$Nombre', '$Apellido1', '$Apellido2', '$AnioTitulacion');";
    $res=mysqli_query($conexion, $consulta) or die("consulta incorrecta");

    header("Location:index.php");
		
?>

