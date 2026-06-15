import java.util.*;

/**
 * if(j>j+1)
 */
public class eje {
    static void mostrarTabla(int t[]) {
        for (int i = 0; i < t.length; i++)// Este bucle sería igual a poner t.toString.
        {
            System.out.println(t[i]);

        }
    }

    static boolean encontrado(int tabla[], int num) {
        boolean encontrado = false;
        for (int i = 0; i < tabla.length && encontrado == false; i++) {
            if (tabla[i] == num) {
                encontrado = true;
            }

        }

        return encontrado;
    }

    public static void main(String[] args) {
        int tabla[] = new int[6];
        int cont = 0;
        while (cont < 6) {
            int num = (int) (Math.random() * 6 + 1);
            if (encontrado(tabla, num) == false) {
                tabla[cont] = num;
                cont++;

                System.out.println();
            }
            mostrarTabla(tabla);// Al mostrar la tabla se mostrarám varios 0 debido a que los números son
                                // iguales
        }
    }
}
