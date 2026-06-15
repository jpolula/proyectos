import java.util.Scanner;

/**
 * Pedir un número y calcular su factorial.
 */
public class eje12 {

    public static void main(String[] args) {
        int numero;
        int factorial = 1;// Inicializo a 1 para que pueda multiplicar en el bucle
        Scanner sc = new Scanner(System.in);
        System.out.println("Dime un número para calcular su factorial");
        numero = sc.nextInt();
        for (int cont = numero; cont > 0; cont--) {// n*n-1,n*n-2.....
            factorial = (factorial * cont);// Almaceno los valores
        }
        System.out.println(" El factorial de " + numero + " es " + factorial);
    }
}