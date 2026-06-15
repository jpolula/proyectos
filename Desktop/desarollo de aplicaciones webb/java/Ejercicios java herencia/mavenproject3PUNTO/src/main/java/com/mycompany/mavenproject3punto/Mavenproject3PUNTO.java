/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.mavenproject3punto;

import java.util.Arrays;

/**
 *
 * @author Juan Pedro
 */
public class Mavenproject3PUNTO {

    public static void main(String[] args) {
        Punto2D p1=new Punto2D(2.9, 2.8);
        Punto2D p2=new Punto2D(2.8, 2.8);
        
        
        System.out.println(p1.equals(p2));
        
         System.out.println(p1.compareTo(p2));
         Punto2D puntos[]=new Punto2D [8];
         
          for(int i=0;i<puntos.length;i++)
          {
              puntos[i]=new Punto2D(Math.random()*10,Math.random()*10);
          }
          
       
          Arrays.sort(puntos);
          
          for(int i=0;i<puntos.length;i++)
          {
              System.out.println(puntos[i].distanciaCentro());
          }
          
          
          System.out.println("--------------------------------------------------------------------------------");
          
          ComparaDistancia c1=new ComparaDistancia();
          ComparaX c2=new ComparaX();
          
         Arrays.sort(puntos,c1);
          for(int i=0;i<puntos.length;i++)
          {
              System.out.println(puntos[i].distanciaCentro());
          }
          
          System.out.println();
          
          /*Arrays.sort(puntos,c2);
          for(int i=0;i<puntos.length;i++)
          {
              System.out.println(puntos[i].getX());
          }*/
          
    }
}
