/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.variasventanas;

/**
 *
 * Clase para el intercambio de información entre diferentes ventanas
 * En nombre de la clase puede ser el que se quiera
 */
public class Singleton 
{
   private final static Singleton INSTANCIA = new Singleton();
   
   private Singleton(){}
   //El constructor es privado para evitar que se puedan crear instancias de esta clase
   
   public static Singleton getInstancia()
   {
       return INSTANCIA;
   }
   
   //Ahora definimos atributos, getters y setters que queramos usar para intercambiar información
   private int dato;
   
   public void setDato(int d)
   {
       dato = d;
   }
   
   public int getDato()
   {
       return dato;
   }
}
