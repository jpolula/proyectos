/**
 * prueba
 */
public class prueba {

    public static void main(String[] args) {
        String cadena = "Hola como estass";
        char c = cadena.charAt(1);// Coge caracater a caracter dependiendo del indice que le pongamos.
        // Asigna el indice 1 a la variable c.
        System.out.println(cadena.charAt(1));
        String trozoCadena = cadena.substring(0, 4);// Me coge los caracteres que le especifique en el argument
        System.out.println(trozoCadena);
        String cadenaVacia = "";// Inicializo la variable.
        for (int cont = cadena.length() - 1; cont >= 0; cont--) {
            char a = cadena.charAt(cont);
            cadenaVacia = cadenaVacia + a;
        }
        System.out.println(cadenaVacia);
    }
}