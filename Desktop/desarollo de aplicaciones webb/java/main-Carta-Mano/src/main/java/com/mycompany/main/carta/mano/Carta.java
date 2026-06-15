/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.carta.mano;

/**
 *
 * @author Juan Pedro
 */
public class Carta 
{
   private int valorCarta;
    private String palo;
    
    public Carta()//Constructor vacio.
    {
        valorCarta=0;
        palo="";
    }
    public Carta(int valorCar,String pal)
    {
        valorCarta=valorCar;
        palo=pal;
    }
    
    public int getValor() //Con este metodo obtengo el valor de una carta.
    {
        return valorCarta;
    }
    
    public String getPalo() //Con este metodo obtengo el palo de una carta
    {
        return palo;
    }
    
    public void mostrar()
    {
        System.out.println("Valor de la carta: " +valorCarta);
        System.out.println("Palo de la carta " +palo );
    }
}
