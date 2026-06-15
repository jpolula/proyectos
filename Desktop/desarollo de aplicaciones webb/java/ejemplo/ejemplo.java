import java.io.IOException;

/**
 * ejemplo
 */
public class ejemplo {

    public static void main(String[] args) {
        mostrarYCerrarMensaje("Este es mi mensaje", 500); // 2000 milisegundos de retardo
    }

    public static void mostrarYCerrarMensaje(char matriz[][], int retardoMilisegundos) {
        System.out.println(matriz); // Muestra el mensaje

        try {
            Thread.sleep(retardoMilisegundos); // Espera el tiempo especificado
        } catch (InterruptedException e) {
            e.printStackTrace();
        }

        limpiarConsola(); // Limpia la consola para ocultar el mensaje
    }

    private static void limpiarConsola() {
        try {
            if (System.getProperty("os.name").contains("Windows")) {
                new ProcessBuilder("cmd", "/c", "cls").inheritIO().start().waitFor();
            } else {
                // Si no es Windows, intenta limpiar con ANSI escape codes (puede no funcionar
                // en todos los terminales)
                System.out.print("\033[H\033[2J");
                System.out.flush();
            }
        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }
    }
}
