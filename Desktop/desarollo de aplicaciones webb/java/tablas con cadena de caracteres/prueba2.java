public class prueba2 {
    public static void main(String[] args) {
        String cadenaSinVocales = "";
        char c = ' ';
        String cadena = "Hola como estas, encantado de conocerte";
        for (int cont = 0; cont < cadena.length(); cont++) {// recorro la cadena caracter a caracter
            c = cadena.charAt(cont);// obtengo caracter a caracter de la cadena
            if (!esVocal(c)) {
                cadenaSinVocales = cadenaSinVocales + c;
            }
        }
        System.out.println(cadenaSinVocales);
    }

    static boolean esVocal(char caracter) {
        boolean esVocal = true;
        String vocales = "aeiouáéíóú";// La utilizaré para comparar con el caracter.
        caracter = Character.toLowerCase(caracter);// Pongo en minuscula caracter a caracter.
        caracter = Character.toUpperCase(caracter);// Se utiliza para poner el caracter en mayuscula.
        if (vocales.indexOf(caracter) == -1) {// si el caracter no es vocal se copia en la variable.
            esVocal = false;
        } else {
            esVocal = true;
        }
        //
        return esVocal;
    }
}
