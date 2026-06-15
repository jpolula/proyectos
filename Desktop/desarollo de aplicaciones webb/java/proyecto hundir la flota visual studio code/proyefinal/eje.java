import java.util.Scanner;

/**
 * eje
 */
public class eje {

    // PRE: Necesario haber reservado memoria en la tabla.
    // Función que rellena una matriz de espacios en blanco para posteriormente
    // llenarla con carácteres.
    static void rellenarMatriz(char matriz[][]) {
        for (int fil = 0; fil < matriz.length; fil++) {
            for (int col = 0; col < matriz[0].length; col++) {
                matriz[fil][col] = ' '; // En cada coordenada que haga el bucle la coordenada tomará el valor ' '
            }
        }
    }

    static void mostrarMatriz(char matriz[][])
    // Función para mostrar una matriz de n filas y n columnas
    // PRE: La matriz tiene que ser válida.
    {
        int c = 65;// Usaré este número para hacerle un casting y que las columnas se me vean de la
                   // A a la Z
        System.out.print("  ");

        for (int i = 0; i < matriz[0].length; i++)// Echarle un ojo
        {

            System.out.print((char) c);// Hacemos un casting a la variable c (65 en el codigo ASCII simboliza la A,
                                       // 66 la B... etc)
            System.out.print(" ");
            c++;
        }
        System.out.println();
        for (int fil = 0; fil < matriz.length; fil++) {
            String str = String.format("%02d", fil); // Con este metodo añado un 0 a la izquierda para cuadrar la
                                                     // matriz
            System.out.print(str);
            for (int j = 0; j < matriz[0].length; j++) {
                System.out.print(matriz[fil][j]);
                System.out.print("|"); // Uso una tubería para separar un valor de otro
            }
            System.out.println(); // Salto de linea para la siguiente fila
        }
    }

    static boolean rellenarBarco(char matriz[][], int tamañoBarco)// Función que te rellena un barco de x
                                                                  // posiciones. //Preguntar a Alejandro si hace
                                                                  // falta pasar los argumentos fila y cplumna
    {
        int modF = 0;// Incrementa o decrementa el valor de la fila dependiendo de la dirección que
                     // vaya.
        int modC = 0;// Incrementa o decrementa el valor de la columna dependiendo de la dirección al
                     // que vaya el barco
        int numero = (int) (Math.random() * 8);// Numero indica la dirección a la cual voy a colocar el barco
        switch (numero) {

            case 0: // diagonal arriba
                modF = -1;
                modC = 1;
                break;
            case 1:// diagonal abajo
                modF = 1;
                modC = 1;
                break;
            case 2:// diagonal inversa arriba
                modF = -1;
                modC = 1;
                break;
            case 3:// diagonal inversa abajo
                modF = 1;
                modC = -1;
                break;
            case 4:// columna arriba
                modF = -1;
                modC = 0;
                break;
            case 5:// columna abajo
                modF = 1;
                modC = 0;
                break;
            case 6:// fila derecha
                modF = 0;
                modC = 1;
                break;
            case 7:// fila izquierda
                modF = 0;
                modC = -1;
        }

        int fila = (int) (Math.random() * matriz.length);// Número aleatorio que te da el valor de una fila.
        int columna = (int) (Math.random() * matriz[0].length);// número aleatorio que te da el valor de una
                                                               // columna.
        boolean seguir = true; // variable que utilizo para salirme del bucle si es false y para seguir si es
                               // true
        columna = columna + modC;// Variable que usaré para recorrer el bucle y buscar posición por posición y
                                 // mirar si el barco cumple las condiciones establecidas en el bucle
        fila = fila + modF; // Variable que usaré para recorrer un bucle posición por posición y mirar si el
                            // barco cumple las condiciones establecidas en el bucle

        int fil = fila;// Creo una copia de fila, que la usaré para rellenar el barco si todas las
                       // condiciones se cumplen
        int col = columna; // Creo una copia de columna, que la usaré para rellenar el barco si todas las
                           // condiciones se cumplen.

        for (int i = 0; i < tamañoBarco && seguir == true; i++)// Bucle que recorre un barco de n tamaño
        {
            System.out.println(fila + " " + columna);

            if (dentro(matriz, fila, columna) == false) { // si la posición del barco no esta dentro de la matriz no
                                                          // podemos seguir mirando esta posición por que ya no nos
                                                          // vale
                seguir = false;

            }
            if (seguir == true && agua(matriz, fila, columna) == false) // Si esa celda esta ocupada no podemos
                                                                        // seguir avanzando.
            {
                seguir = false;
            }

            if (seguir == true && adyacente(matriz, fila, columna) == false) // Si en una de las 8 posiciones hay un
                                                                             // barco tampoco podemos seguir.
            {
                seguir = false;
            }
            columna = columna + modC;
            fila = fila + modF;
        }
        // Después de habernos cerciorado que el barco se puede colocar lo colocamos
        for (int i = 0; i < tamañoBarco && seguir == true; i++) {
            matriz[fil][col] = 'B';
            fil = fil + modF;
            col = col + modC;
        }
        return seguir;
    }

    static boolean agua(char matriz[][], int fila, int columna)// Función que me permite saber si en la casilla
                                                               // indicada como argumento hay agua (' ')
    {
        boolean esAgua = true; // Suponemos que en la casilla donde estamos hay agua (' ')

        if (matriz[fila][columna] != ' ') {
            esAgua = false;
        }
        return esAgua;
    }

