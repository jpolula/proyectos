import java.util.Scanner;

/**
 * Pedir 5 calificaciones de alumnos y decir al final si hay algún suspenso.
 */
public class eje14 {

    public static void main(String[] args) {
        int calificacion;
        int contSuspensos = 0;// Contador para los alumnos suspensos
        boolean aprobado = true;// Suponemos que no hay ningún suspenso, aún....

        Scanner sc = new Scanner(System.in);

        for (int cont = 0; cont < 5; cont++) {
            System.out.println("Dime una nota");
            calificacion = sc.nextInt();
            if (calificacion < 5) {
                aprobado = false;
                contSuspensos++;
            }
            if (calificacion < 0) {
                System.out.println("Nota erronea");// Si el usuario pone un número negativo saldrá un mensaje de error
            }
            System.out.println(aprobado);

        }
        System.out.println("El número de suspensos es " + contSuspensos);

    }
}