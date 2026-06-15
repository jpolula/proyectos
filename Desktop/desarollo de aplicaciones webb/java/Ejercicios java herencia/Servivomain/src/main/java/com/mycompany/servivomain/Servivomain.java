/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.servivomain;

import java.util.Arrays;

/**
 *
 * @author Juan Pedro
 */
public class Servivomain 
{

    public static void main(String[] args) 
    {
       // Crear instancias de las subclases
        SerVivo ave = (SerVivo) new Ave("Loro",12.00);
        SerVivo pez = (SerVivo) new Pez("Pez Dorado",7);

        // Llamar a métodos de la interfaz SerVivo usando polimorfismo
        ave.comer();  // Salida: Loro está comiendo como un ave
        ave.dormir(); // Salida: Loro está durmiendo como un ave

        pez.comer();  // Salida: Pez Dorado está comiendo como un pez
        pez.dormir(); // Salida: Pez Dorado está durmiendo como un pez

        // No se puede llamar a métodos específicos de las subclases
        // ave.volar(); // Esto generaría un error de compilación
        // pez.nadar(); // Esto generaría un error de compilación

        Pez p1=new Pez("Eustaquio", 18);
        Pez p2=new Pez("Eustaquio", 18);
        
        System.out.println(p1.compareTo(p2));
        
        Pez peces[]=new Pez[10];
        String nombres[]={"Alvaro","Roberto","Eustaquio","Pepe","Gonzalo","ter","j3yg","3yig3","li3rygf","i3g3ieuge3d"};
        
        for(int i=0;i<peces.length;i++)
        {
            peces[i]=new Pez(nombres[i], (int) (Math.random()*50+1));
        }

        
        Arrays.sort(peces);
        
        for(int i=0;i<peces.length;i++)
        {
            System.out.println(peces[i].getHundimiento());
        }
        // Si se quiere acceder a métodos específicos, es necesario hacer un casting
    }
}
