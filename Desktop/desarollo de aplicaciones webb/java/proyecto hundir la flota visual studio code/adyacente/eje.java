public class eje {
    // Función que busca en una coordenada si tiene barcos alrededor.
    // PRE: La coordenada pasada como parámetro no puede salirse del tablero y no
    // puede estar ocupada por otro barco.
    static boolean adyacente(char matriz[][], int fila, int columna) {
        // Conforme una de estas condiciones sea cierta ya sabemos que no podemos
        // colocar el barco
        boolean seguir = true; // Suponemos que la coordenada no tiene barcos alrededor (8 posiciones)

        // Condición para saber si hay adyacencia hacia arriba y abajo
        if (matriz[fila - 1][columna] != ' ' || matriz[fila + 1][columna] != ' ') {
            seguir = false;
        }

        // Condición para saber si hay adyacencia hacia la izquierda y derecha
        if (matriz[fila][columna - 1] != ' ' || matriz[fila][columna + 1] != ' ') {

            seguir = false;
        }

        // Condición para saber si hay adyacencia hacia la diagonal abajo y arriba
        if (matriz[fila + 1][columna + 1] != ' ' || matriz[fila - 1][columna - 1] != ' ') {

            seguir = false;
        }

        // Condición para saber si hay adyacencia hacia la diagonal inversa abajo y
        // arriba.
        if (matriz[fila + 1][columna - 1] != ' ' || matriz[fila - 1][columna + 1] != ' ') {
            seguir = false;
        }

        return seguir;
    }

    // Función que me comprueba si una coordenada esta en el tablero //Preguntar a
    // PRE: La coordenada pasada como argumento deberá de estar vacia.

    static boolean dentro(char matriz[][], int fila, int columna) {
        boolean dentro = true; // Suponemos que esa posición esta dentro del tablero
        if (columna < 0 || columna > matriz.length - 1) { // Con esta condición miro si la coordenada se sale de la fila
            dentro = false;
        }

        if (fila < 0 || fila > matriz[0].length - 1) {// Con esta condición miro si la coordenada se sale de la columna
            dentro = false;
        }

        if (fila < 0 && columna < 0 || fila > matriz.length - 1 && columna > matriz[0].length - 1) {
            // Con esta condición miro si la coordenada se sale de la diagonal
            dentro = false;
        }

        if (fila > matriz.length - 1 && columna > matriz[0].length - 1) {
            dentro = false;
        }
        return dentro;
    }

    public static void main(String[] args) {

        boolean seguir = true;
        char matrizInterna[][] = { { ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' },
                { ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' }, { ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' },
                { ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' }, { ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' },
                { ' ', ' ', ' ', ' ', ' ', ' ', 'B', ' ' }, { ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' },
                { ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ' } };
        matrizInterna[1][5] = 'B';
        int fila = 1;
        int columna = 5;
        int tamañoBarco = 4;
        for (int i = 0; i < tamañoBarco && seguir == true; i++) {
            seguir = dentro(matrizInterna, fila, columna);
            System.out.println(seguir);
            fila--;
            columna++;
        }
    }
}