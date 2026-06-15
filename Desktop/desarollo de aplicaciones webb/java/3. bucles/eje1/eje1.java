
/* Diseñar un programa que pida un número y diga  si es par si es positivo y su cuadrado. 
El proceso terminará cuando se ponga un 0*/
import java.util.Scanner;

public class eje1 {
    public static void main(String[] args) {
        boolean par, positivo;
        int numero;
        Scanner sc = new Scanner(System.in);
        System.out.println("Digame un número, para salir pulse 0");
        numero = sc.nextInt();

        while (numero != 0) {

            if (numero % 2 == 0) {
                par = true;
            } else {
                par = false;
            }
            if (numero > 0) {
                positivo = true;
            } else {
                positivo = false;
            }
            System.out.println("El número elegido es " + numero);
            System.out.println("¿El numero es par? " + par);
            System.out.println("El número es positivo " + positivo);
            System.out.println("El cuadrado del número elegido es " + numero * numero);
            System.out.println("Digame otro numero");
            numero = sc.nextInt();// Volvemos a leer otro número para no tener un bucle infinito.

        }
    }
}
