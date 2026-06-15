import java.util.Scanner;

/**
 * proyecto
 */
public class proyecto {
    static void actualizarInterfaz(char m[][], int disparos, int disparosAcertados) {
        // Función que me actualiza toda la interfaz del juego.
        int c = 65;
        System.out.print("  ");

        for (int i = 0; i < m[0].length; i++) {
            System.out.print((char) c);
            System.out.print(" ");
            c++;
        }
        System.out.println();
        for (int i = 0; i < m.length; i++) {
            System.out.print(i);
            System.out.print(" ");

            for (int j = 0; j < m.length; j++) {

                System.out.print(m[i][j]);
                System.out.print("|");
            }
            System.out.println();

        }
        System.out.println("Número de disparos: " + disparos);
        System.out.println("Disparos acertados: " + disparosAcertados);

    }

    static boolean comprobarTablero(char mUsu[][], char mInterna[][], int fila, int columna, int disparosAcertados) {
        boolean encontrado = true;
        // Función que comprueba en el tablero interno si la coordenada puesto ha tocado
        // un barco o no.
        // PRE: Las coordenadas deben ser válidas.
        if (mInterna[fila][columna] == 'B') {
            mUsu[fila][columna] = 'x';

        } else {
            mUsu[fila][columna] = 'A';
            encontrado = false;
        }
        return encontrado;
    }

    static void mostrarMatriz(char m[][])
    // Función para mostrar una matriz
    {
        int c = 65;
        System.out.print("  ");

        for (int i = 0; i < m[0].length; i++) {
            System.out.print((char) c);
            System.out.print(" ");
            c++;
        }
        System.out.println();
        for (int i = 0; i < m.length; i++) {
            System.out.print(i);

            System.out.print(" ");

            for (int j = 0; j < m.length; j++) {

                System.out.print(m[i][j]);
                System.out.print("|");
            }
            System.out.println();

        }
    }

    static void rellenarMatriz(char m[][]) {

        for (int fil = 0; fil < m.length; fil++) {
            for (int col = 0; col < m.length; col++) {
                m[fil][col] = ' ';
            }

        }
    }

    public static void main(String[] args) {
        // boolean acertado = true; // Variable que uso para incrementar la variable
        // disparosAcertados.
        int disparosAcertados = 0; // Variable que uso para salir del bucle cuando se cumplan las condiciones.
        Scanner sc = new Scanner(System.in);
        int disparos = 2; // Numero de disparos que dispone el usuario
        char matrizUsuario[][] = new char[8][8];

        char matrizInterna[][] = { { 'B', 'B', 'B', 'B', ' ', ' ', ' ', ' ' },
                { ' ', ' ', ' ', ' ', ' ', ' ', 'B', ' ' }, { 'B', ' ', ' ', ' ', 'B', ' ', ' ', 'B' },
                { 'B', ' ', ' ', ' ', 'B', ' ', ' ', ' ' }, { 'B', ' ', ' ', ' ', 'B', ' ', ' ', ' ' },
                { ' ', ' ', ' ', ' ', ' ', ' ', 'B', ' ' }, { ' ', ' ', ' ', ' ', ' ', ' ', ' ', 'B' },
                { 'B', ' ', ' ', ' ', 'B', ' ', ' ', ' ' } };

        rellenarMatriz(matrizUsuario);

        mostrarMatriz(matrizInterna);

        int opcionUsuario;

        while (disparos < 0 || disparosAcertados != 16) {
            mostrarMatriz(matrizUsuario);
            System.out.println(" 1: Disparo normal");
            opcionUsuario = sc.nextInt();
            switch (opcionUsuario) {
                case 1:
                    System.out.println("Dime la fila a buscar (de 0 a 7)");
                    int fila = sc.nextInt();
                    System.out.println("Dime la columna a buscar (A a F)");
                    // columna = sc.next().toUpperCase();// Preguntar a Alejandro
                    int columna = sc.next().charAt(0) - 65;// le resto 65
                    System.out.println(columna);
                    // columna = Character.toUpperCase(columna);
                    System.out.println(columna);

                    if (comprobarTablero(matrizUsuario, matrizInterna, fila, columna,
                            disparosAcertados) == true) {
                        disparosAcertados++; // Si hemos tocado el barco subimos en 1 el número de
                        System.out.println("Tocado!!");
                        disparos--;
                    } else {
                        System.out.println("Agua!!!!!");
                    }
                    actualizarInterfaz(matrizUsuario, disparos, disparosAcertados);// Después de
                    // saber si es agua o
                    // tocado actualizo
                    // toda la interfaz.

                    break;
                case 2:// Proximamente
                    break;

            }

        }

        if (disparosAcertados == 16) {
            System.out.println("Ganaste");
        } else {
            System.out.println("Has perdido");
        }
    }

}
