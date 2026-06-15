<HTML>
  <HEAD>
   <TITLE>Web de consultas de psicologia</TITLE>
  </HEAD>
  <BODY>

<?php
   // me conecto a la BD
   include ('conexion.php');

   //obtengo el modelo
   $NumColegiado = $_REQUEST["id"];

   // Creo y construyo la consulta
   $consulta = "DELETE FROM psicologos WHERE NumColegiado='".$NumColegiado."';";
   $res=mysqli_query($conexion, $consulta) or die("consulta incorrecta");

   header("Location:index.php");		
?>

