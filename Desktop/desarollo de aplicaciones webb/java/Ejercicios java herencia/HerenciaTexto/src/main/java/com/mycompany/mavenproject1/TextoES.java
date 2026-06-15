/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.mavenproject1;

/**
 *
 * @author usuario
 */
public class TextoES extends TextoENG
{
    protected String vocalesEs="aeiou";
    
    public TextoES()
    {
        super(); //llamada al constructor del padre
        //antes de ejecutar este constructor, se llama al del padre
        super.vocales = super.vocales+vocalesEs; //Uno el atributo del padre con el hijo. La variable vocales es del padre 
    }
    
     public TextoES(String vo)
     {
         this(); //Llamo al constructor de la clase.
         super.vocales=super.vocales+vo;
     }
        
     public String getvocalesEs()
     {
         return vocalesEs;
     }
     
     public void AñadirVocal(char vocal) //Con este metodo añado una vocal a nuestra clase de modo que la variable tendra(aeiouáéíóú+vocal)
     {
         super.vocales+=vocal;
     }
}
