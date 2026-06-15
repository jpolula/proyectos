/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.servivomain;

/**
 *
 * @author Juan Pedro
 */
public class Animal implements SerVivo,Comparable
{
            // Archivo: Animal.java
        // Clase base (superclase) que implementa la interfaz SerVivo
         private String nombre;

        public Animal(String nombre) 
        {
            this.nombre = nombre;
        }

        public String getNombre() 
        {
            return nombre;
        }

        public void comer()
        {
        
        }
        
        public void dormir()
        {
        
        }
        public int compareTo(Object otro)
        {
            return 0;
        }
    // Método común a todos los animales
    public void mover() {
        System.out.println(nombre + " se está moviendo");
    }
}


