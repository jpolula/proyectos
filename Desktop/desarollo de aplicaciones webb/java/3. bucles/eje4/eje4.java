/*Un centro de investigación de la flora urbana necesita una aplicación que muestre cual es el arbol más alto.
 Para ello se introducira por teclado la altura en centimetros de cada arbol (terminando la introducción de datos 
cuando se utilice -1 ). Los arboles se identificsn mediante etiquetas con números unicos correlativos, comenzando 
en 0. Diseñar una aplicación que resuelva el problema planteado*/

import java.util.Scanner;

public class eje4 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        int altura = 0;
        int etiqueta = 0;
        int etiquetaArbolAlto;
        int alturaArbolAlto;
        alturaArbolAlto = altura;// la altura por defecto será 0
        etiquetaArbolAlto = etiqueta;// Por ahora, la etiqueta será la 0
        do {
            System.out.println("Dime la altura del primer árbol" + " Su etiqueta es " + etiqueta);
            altura = sc.nextInt();

            if (altura > alturaArbolAlto) {
                alturaArbolAlto = altura;// Actualizo la altura del nuevo árbol más alto
                etiquetaArbolAlto = etiqueta;// Actualizo su etiqueta
            }
            etiqueta++;// incrementamos en 1 la variable para identificar al siguiente arbol
            if (altura == -1) {
                System.out.println("Error");
            }

        } while (altura != -1);

        System.out.println("El arból más alto mide " + alturaArbolAlto);
        System.out.println("La etiqueta del árbol más alto  " + etiquetaArbolAlto);

    }
}