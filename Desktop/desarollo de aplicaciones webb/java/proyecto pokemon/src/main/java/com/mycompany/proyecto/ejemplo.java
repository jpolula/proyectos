public class ejemplo {

    public static void main(String[] args) {
        mostrarYCerrarMensaje("Este es mi mensaje", 500); // 2000 milisegundos de retardo
    }

    public static void mostrarYCerrarMensaje(String mensaje, int retardoMilisegundos) {
        System.out.println(mensaje); // Muestra el mensaje

        try {
            Thread.sleep(retardoMilisegundos); // Espera el tiempo especificado
        } catch (InterruptedException e) {
            e.printStackTrace();
        }

        limpiarConsola(); // Limpia la consola para ocultar el mensaje
    }

    private static void limpiarConsola() {
        // Imprime varias líneas en blanco para simular la limpieza
        for (int i = 0; i < 50; i++) {
            System.out.println();
        }
    }
}
