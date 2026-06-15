package codigo;

import java.util.Arrays;

public class Main {

    public static void main(String[] args) {
        int aux[] = { 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 };
        System.out.println("Tabla original: " + Arrays.toString(aux));

        desordenar(aux);
        System.out.println("Tabla desordenada: " + Arrays.toString(aux));
    }

    static void desordenar(int t[]) {
        for (int i = 0; i < t.length; i++) {
            int indice1 = (int) (Math.random() * t.length);
            int indice2 = (int) (Math.random() * t.length);

            int aux = t[indice1];// aux vale 8 i1=8 i2=9 8,9
            // indice 1 vale
            t[indice1] = t[indice2];//
            t[indice2] = aux;
        }
    }
}
