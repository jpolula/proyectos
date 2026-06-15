/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.mavenproject3punto;

import java.util.Comparator;

/**
 *
 * @author Juan Pedro
 */
public class ComparaDistancia implements Comparator
{
        public int compare(Object o1,Object o2)
        {
           Punto2D p1=(Punto2D) o1;
           Punto2D p2=(Punto2D) o2;
           
           if(p1.distanciaCentro()>p2.distanciaCentro())
           {
               return 1;
           }
           else
           {
               if(p1.distanciaCentro()<p2.distanciaCentro())
               {
                   return -1;
               }
               else
               {
                   return 0;
               }
           }
        }
}
