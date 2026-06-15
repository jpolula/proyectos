/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.carta.mano;

import java.util.Arrays;

/**
 *
 * @author Juan Pedro
 */
public class Mano 
{
    private Carta cartas[]; //No le asigno memoria ya que se la asignaré dentro del constructor.
    
    
    public Mano()
    {
        cartas=new Carta[0]; //Reservo memoria para añadir cartas
    }
    
    public void addCarta(Carta c) //Metodo para añadir una carta a nuestro vector. Reservaré una posición para cada carta que me llegue
    {
        cartas=Arrays.copyOf(cartas, cartas.length+1);
        cartas[cartas.length-1]=c; 
    }
    
    public Carta getCarta(int i) //Metodo que me devuelve el valor de una carta del vector.
    {
        return cartas[i];
    }
    
    public void mostrar() //Metodo que me muestra el contenido de la mano creada.
    {
        for(int i=0;i<cartas.length;i++)
        {
            System.out.print(cartas[i].getValor());
            System.out.print("");
        }
    }
    
    public int sumarMano() //Metodo para sumar la mano de un solo jugador.
    {
        int resul=0;
        for(int i=0;i<cartas.length;i++)
        {
            resul+=cartas[i].getValor(); //En cada iteracion del bucle iré guardando el valor de cada carta. (5 valores en total).
        }
        return resul;
    }
    
   public int sumaPalos(String palo)
   {
       int cont=0;
       for(int i=0;i<cartas.length;i++)
       {
           if(cartas[i].getPalo()==palo)
           {
               cont ++;
           }
           
       }
       return cont;
   }
}
