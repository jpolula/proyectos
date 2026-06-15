/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.ejemplou;

/**
 * @author Juan Pedro
 */
public class Ejemplou {

    public static void main(String[] args) {
     boolean  dentro=true; //suponemos que esa posición esta dentro de la tabla.
     char matriz[][]=new char[3][3];
     matriz[0][0]=' ';
     matriz[1][0]=' ';
     matriz[2][0]=' ';
     
     for(int fil=0;fil<5;fil++)
     {
         for(int col=0;col<5;col++)
         {
             if(matriz[fil][col]==' ')
             {
                 dentro=false;
                 System.out.println(dentro);
             }
             System.out.println(matriz[fil][col]);
        
         }
             
     }
     
    }
}
