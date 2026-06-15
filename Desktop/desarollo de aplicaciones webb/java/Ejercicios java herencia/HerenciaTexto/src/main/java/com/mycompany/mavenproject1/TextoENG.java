/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.mavenproject1;

/**
 *
 * @author usuario
 */
public class TextoENG
{
    protected String vocales;
    
    public TextoENG()
    {
        vocales="aeiou"; //vocales del alfabeto inglés
    }        
    
    public int contarVocales(String texto)
    {
        int cont=0; 
        
        texto = texto.toLowerCase();
        
        for(int i=0; i<texto.length(); i++)
        {
            if (vocales.indexOf(texto.charAt(i)) != -1)
            {
                cont++;
            }
        }
        
        return cont;
    }
}
