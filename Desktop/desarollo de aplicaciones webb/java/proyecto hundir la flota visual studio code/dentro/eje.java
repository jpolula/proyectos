import java.util.Scanner;

/**
 * eje
 */
public class eje {

    public static void main(String[] args) {
        boolean centi = true; // Se supone que no me saldré de rango al colocar el barco
        Scanner sc = new Scanner(System.in);
        System.out.println("Dime el número de fila ");
        int fila = sc.nextInt();

        System.out.println("Dime la columna");
        int columna = sc.nextInt();
        char[][] m = new char[fila][columna];
        int tamañoBarco = 4;

        int fil = 3;
        int col = 2;
        // Condiciones para saber si puedo poner un barco de tamaño n en forma de
        // columna
        if (fil - (tamañoBarco - 1) < 0) {// Para saber si una columna hacia arriba se sale de rango.
            centi = false;
        }

        if (fil + (tamañoBarco - 1) > m[0].length - 1) {// Con esta condición miro si
            // puedo colocar el barco
            // hacia abajo en forma de columna
            centi = false;
        }
        // Condiciónes para saber si puedo poner un barco de tamaño n en forma de fila

        if (col - (tamañoBarco - 1) < 0) {// Con esta condición miro si puedo poner el barco en la izquierda
            centi = false;
        }

        if (col + (tamañoBarco - 1) > m.length - 1) {// Con esta condición miro si
            // puedo poner el barco en la derecha
            centi = false;

        }

        // Condiciones para saber si puedo colocar un barco en forma diagonal.
        if (fil - (tamañoBarco - 1) < 0 && col - (tamañoBarco - 1) < 0) {// con esta
            // condición miro si puedo poner el
            // barco en posiciendo diagonal hacia arriba
            centi = false;
        }
        // Con esta condición miro a ver si puedo colocar el barco de forma diagonal
        // hacia abajo.
        if (fil + (tamañoBarco - 1) > m.length - 1 && col + (tamañoBarco - 1) > m[0].length - 1) {
            centi = false;
        }

        // Condiciones para saber si puedo colocar un barco en diagonal inversa.
        if (fil - (tamañoBarco - 1) < m.length - 1 && col + (tamañoBarco - 1) > m[0].length - 1) {
            // Condición para versi puedo colocarel barco en diagonal inversa hacia arriba
            centi = false;
        }

        // Condición para saber si puedo poner un barco de forma diagonal inversa hacia
        // abajo
        if (fil + (tamañoBarco - 1) > m.length - 1 && col - (tamañoBarco - 1) < m[0].length - 1) {
            centi = false;
        }
        System.out.println(centi);
    }
}