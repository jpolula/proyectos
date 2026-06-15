/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.proyecto;

import java.util.Scanner;

/**
 * Juan Pedro Martínez Granados
 */
public class Proyecto {
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
        System.out.print("   ");

        for (int i = 0; i < matriz[0].length; i++) {

            System.out.print((char) c);// Hacemos un casting a la variable c (65 en el codigo ASCII simboliza la A, 66
                                       // la B... etc)
            System.out.print(" ");
            c++;
        }
        System.out.println();
        for (int fil = 0; fil < matriz.length; fil++) {
            String str = String.format("%02d", fil); // Con este metodo añado un 0 a la izquierda para cuadrar la matriz
            System.out.print(str);
            System.out.print(" ");
            for (int j = 0; j < matriz[0].length; j++) {
                System.out.print(matriz[fil][j]);
                System.out.print("|"); // Uso una tubería para separar un valor de otro
            }
            System.out.println(); // Salto de linea para la siguiente fila
        }
    }

    static boolean rellenarBarcos(char matriz[][], int tamañoBarco)// Función que te rellena un barco de x posiciones.
                                                                   // //Preguntar a Alejandro si hace falta pasar los
                                                                   // argumentos fila y cplumna
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
        int columna = (int) (Math.random() * matriz[0].length);// número aleatorio que te da el valor de una columna.
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
            if (dentro(matriz, fila, columna) == false) { // si la posición del barco no esta dentro de la matriz no
                                                          // podemos seguir mirando esta posición por que ya no nos vale
                seguir = false;

            }
            if (seguir == true && agua(matriz, fila, columna) == false) // Si esa celda esta ocupada no podemos seguir
                                                                        // avanzando.
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

    static boolean agua(char matriz[][], int fila, int columna)// Función que me permite saber si en la casilla indicada
                                                               // como argumento hay agua (' ')
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
        if (columna < 0 || columna >= matriz[0].length) { // Con esta condición miro si la coordenada se sale de la fila
            dentro = false;
        }

        if (fila < 0 || fila >= matriz.length) {// Con esta condición miro si la coordenada se sale de la columna
            dentro = false;
        }

        if (fila < 0 && columna < 0 || fila >= matriz.length - 1 && columna >= matriz[0].length) { // Con esta condición
                                                                                                   // miro si la
                                                                                                   // coordenada se sale
                                                                                                   // de la diagonal
            dentro = false;

        }

        if (fila >= matriz.length && columna >= matriz[0].length) { // Con esta condición miro si la coordenada se sale
                                                                    // de la diagonal inversa.
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
        boolean seguir = true; // Suponemos que la coordenada no tiene barcos alrededor (8 posiciones)
        if (dentro(matriz, fila - 1, columna) == true)// Llamo a la función de dentro para ver si esa celda esta dentro
                                                      // del tablero
        {
            if (matriz[fila - 1][columna] != ' ') // si esa celda esta dentro del tablero Comparo para saber si hay
                                                  // adyacencia hacia arriba
            {
                seguir = false;
            }
        }

        if (dentro(matriz, fila + 1, columna) == true) //// si esa celda esta dentro del tablero Comparo para saber si
                                                       //// hay adyacencia hacia abajo
        {
            if (matriz[fila + 1][columna] != ' ') {
                seguir = false;
            }
        }

        if (dentro(matriz, fila, columna - 1) == true)// // Condición para saber si hay adyacencia hacia la izquierda.
        {
            if (matriz[fila][columna - 1] != ' ') {
                seguir = false;
            }
        }

        if (dentro(matriz, fila, columna + 1) == true) // Condición para saber si hay adyacencia hacia la derecha
        {
            if (matriz[fila][columna + 1] != ' ') {
                seguir = false;
            }
        }

        // Condición para saber si hay adyacencia hacia la diagonal abajo
        if (dentro(matriz, fila + 1, columna + 1) == true) {
            if (matriz[fila + 1][columna + 1] != ' ') {
                seguir = false;
            }
        }
        // Condición para saber si hay adyacencia hacia la diagonal arriba
        if (dentro(matriz, fila - 1, columna - 1) == true) {
            if (matriz[fila - 1][columna - 1] != ' ') {
                seguir = false;
            }
        }

        // Condición para saber si hay adyacencia hacia la diagonal inversa abajo.
        if (dentro(matriz, fila + 1, columna - 1) == true) {
            if (matriz[fila + 1][columna - 1] != ' ') {
                seguir = false;
            }
        }
        // Condición para saber si hay adyacencia hacia la diagonal inversa arriba
        if (dentro(matriz, fila - 1, columna + 1) == true) {
            if (matriz[fila - 1][columna + 1] != ' ') {
                seguir = false;
            }
        }
        return seguir;
    }

    static void colocarTodosLosBarcos(char matriz[][]) // Función que me coloca todos los barcos si se cumplen todas las
                                                       // condiciones.
    // POS: Las celdas que tenián ' ' cambian su valor dependiendo de donde se
    // coloca el barco
    // PRE: Necesario poner un contador para que no haya bucle infinito en caso de
    // que en una iteración el barco no se pueda colocar.
    {
        int cont = 10;
        int numerosBarcos[] = { 4, 3, 3, 2, 2, 1, 1 };
        boolean seguir = false;
        for (int i = 0; i < numerosBarcos.length; i++) {

            while (cont != 0 && seguir == false) // mientras que no se coloque el barco o se nos acaben los intentos el
                                                 // bucle seguirá haciendo iteraciones
            {
                seguir = rellenarBarcos(matriz, numerosBarcos[i]);
                cont--;
            }
            if (seguir == true) {
                barcoColocado(numerosBarcos[i]);
            } else {
                barcoNoColocado(numerosBarcos[i]);
            }
            cont = 10;// Vuelvo a poner el contador en 10 para el siguiente barco.
            seguir = false; // Vuevo a poner la variable centinela a false para el siguiente barco
        }
    }

    // Uso estas funciones para no mezclar datos con salidas por pantalla
    static void barcoColocado(int tamañoBarco) // Función que sirve para decirle al usuario que un barco no se ha
                                               // colocado de manera correcta
    {
        System.out.println("El barco de tamaño " + tamañoBarco + " ha sido colocado satisfactoriamente");
    }

    static void barcoNoColocado(int tamañoBarco) // Función que sirve para decirle al usuario que un barco se ha
                                                 // colocado de manera correcta
    {
        System.out.println("El barco de tamaño " + tamañoBarco + " no ha sido posible colocarlo");
    }

    static int cantidadBalas(char matriz[][], int fila, int columna) // Función que usaré para saber el número de balas
                                                                     // de las que dispondrá el usuario.
    {
        int nf = matriz.length;
        int nc = matriz[0].length;
        int numBalas = 0; // Contador que usaré para saber el número de balas.
        for (int fil = 0; fil < nf; fil++) {
            for (int col = 0; col < nc; col++) // En cada iteración del bucle interno sumare 1 a la cantidad de balas
            {
                numBalas++;
            }
        }
        numBalas /= 3;
        return numBalas;
    }

    static int cantidadBlancos(char matriz[][]) // Función que usaré para saber el total de blancos tengo que tocar
    {
        int nf = matriz.length;
        int nc = matriz[0].length;
        int cantidadBlancos = 0; // Variable que uso para guardar los blancos a batir.
        for (int fil = 0; fil < nf; fil++) {
            for (int col = 0; col < nc; col++) {
                if (matriz[fil][col] == 'B') // Cada vez que un valor de la matriz sea B, incrementamos la variable
                                             // cantidadBlancos
                {
                    cantidadBlancos++;
                }
            }
        }
        return cantidadBlancos;
    }

    static int cantidadColumnas(int numColumnas)// Función que me permite decir desde que columna hasta que columna
                                                // puede utilizar el usuario
    {
        int col = 65;
        for (int i = 0; i < numColumnas; i++) {
            col++;
        }
        return col;
    }

    static boolean comprobarTablero(char mUsu[][], char mInterna[][], int fila, int columna) {
        boolean encontrado = true;
        // Función que comprueba en el tablero interno si la coordenada puesto ha tocado
        // un barco o no.
        // PRE: Las coordenadas deben ser válidas.
        // POST: la matriz tomará valores diferentes.
        if (mInterna[fila][columna] == 'B') {
            mUsu[fila][columna] = 'X';
            mInterna[fila][columna] = 'X';
        } else {
            mUsu[fila][columna] = 'A';
            mInterna[fila][columna] = 'A';
            encontrado = false;
        }
        return encontrado;
    }

    static void actualizarInterfaz(char matriz[][], int balasRestantes, int blancosAdar) // Función que me actualiza el
                                                                                         // juego conforma el usuario va
                                                                                         // poniendo coordenadas.
    {
        // Pre: Es necesario que el usuario haya puesto un mínimo de 1 coordenada.
        mostrarMatriz(matriz);
        System.out.println("Balas restantes: " + balasRestantes);
        System.out.println("Cantidad de barcos a dar: " + blancosAdar);
    }

    static int mostrarFilaOcolumna(char matrizusu[][], char matrizInterna[][], int opcion, int blancosAdar) // Función
                                                                                                            // que me
                                                                                                            // permite
                                                                                                            // mostrar
                                                                                                            // una fila
                                                                                                            // o una
                                                                                                            // columna y
                                                                                                            // mostrarla
                                                                                                            // al
                                                                                                            // usuario
    // PRE: El usuario deberá de disponer de balas sufucientes para poder
    // ejecutarlo.
    {

        if (opcion == 0) // Si sale la opción 0 mostraré una fila aleatoria
        {
            int fila = (int) (Math.random() * matrizusu.length);
            for (int col = 0; col < matrizusu.length; col++)// En cada iteración del bucle comprobará si hay agua o se
                                                            // ha tocado algún barco.
            {
                if (comprobarTablero(matrizusu, matrizInterna, fila, col) == true) {
                    blancosAdar--;
                }
            }
        }

        if (opcion == 1)// Si sale la opcion 1 mostraré una columna aleatoria.
        {
            int columna = (int) (Math.random() * matrizusu[0].length);
            for (int fil = 0; fil < matrizusu[0].length; fil++) // En cada iteración del bucle comprobará si hay agua o
                                                                // se ha tocado algún barco.
            {
                if (comprobarTablero(matrizusu, matrizInterna, fil, columna) == true) {
                    blancosAdar--;
                }
            }
        }
        return blancosAdar;
    }

    static int costeBarrena(char matriz[][], int opcion, int balas) // Función que me permitirá saber el coste de la
                                                                    // bomba barrena, ya sea una fila o una coliumna
    {
        if (opcion == 0) {
            balas -= (matriz.length + 2);
        }

        if (opcion == 1) {
            balas -= (matriz[0].length + 2);

        }

        return balas;
    }

    static void pista(char matrizUsu[][], char matrizInterna[][]) // Esta función me permitirá
    {
        int cont = 1; // Variable que usaré para salirme del bucle si se cumple la condición dada en
                      // el bucle.
        while (cont != 0) {
            int fila = (int) (Math.random() * matrizUsu.length);
            int columna = (int) (Math.random() * matrizUsu[0].length);
            if (matrizInterna[fila][columna] == 'B') {
                matrizUsu[fila][columna] = 'X';
                cont--;
            }
        }
    }

    static void bombaAtomica(char mUsu[][], char mInterna[][])
    // Función que me muestra la adyacencia de una posición aleatoria incluida la
    // posición actual.
    // POST: Los valores de la matriz del usuario cambiaran.
    {
        int fila = (int) (Math.random() * mUsu.length); // Genero un número aleatorio para saber el número de fila
        int columna = (int) (Math.random() * mUsu[0].length); // Genero un número aleatorio para saber el número de
                                                              // columna.
        System.out.println(fila + "" + columna);
        if (dentro(mUsu, fila, columna) == true) // No sería necesario usar la función de dentro en la casilla puesto
                                                 // que esta casilla estará dentro si o si.
        {
            comprobarTablero(mUsu, mInterna, fila, columna);
        }

        if (dentro(mUsu, fila - 1, columna) == true) // calculo si la adyacencia hacia arriba esta dentro del tablero
        {
            comprobarTablero(mUsu, mInterna, fila - 1, columna); // si hay adyacencia compruebo la coordenada en el
                                                                 // tablero.
        }

        if (dentro(mUsu, fila + 1, columna) == true) // calculo si la adyacencia hacia abajo esta dentro del tablero
        {
            comprobarTablero(mUsu, mInterna, fila + 1, columna); // si hay adyacencia compruebo la coordenada en el
                                                                 // tablero.
        }

        if (dentro(mUsu, fila, columna - 1) == true) // calculo si la adyacencia hacia la izquierda esta dentro del
                                                     // tablero
        {
            comprobarTablero(mUsu, mInterna, fila, columna - 1); // si hay adyacencia compruebo la coordenada en el
                                                                 // tablero.
        }

        if (dentro(mUsu, fila, columna + 1) == true) // calculo si la adyacencia hacia la derecha esta dentro del
                                                     // tablero
        {
            comprobarTablero(mUsu, mInterna, fila, columna + 1); // si hay adyacencia compruebo la coordenada en el
                                                                 // tablero.
        }

        if (dentro(mUsu, fila - 1, columna - 1) == true) // calculo si la adyacencia hacia la diagonal arriba esta
                                                         // dentro del tablero
        {
            comprobarTablero(mUsu, mInterna, fila - 1, columna - 1); // si hay adyacencia compruebo la coordenada en el
                                                                     // tablero.
        }

        if (dentro(mUsu, fila + 1, columna + 1) == true) // calculo si la adyacencia hacia la diagonal abajo esta dentro
                                                         // del tablero
        {
            comprobarTablero(mUsu, mInterna, fila + 1, columna + 1); // si hay adyacencia compruebo la coordenada en el
                                                                     // tablero.
        }

        if (dentro(mUsu, fila - 1, columna + 1) == true) // calculo si la adyacencia hacia la diagonal inversa arriba
                                                         // esta dentro del tablero
        {
            comprobarTablero(mUsu, mInterna, fila - 1, columna + 1); // si hay adyacencia compruebo la coordenada en el
                                                                     // tablero.
        }

        if (dentro(mUsu, fila + 1, columna - 1) == true) // calculo si la adyacencia hacia la diagonal inversa abajo
                                                         // esta dentro del tablero
        {
            comprobarTablero(mUsu, mInterna, fila + 1, columna - 1); // si hay adyacencia compruebo la coordenada en el
                                                                     // tablero.
        }
    }

    static void barcoPortada() {
        System.out.println("             / \\     ");
        System.out.println("            / _ \\    ");
        System.out.println("           | / \\ |   ");
        System.out.println("           ||   ||   ");
        System.out.println("           ||   ||   ");
        System.out.println("        ___|_____|___ ");
        System.out.println("      _|    HUNDIR_|_");
        System.out.println("     /  \\           /  \\");
        System.out.println("    /    \\____LA_____/    \\");
        System.out.println("   |  _    |     |    _  |");
        System.out.println("   | / \\   |  |  |   / \\ |");
        System.out.println("   ||   |  |  |  |   |   ||");
        System.out.println("   ||   |  |FLOTA_|   |   ||");
        System.out.println("   | \\_/            \\_/ |");
        System.out.println("~~~~~~~~~~~~~~~~~~~~~~~~~~~~");
    }

    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        barcoPortada();
        System.out.println("Dime cuantas filas quieres tener:  de 1 a 25");

        int fila = sc.nextInt();

        while (fila > 26 || fila <= 0) // Con este bucle me cercioro de que el usuario me ponga una fila válida
        {
            System.out.println("Dime cuantas filas quieres tener:  de 1 a 25");
            fila = sc.nextInt();
        }

        System.out.println("Dime las columnas que quieres tener: de 1 a 26");
        int columna = sc.nextInt();

        while (columna > 26 || columna <= 0) // Con este bucle me cercioro de que el usuario me pone una columna válida
        {
            System.out.println("Dime cuantas columnas quieres tener:  de 1 a 26");
            fila = sc.nextInt();
        }

        char matrizUsu[][] = new char[fila][columna];// Esta será la matriz que verá el usuario.

        char matrizInterna[][] = new char[fila][columna]; // Creamos otra matriz, que la usaremos para poner los barcos.
                                                          // Tendrá la misma longitud que la matriz del usuario.

        rellenarMatriz(matrizInterna); // Relleno de ' ' la matriz interna

        rellenarMatriz(matrizUsu); // Relleno de ' ' la matriz del usuario

        colocarTodosLosBarcos(matrizInterna); // Coloco todos los barcos en la matriz interna.
        mostrarMatriz(matrizInterna);
        int cantidadBalas = cantidadBalas(matrizUsu, fila, columna); // Guardo el valor de la cantidad de bala que
                                                                     // dispondremos para jugar.

        int cantidadBarcosATocar = cantidadBlancos(matrizInterna); // Variable que usare para contar las veces que le he
                                                                   // dado a los barcos.

        int opcionUsuario; // Con esta variable guardaré el valor de la opción que quiere el usuario.

        int fil;
        // Variables que usaré para las diferentes opciones del switch
        int col;

        mostrarMatriz(matrizUsu);
        System.out.println("Balas restantes " + cantidadBalas);
        System.out.println("Cantidad de blancos a dar " + cantidadBarcosATocar);

        while (cantidadBalas != 0 && cantidadBarcosATocar != 0) {
            System.out.println(" 1: Disparo normal ");
            System.out.println(" 2: Bomba barrena coste: tamaño de la fila o columna + 2 balas ");
            System.out.println(" 3: Bomba atómica  coste: 10 balas");
            System.out.println(" 4: Pista  coste: 15 balas");
            System.out.println(" 5: Flash  coste: 25 balas");
            opcionUsuario = sc.nextInt();

            switch (opcionUsuario) {
                case 1: // Utilizaré este case para un disparo normal.
                    do {
                        System.out.println(" Fila a buscar: " + " de " + 0 + " a " + (fila - 1));
                        fil = sc.nextInt();
                    } while (fila < 0);
                    do {
                        col = cantidadColumnas(columna); // Uso está función para que el usuario sepa desde que letra
                                                         // hasta que letra va su matriz.
                        // col = sc.next().toUpperCase();
                        System.out.println(" Fila a buscar: " + "de la A " + "a la " + (char) (col - 1));
                        col = sc.next().charAt(0) - 65; // Le resto 65 para que a la hora de buscar la coordenada me de
                                                        // valores de 0 a 24.
                    } while (fila < 0);

                    if (comprobarTablero(matrizUsu, matrizInterna, fil, col) == true) {
                        cantidadBarcosATocar--; // Si hemos tocado un barco bajaré la cantidad de blancos
                        System.out.println(" TOCADO!!!!!!");

                    } else {
                        System.out.println("AGUA!!!!!!!");
                    }
                    cantidadBalas--;
                    actualizarInterfaz(matrizUsu, cantidadBalas, cantidadBarcosATocar);
                    break;

                case 2: // Utilizaré ese case para la bomba barrena, que me mostrará toda una fila o una
                        // columna
                    if (cantidadBalas >= matrizUsu.length + 2 || cantidadBalas >= matrizUsu[0].length + 2) {
                        int opcion = (int) (Math.random() * 2);

                        cantidadBarcosATocar = mostrarFilaOcolumna(matrizUsu, matrizInterna, opcion,
                                cantidadBarcosATocar);

                        if (opcion == 0) // si sale el 0 como número aleatorio calcularé el coste de la fila.
                        {
                            cantidadBalas = costeBarrena(matrizUsu, opcion, cantidadBalas);
                        } else // si no sale el 0 calcularé el coste de la columna.
                        {
                            cantidadBalas = costeBarrena(matrizUsu, opcion, cantidadBalas);
                        }

                        actualizarInterfaz(matrizUsu, cantidadBalas, cantidadBarcosATocar);
                    } else {
                        System.out.println("No dispone de balas suficientes para realizar la bomba barrena");
                    }
                    break;

                case 3:
                    if (cantidadBalas >= 10) // Si el usuario dispone de 10 balas o más podrá usar la bomba atómica.
                    {
                        bombaAtomica(matrizUsu, matrizInterna);
                        actualizarInterfaz(matrizUsu, cantidadBalas, cantidadBarcosATocar);
                        cantidadBalas -= 10;
                    } else {
                        System.out.println("No dispone de suficientes balas para la bomba atómica");
                    }
                    break;

                case 4: // Opción que usaré para que el usuario vea una parte de un barco con un coste
                        // de 15 balas;
                    if (cantidadBalas >= 15) {
                        pista(matrizUsu, matrizInterna);
                        cantidadBalas -= 15;
                        actualizarInterfaz(matrizUsu, cantidadBalas, cantidadBarcosATocar);
                    } else {
                        System.out.println("No dispone de balas suficientes para usar la opción pista");
                    }

                    break;

                case 5:
                    break;

                default:
                    System.out.println("Opción erronea");
            }
        }
        if (cantidadBarcosATocar == 0) {
            System.out.println("HAS GANADO!!");
        } else {
            System.out.println("HAS PERDIDO!!!!!");
        }
    }
}
