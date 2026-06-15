import java.util.*;

/**
 * {1,1,1,2,9,9,9,8,8} {1}
 * {15} {15}
 * {1,1,1,2,2,5,5} {5}
 */
public class eje {

    public static void main(String[] args) {
        int tabla[] = { 1, 1, 1, 2, 2, 9, 2, 2 };
        System.out.println(finOdd(tabla));
    }

    static int finOdd(int[] t) {
        int repe = 0;// Variable para saber los numeros repetidos.
        int resul = 0;// variable que devuelvo con el resultado que pide el ejercicio.
        for (int cont = 0; cont < t.length; cont++) {// recorro toda la tabla.

            repe = 0;// vuelvo a inicializar a 0 despues de haber comparado el valor de la posicion 1
                     // con todos los
                     // demas

            for (int cont2 = 0; cont2 < t.length; cont2++) {
                if (t[cont] == t[cont2]) {// si el valor 1 es igual sumo uno al contador.

                    repe++;
                }
                if (repe % 2 != 0) {// si el valor es impar.......
                    resul = t[cont2];
                    break;

                }
            }

        }
        return resul;
    }
}
