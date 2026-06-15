/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.main.carta.mano;

/**
 *
 * @author Juan Pedro
 */
public class MainCartaMano {
    
    public static Carta obtenerCarta()
//devuelve un objeto de tipo Carta de las posibles que hay en la baraja
//1-10, sota, caballo, rey de OROS
//1-10, sota, caballo, rey de COPAS
//1-10, sota, caballo, rey de ESPADAS
//1-10, sota, caballo, rey de BASTOS
//(52 posibilidades en total)
{
     String palos[] ={"oros", "copas", "espadas", "bastos"}; //Las cartas podrán ser 4 palos diferentes.
     int valorCarta=(int) Math.random()*13+1; //Genero un numero aleatorio.
     int paloCarta=(int) (Math.random()*palos.length); //Genero un palo aleatorio para la carta
     
     Carta nuevaCarta=new Carta(valorCarta, palos[paloCarta]);
     return nuevaCarta;
}
    public static void main(String[] args)
    {
         Mano jug1=new Mano();
         Mano jug2=new Mano();

         for(int i=0; i<5; i++)
    //metemos cartas en cada mano (pueden estar repetidas)
        {
            Carta nuevaCarta;
            nuevaCarta = obtenerCarta();
            jug1.addCarta(nuevaCarta);
            nuevaCarta = obtenerCarta();
            jug2.addCarta(nuevaCarta);
        }
         int puntuacion1=jug1.sumarMano(); //Guardo el valor de la suma de la mano en una variable
         int puntuacion2=jug2.sumarMano();//Guardo el valor de la otra suma en otra variable.
         
         System.out.print("Jugador 1 : " );
         jug1.mostrar();
         System.out.println();
         
         System.out.print("Jugador 2");
         jug2.mostrar();
         if(puntuacion1>puntuacion2)
         {
             jug1.mostrar();
         }
         else
         {
             if(puntuacion2>puntuacion1)
             {
                 jug2.mostrar();
             }
             else
             {
                 if(jug1.sumaPalos("oros")>jug2.sumaPalos("oros"))
                 {
                     jug1.mostrar();
                 }
                 else
                 {
                     if(jug2.sumaPalos("oros")>jug2.sumaPalos("oros"))
                     {
                         jug2.mostrar();
                     }
                     else
                     {
                         if(jug1.sumaPalos("copas")>jug2.sumaPalos("copas"))
                         {
                             jug1.mostrar();
                         }
                         else
                         {
                             if(jug2.sumaPalos("copas")>jug1.sumaPalos("copas"))
                             {
                                 jug2.mostrar();
                             }
                             else
                             {
                                 if(jug1.sumaPalos("espadas")>jug2.sumaPalos("espadas"))
                                 {
                                     jug1.mostrar();
                                 }
                                 else
                                 {
                                    if(jug2.sumaPalos("espadas")>jug1.sumaPalos("espadas"))
                                    {
                                        jug2.mostrar();
                                    }
                                    else
                                    {
                                        if(jug1.sumaPalos("bastos")>jug2.sumaPalos("bastos"))
                                        {
                                            jug1.mostrar();
                                        }
                                        else
                                        {
                                            if(jug2.sumaPalos("bastos")>jug1.sumaPalos("bastos"))
                                            {
                                                jug2.mostrar();
                                            }
                                            else
                                            {
                                                System.out.println("No hay ningún ganador");
                                            }
                                        }
                                    }
                                 }
                             }
                         }
                     }
                 }
             }
         }
  }
}
