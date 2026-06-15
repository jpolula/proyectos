/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.eje3;

import java.util.Scanner;

/**
 * Crear un programa que lea un número double por teclado y haga lo siguiente:
 * 
 * indique si la parte entera es múltiplo de 3
 * 
 * indique si la parte entera es múltiplo de 5
 * 
 * indique si la parte entera es múltiplo de 3 y de 5
 * 
 * en todos los casos anteriores, deberá mostrar el número de cifras de la parte
 * entera
 */
public class Eje3 {
    static int saberCifras(int num) {
        int contDigitos = 1;
        while (num > 10) {// si o si un número tiene que tener 1 cifra.
            num = num / 10;
            contDigitos++; // Subo uno el contador cada vez que el número es mayor de 10.
        }
        return contDigitos;

    }

    public static void main(String[] args) {
        System.out.println("Dime un número");
        Scanner sc = new Scanner(System.in);
        double num = sc.nextDouble();
        int contDigitos = 1; // Un número tiene que tener una cifra si o si

        if ((int) num % 3 == 0) // si el numero diviendolo entre 3 su resto da 0 es múltiplo de 3
        {
            System.out.println((int) num + " es múltiplo de 3");
        } else {
            System.out.println((int) num + " No es múltiplo de 3"); //
        }

        if ((int) num % 5 == 0) // si el numero diviendolo entre 5 su resto da 0 es múltiplo de 5
        {
            System.out.println((int) num + " es múltiplo de 5");
        } else {
            System.out.println((int) num + " No es múltiplo de 5");
        }

        if ((int) num % 3 == 0 && (int) num % 5 == 0) {
            System.out.println((int) num + " es multiplo de 3 y 5");
        } else {
            System.out.println((int) num + " no es multiplo de 3 y 5 a la misma vez");

        }

        int resul = saberCifras((int) num);
        System.out.println((int) num + " tiene " + resul + " cifras");
    }
}
