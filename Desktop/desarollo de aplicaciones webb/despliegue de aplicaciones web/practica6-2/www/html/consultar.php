<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web de consultas de psicologia</title>
    <!-- <link rel="stylesheet" href="estilos.css"> -->
    <style>
        * {
            background-color: #f8f9fa;
            font-family: Vegur, 'PT Sans', Verdana, sans-serif;
            background-image: url('fondo.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
        }

        table {
            width: 100%;
            border: 2px solid #683475;
            margin: auto;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        }

        h1 {
            color: #4e3355;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        th {
            /* background-color: #683475 !important; */
            /* color: white !important; */
            padding: 15px;
            text-align: left;
        }

        td {
            /* background-color: white; */
            height: 40px;
        }

        .def {
            background-color: #683475 !important;
            /* color: white ; */
            width: 200px;
            text-align: left;
            padding: 10px;
        }

        input[type="number"],
        input[type="text"],
        select {
            width: 100%;
            height: 30px;
            box-sizing: border-box;
            padding: 5px;
            margin-bottom: 5px;
        }

        .contenedor {
            display: flex;
        }

        .bloque {
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
        }

        .botonEnviar {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            text-decoration: none;
            border-radius: 5px;
            background-color: #683475 !important;
            /* color: white; */
        }

        .botonEnviar:hover {
            background-color: #835d8c !important;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <td>
            <center><h1>Psicólogos</h1></center>

            <?php
            error_reporting(0);
            // me conecto a la BD
            include('conexion.php');

            // Obtengo las variables
            $id = $_REQUEST["id"];

            $consulta = "SELECT * FROM psicologos WHERE NumColegiado='" . $id . "';";
            $res = mysqli_query($conexion, $consulta) or die("consulta incorrecta");
            $fila = mysqli_fetch_array($res);
            ?>

            <form method="get" action="consultaPsicologos.php">
                <table border="1" align="center" cellpadding="3" cellspacing="0">
                    <tr>
                        <td class="def"><div class="def" align="right"><b>Nombre </b><input type="checkbox" id="checkboxNombre"name="checkboxNombre" value="valorDelCheckbox"></div></td>
                        <td>
                            <input name="Nombre" type="text" value="<?php echo $fila['Nombre']; ?>"> 
                        </td>
                    </tr>
                    <tr>
                        <td class="def"><div class="def" align="right"><b>Primer Apellido </b><input type="checkbox"   id="checkboxApellido1" name="checkboxApellido1"  value="valorDelCheckbox">
                            </div></td>
                        <td><input name="Apellido1" type="text" value="<?php echo $fila['Apellido1']; ?>"> </td>
                    </tr>
                    <tr>
                        <td class="def"><div class="def" align="right"><b>Segundo Apellido </b><input type="checkbox"  id="checkboxApellido2"  name="checkboxApellido2"  value="valorDelCheckbox">
                            </div></td>
                        <td><input name="Apellido2" type="text" value="<?php echo $fila['Apellido2']; ?>"> </td>
                    </tr>
                    <tr>
                        <td class="def"><div class="def" align="right"><b>Año titulación </b><input type="checkbox" id="checkboxAnioTitulacion" name="checkboxAnioTitulacion" value="valorDelCheckbox">
                            </div></td>
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
                    
                    <tr>
                        <td colspan="2">
                            <div align="center">
                            <input class="botonEnviar" type="submit" name="SubmitPsicologos" value="Consultar"/>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </td>
        <!-- ------------------------------------------------- -->
        <td>
            <center><h1>Pacientes</h1></center>

            <?php
            error_reporting(0);
            // me conecto a la BD
            include('conexion.php');

            // Obtengo las variables
            $id = $_REQUEST["id"];

            $consulta = "SELECT * FROM pacientes WHERE DNI='" . $id . "';";
            $res = mysqli_query($conexion, $consulta) or die("consulta incorrecta");
            $fila = mysqli_fetch_array($res);
            ?>

            <form method="get" action="consultaPacientes.php">
                <table border="1" align="center" cellpadding="3" cellspacing="0">
                    <tr>
                        <td class="def"><div class="def" align="right"><b>Nombre </b><input type="checkbox" id="checkboxNombre" name="checkboxNombre" value="valorDelCheckbox"></div></td>
                        <td><input name="Nombre" type="text" value="<?php echo $fila['Nombre']; ?>"> </td>
                    </tr>
                    <tr>
                        <td class="def"><div class="def" align="right"><b>Primer Apellido </b><input type="checkbox" id="checkboxApellido1" name="checkboxApellido1" value="valorDelCheckbox"></div></td>
                        <td><input name="Apellido1" type="text" value="<?php echo $fila['Apellido1']; ?>"> </td>
                    </tr>
                    

                    <tr>
                        <td class="def"><div class="def" align="right"><b>Mes Primera Consulta </b><input type="checkbox"  id="checkboxPrimeraConsulta"  name="checkboxPrimeraConsulta" value="valorDelCheckbox"></div></td>
                        <td>
                        <select name="AnioSeleccionado">
                                <?php
                                for ($anio = 1879; $anio <= 2024; $anio++) {
                                    echo '<option value="' . $anio . '"';
                                    if ($fila['AnioSeleccionado'] == $anio) {
                                        echo ' selected';
                                    }
                                    echo '>' . $anio . '</option>';
                                }
                                ?>
                         </select>
                        <select name="MesSeleccionado">
                                <?php
                                    // Array con los nombres de los meses
                                    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                    foreach ($meses as $numeroMes => $nombreMes) {
                                        echo '<option value="' . $numeroMes . '"';
                                        if ($numeroMes == $mesSeleccionado) {
                                            echo ' selected';
                                        }
                                        echo '>' . $nombreMes . '</option>';
                                    }
                                ?>
                            </select> 
                                </td>
                    </tr>
                    

                    <tr>
                        <td colspan="2">
                            <div align="center">
                            <input class="botonEnviar" type="submit" name="SubmitPacientes" value="Consultar"/>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>

    <!--  -->
    <tr>
        <td colspan="2">
            <center><h1>Psicólogos-Pacientes</h1></center>
            <form method="get" action="consultaPsicologosPacientes.php">
                <table>
                <tr>
                        <td class="def"><div class="def" align="right"><b>Inserte Nº Colegiado</b><input type="checkbox" id="checkboxID" name="checkboxID" value="valorDelCheckbox"></div></td>
                        <td>
                            <select name="NumColegiado">
                                <?php
                                include('conexion.php');
                                $consulta = "SELECT NumColegiado FROM psicologos;";
                                $res = mysqli_query($conexion, $consulta) or die("Consulta incorrecta");
                                while ($fila = mysqli_fetch_array($res)) {
                                    echo "<option value='" . $fila['NumColegiado'] . "'>" . $fila['NumColegiado'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <td>
                    
                    <tr>
                        <td colspan="2">
                            <div align="center">
                            <input class="botonEnviar" type="submit" name="SubmitPacientes" value="Consultar"/>
                            </div>
                        </td>
                    </tr>           
                </table>
            </form>
        </td>
    
    </tr>
</table>

</body>
</html>
