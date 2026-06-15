/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.servivomain;

/**
 *
 * @author Juan Pedro
 */
// Archivo: Ave.java
// Subclase que hereda de Animal y puede volar
class Ave extends Animal 
{
    private double alturaMax;
    public Ave(String nombre,Double alturaM) {
        super(nombre);
        alturaMax=alturaM;
    }

   public int compareTo(Object otro)
   {
       Ave otroAve=(Ave) otro;
       if(alturaMax>otroAve.alturaMax)
       {
           return 1;
       }
       else
       {
           if(alturaMax<otroAve.alturaMax)
           {
               return -1;
           }
           else
           {
               return 0;//Si devuelve 0 es que la altura son iguales.
           }
       }
      
   }
   
   public boolean equals(Object otro)
   {
       Ave otraAve=(Ave) otro;
       if(alturaMax==otraAve.alturaMax)
       {
           return true;
       }
       else
       {
           return false;
       }
   }
    public void comer() {
        System.out.println(getNombre() + " está comiendo como un ave");
    }

   
    public void dormir() {
        System.out.println(getNombre() + " está durmiendo como un ave");
    }

    // Método específico de las aves que implementa volar
    public void volar() {
        System.out.println(getNombre() + " está volando");
    }
}

