/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.ejercicio1;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;
import java.util.Scanner;

/**
 *
 * @author Juan Pedro
 */
public class Ejercicio1 {

    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);

        Map<String, Integer> palabras = new HashMap<>();

        String palabra;

        System.out.println("Introduce palabras hasta poner 'fin':");

        palabra = sc.next();

        while (palabra.equals("fin") == false) // Seria lo mismo a poner ! al comienzo de la condicion.
        {
            // if(palabras.get(palabra)==null)
            if (palabras.containsKey(palabra) == false) // si el mapa no contien esa palabra la añadimos.
            {
                // la palabra NO ESTÁ en el map; es la primera vez que aparece
                palabras.put(palabra, 1);
            } else // Si la palabra esta actualizamos el valor
            {
                // actualizar el valor de la palabra en el mapa
                int apariciones = palabras.get(palabra); // obtenemos el Value asociado a un Key determinado (palabra)
                // Es igual a poner palabras.get(palabra) +1.
                // Utilizamos el metodo get que es de mapa que mediNTE SU KEY obtenemos el valor
                // asociado
                palabras.put(palabra, apariciones + 1); // Con esto machaco el anterior añadiendo un +1 al Value
            }

            System.out.println("Introduce palabras hasta poner 'fin':");
            palabra = sc.next();
        } // del while

        System.out.println(palabras);

        Map<Integer, List<String>> listas = new HashMap<>();

        // recorrer el primer mapa
        Iterator<Map.Entry<String, Integer>> itr = palabras.entrySet().iterator();

        while (itr.hasNext()) {
            // variable local para acceder a la pareja del mapa
            Map.Entry<String, Integer> elem = itr.next();

            // elem contiene algo similar a "pero" -> 3

            if (listas.containsKey(elem.getValue()) == false) {
                // el número de repeticiones NO ESTÁ en el map
                List<String> listilla = new ArrayList<>(); // LISTA VACÍA
                listilla.add(elem.getKey()); // añado la palabra a la lista
                listas.put(elem.getValue(), listilla); //
            } else {
                // el número de repeticiones YA ESTÁ en el map
                List<String> temp = listas.get(elem.getValue());//
                temp.add(elem.getKey());
                // Lo unico que no entiendo es la parte del else
                // No es necesario actualizar la entrada del mapa puesto
                // que el nuevo elemento de la lista se añade a través de temp

            }

        }

        int key = 1;
        List<String> value = listas.get(key);
        System.out.println(value);
    }
}
