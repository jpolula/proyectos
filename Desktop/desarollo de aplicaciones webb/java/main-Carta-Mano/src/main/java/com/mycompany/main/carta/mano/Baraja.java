/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.carta.mano;

/**
 *
 * @author Juan Pedro
 */
public class Baraja 
{
    Carta[]mazo;
    private int restantes; //Cartas que quedan por salir
    
    public Baraja()
    {
        int cont=0;
        mazo=new Carta[52];
        //Crear 4 bucles, (oro,bastos,copas y espadas) y que haga 13 iteraciones en cada bucle.
        restantes=52;
        for(int i=1;i<14;i++) // Cartas de oros
        {
           mazo[cont]=new Carta(i,"oros");
           cont++;
        }
        
        for(int i=0;i<14;i++) // Cartas de copas
        {
           mazo[cont]=new Carta(i,"copas");
           cont++;
        }
        
        for(int i=0;i<14;i++) // Cartas de bastos
        {
           mazo[cont]=new Carta(i,"bastos");
           cont++;
        }
        
        for(int i=0;i<14;i++) // Cartas de espadas
        {
           mazo[cont]=new Carta(i,"espadas");
           cont++;
        }
    }
    
    public Carta robar()
    {
        int escogida=(int) (Math.random()*restantes); //Genero un numero aleatorio hasta el 52
        Carta resul;
        
        resul=new Carta(mazo[escogida].getValor(), mazo[escogida].getPalo()); //Creo la carta con el valor aleatorio que me ha dado el random ,
        
        mazo[escogida]=mazo[restantes-1]; //Asigno la Ultima carta del mazo a la que he robado.
        restantes--; //Le resto una a la baraja.
        return resul;
    }
}
