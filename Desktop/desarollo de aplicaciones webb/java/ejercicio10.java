import java.util.ArrayList;
import java.util.List;
import java.util.Random;

/**
 * ejercicio10
 */
public class ejercicio10 {

    static List<Integer> eliminarRepetidos(List<Integer> lista) {
        List<Integer> listaSinRepetidos = new ArrayList<>();

        for (Integer i = 0; i < lista.size(); i++) {
            if (listaSinRepetidos.contains(lista.get(i)) == false) {
                listaSinRepetidos.add(lista.get(i));
            }
        }

        return listaSinRepetidos;
    }

    public static void main(String[] args) {
        List<Integer> lista = new ArrayList<>();

        for (int i = 0; i < 500; i++) {
            Random random = new Random();
            lista.add(random.nextInt(50) + 1);
        }

        System.out.println("Lista original:");
        for (Integer num : lista) {
            System.out.print(num + "|");
        }
        System.out.println();

        List<Integer> listaD = eliminarRepetidos(lista);

        System.out.println("Lista sin repetidos:");
        for (Integer num : listaD) {
            System.out.print(num + "|");
        }
    }
}