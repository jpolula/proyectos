
/*Codificar el juego el numero secreto, que consiste en acertar un numero entre 1 y 100 (generado aleatoriamente).
 Para ello se introduce por teclado una serie de números, para los que se indica (mayor o menor) según sea
 mayor o menor con respecto al número secreto. El programa acaba cuando el usuario acierta el numero o pulsa -1.
*/
import java.util.*;

public class eje3 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        int numeroSecreto;
        int numeroUsuario;
        numeroSecreto = (int) (Math.random() * 100 + 1);
        System.out.println("Dime un número para ver si es el secreto o pon -1 para salir");
        numeroUsuario = sc.nextInt();
        while (numeroUsuario >= 0 && numeroSecreto != numeroUsuario) {
            if (numeroSecreto < numeroUsuario) {
                System.out.println("El numero es menor");
            } else {
                if (numeroSecreto > numeroUsuario) {

                    System.out.println("El numero secreto es mayor");
                }
            }
            System.out.println("Dime otro número para ver si es el secreto o pon -1 para salir");
            numeroUsuario = sc.nextInt();
        }
    }
}