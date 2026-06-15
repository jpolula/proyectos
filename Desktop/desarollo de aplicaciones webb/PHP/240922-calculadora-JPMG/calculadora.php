<!DOCTYPE html>
<html lang="en">
    <body>
        <?php
            $edad1=$_REQUEST["valor1"]; //GUardo el valor 1 en una variable
            $edad2=$_REQUEST["valor2"]; //gurado el valor 2 en una variable
            $operacion=$_REQUEST["operacion"]; //guardo la variable del select

            $resultado; //Creo una variable donde guardare el resultado de la opracion dependiendo del caso 
            switch ($operacion) 
            {
                case 'suma':
                    $resultado=$edad1+$edad2;
                    print("La suma de los dos valores es : $resultado");
                    break;

                case 'resta':
                    $resultado=$edad1-$edad2;
                    print("La resta de los dos valores es : $resultado");
                    break;
                
                case 'multiplicacion':
                    $resultado=$edad1*$edad2;
                    print("La multiplicacion de los dos valores es : $resultado");
                    break;

                case 'division':
                    $resultado=$edad1/$edad2;
                    print("La division de los dos valores es : $resultado");
                    break;

                default:
                    print("no se puede realizar la operacion");
                    break;
            }
        ?>
    </body>
</html>