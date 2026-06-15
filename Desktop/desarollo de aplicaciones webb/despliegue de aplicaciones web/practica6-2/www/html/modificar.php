<HTML>
  <HEAD>
   <TITLE>Web de consultas de psicologia</TITLE>
   <!-- <link rel="stylesheet" href="estilos.css"> -->
   <style>
	.botonEnviar {
  display: inline-block;
    padding: 10px 20px;
    margin-top: 20px; 
    
    text-decoration: none;
    border-radius: 5px;
    background-color:#683475 !important;
    /* color: white; */
}
.botonEnviar:hover{
  background-color: #835d8c !important; 
}
  *{
    background-color:#f8f9fa;
	font-family: Vegur, 'PT Sans', Verdana, sans-serif;
  background-image: url('fondo.jpg'); 
            background-size: cover; 
            background-repeat: no-repeat; 
            background-position: center; 
            background-attachment: fixed; 
            font-family: Vegur, 'PT Sans', Verdana, sans-serif;
			margin:0;
}

table {
        width: 50%; 
        border: 2px solid #683475; 
        margin: auto; 
		box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
			
        }
	h1{
    color:#4e3355;
	text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
	}
    th {
    /* background-color: #683475 !important; 
    color: white !important; */
    padding: 15px;
    text-align: left;
	}

	td{
		/* background-color:white; */
		height: 40px;
	}

	
.def{
	background-color:#683475 !important;
	/* color: white ; */
	width: 200px;
	text-align: left;
    padding: 10px;
	
}
input[type="number"],
        input[type="text"],
        select {
            width: 100%; 
            height: 30px; /
            box-sizing: border-box; 
            padding: 5px; 
            margin-bottom: 5px;
        }
   </style>
  </HEAD>
  <BODY>
   <center><h1>Modificar psicologo</h1></center>


<?php
	error_reporting(0);
   // me conecto a la BD
   include ('conexion.php');

   // Obtengo las variables
   $id = $_REQUEST["id"];

    $consulta = "SELECT * FROM psicologos WHERE NumColegiado='".$id."';";
    $res=mysqli_query($conexion,$consulta) or die("consulta incorrecta");
    $fila = mysqli_fetch_array($res);
?>

  <form method="get" action="modificar_guardar.php">
	<table border="1" align="center" cellpadding="3" cellspacing="0">
	  
	  <tr>
		<td class= "def"><div class= "def" align="right"><b>Nº Colegiado</b></div></td>
		<td><input name="NumColegiado" type="text" readonly  value="<?php echo $fila['NumColegiado'];?>"> </td>
	  </tr>
	  <tr>
		<td class= "def"><div class= "def" align="right"><b>DNI</b></div></td>
		<td><input name="DNI" type="text" value="<?php echo $fila['DNI'];?>"> </td>

	  </tr>
	  <tr>
		<td class= "def"><div class= "def" align="right"><b>Nombre</b></div></td>
		<td><input name="Nombre" type="text" value="<?php echo $fila['Nombre'];?>"> </td>
	  </tr>
	  <tr>
		<td class= "def"><div class= "def" align="right"><b>Primer Apellido</b></div></td>
		<td><input name="Apellido1" type="text" value="<?php echo $fila['Apellido1'];?>"> </td>
		</td>
	  </tr>
	  <tr>
		<td class= "def"><div class= "def" align="right"><b>Segundo Apellido</b></div></td>
		<td><input name="Apellido2" type="text" value="<?php echo $fila['Apellido2'];?>"> </td>
		</tr>
  <td class= "def"><div class= "def" align="right"><b>Año titulación</b></div></td>
  <td>
    <select name="AnioTitulacion">
      <?php
      for ($anio = 1879; $anio <= 2024; $anio++) {
        echo '<option value="' . $anio . '"';
        if ($fila['AnioTitulacion'] == $anio) {
          echo ' selected';
        }
        echo '>' . $anio . '</option>';
      }
      ?>
    </select>
  </td>
</tr>
		<td colspan=2>
               <div align="center">
		    <input class="botonEnviar" type="submit" name="Submit" value="Enviar" />
		  </div>
             </td>
	  </tr>
	</table>
  </form>
</BODY>