    // Función que me comprueba si una coordenada esta en el tablero //Preguntar a
    // PRE: La coordenada pasada como argumento deberá de estar vacia.
    static boolean dentro(char matriz[][], int fila, int columna) {
        boolean dentro = true; // Suponemos que esa posición esta dentro del tablero
        if (columna < 0 || columna >= matriz[0].length) { // Con esta condición miro si la coordenada se sale de la
                                                          // fila
            dentro = false;
        }

        if (fila < 0 || fila >= matriz.length) {// Con esta condición miro si la coordenada se sale de la columna
            dentro = false;
        }

        if (fila < 0 && columna < 0 || fila >= matriz.length - 1 && columna >= matriz[0].length) { // Con esta
                                                                                                   // condición miro
                                                                                                   // si la
                                                                                                   // coordenada se
                                                                                                   // sale de la
                                                                                                   // diagonal
            dentro = false;

        }

        if (fila >= matriz.length && columna >= matriz[0].length) { // Con esta condición miro si la coordenada se
                                                                    // sale de la diagonal inversa. /preguntar a
                                                                    // Alejandro
            dentro = false;
        }
        return dentro;
    }

    // Función que busca en una coordenada si tiene barcos alrededor.
    // PRE: La coordenada pasada como parámetro no puede salirse del tablero y no
    // puede estar ocupada por otro barco.
    static boolean adyacente(char matriz[][], int fila, int columna) {
        // Conforme una de estas condiciones sea cierta ya sabemos que no podemos
        // colocar el barco
        boolean dentro;
        boolean seguir = true; // Suponemos que la coordenada no tiene barcos alrededor (8 posiciones)
        if (dentro(matriz, fila - 1, columna) == true)
        // Llamo a la función de dentro para ver si esa celda esta dentro del tablero
        {
            if (matriz[fila - 1][columna] != ' ') // si esa celda esta dentro del tablero Comparo para saber si hay
                                                  // adyacencia hacia arriba y abajo
            {
                seguir = false;
            }
        }

        if (dentro(matriz, fila + 1, columna) == true) {
            if (matriz[fila + 1][columna] != ' ') {
                seguir = false;
            }
        }
        // Condición para saber si hay adyacencia hacia la izquierda y derecha
        if (dentro(matriz, fila, columna - 1) == true) {
            if (matriz[fila][columna - 1] != ' ') {
                seguir = false;
            }
        }

        if (dentro(matriz, fila, columna + 1) == true) {
            if (matriz[fila][columna + 1] != ' ') {
                seguir = false;
            }

        }

        // Condición para saber si hay adyacencia hacia la diagonal abajo y arriba
        if (dentro(matriz, fila + 1, columna + 1) == true) {
            if (matriz[fila + 1][columna + 1] != ' ') {
                seguir = false;
            }
        }

        if (dentro(matriz, fila - 1, columna - 1) == true) {
            if (matriz[fila - 1][columna - 1] != ' ') {
                seguir = false;
            }
        }

        // Condición para saber si hay adyacencia hacia la diagonal inversa abajo y
        // arriba.
        if (dentro(matriz, fila + 1, columna - 1) == true) {
            if (matriz[fila + 1][columna - 1] != ' ') {
                seguir = false;
            }
        }

        if (dentro(matriz, fila - 1, columna + 1) == true) {
            if (matriz[fila - 1][columna + 1] != ' ') {
                seguir = false;
            }

        }
        return seguir;
    }

    static boolean comprobarTablero(char mUsu[][], char mInterna[][], int fila, int columna) {
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

    static void actualizarInterfaz(char matriz[][], int disparos, int disparosAcertados)

    {

        mostrarMatriz(matriz);
    }

    public static void main(String[] args) {
        boolean seguir;

        Scanner sc = new Scanner(System.in);

        System.out.println("Dime cuantas filas quieres tener:  de 1 a 25");

        int fila = sc.nextInt();
        while (fila > 25 || fila <= 0) // Con este bucle me cercioro de que el usuario me ponga una fila válida
        {
            System.out.println("Dime cuantas filas quieres tener:  de 1 a 25");
            fila = sc.nextInt();
        }

        System.out.println("Dime las columnas que quieres tener: de 1 a 25");
        int columna = sc.nextInt();
        while (columna > 25 || columna <= 0) // Con este bucle me cercioro de que el usuario me pone una columna
                                             // válida
        {
            System.out.println("Dime cuantas columnas quieres tener:  de 1 a 25");
            fila = sc.nextInt();
        }

        char matrizInterna[][] = new char[fila][columna]; // Creamos otra matriz, que la usaremos para poner los
                                                          // barcos. Tendrá la misma longitud que la matriz del
                                                          // usuario.

        // rellenarMatriz(matrizUsu);

        // mostrarMatriz(matrizUsu);

        rellenarMatriz(matrizInterna);

        // BUSCAR
        // System.out.println("jyfgwejywflu");
        // int cont = 10;
        // seguir = false;
        // while (cont != 0 && seguir == false) {
        // seguir = rellenarBarco(matrizInterna, 5); // coloco el porataviones.
        // }
        seguir = adyacente(matrizInterna, 0, 0);
        System.out.println(seguir);
        mostrarMatriz(matrizInterna);

    }
}