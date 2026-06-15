
/*Implementar una aplicacion para calcular datos estadisticos de los alumnos de un centro educativo. 
Se introduciran datos hasta que uno de ellos sea negativo, y se pide:
 * La suma de todas las edades introducidas.-------
 * La media 
 * El numero de alumnos---------
 * Cuantos son mayor de edad.------
 */
import java.util.Scanner;

public class eje2 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        int suma = 0, edad;
        double media;
        int contMayor18 = 0;// Guardar a los alumnos que tengan > de 18
        int numAlumnos = 0;// Contador de los alumnos.
        System.out.println("Digame la nota de un alumno");
        edad = sc.nextInt();
        while (edad >= 0) {
            suma += edad;// Guardo el valor del numero que han puesto por teclado.
            numAlumnos++;// Sumo 1 al contador de los alumnos totales
            if (edad >= 18) {
                contMayor18++;
            }
            System.out.println("Digame la edad de otro alumno");
            edad = sc.nextInt();// Añadimos otra edad antes de salir del bucle.
        }
        media = suma / numAlumnos;
        System.out.println("La cantidad de alumnos es " + numAlumnos);
        System.out.println("La suma total de edades es " + suma);
        System.out.println("La cantidad de alumnos mayores de edad son " + contMayor18);
        System.out.println("La media es " + media);

    }
}