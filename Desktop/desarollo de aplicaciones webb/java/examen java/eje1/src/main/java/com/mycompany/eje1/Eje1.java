/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.eje1;

import java.util.*;

/**
 *
 * @author Juan Pedro
 */
public class Eje1 {
    
     static int[] eliminarValor(int tabla[], int valor) //En esta función se copiará la tabla original menos el valor que haya puesto el usuario.
    {
        int tablaCopia[]=new int [0];
        for(int i=0;i<tabla.length;i++)
        {
            if(tabla[i]!=valor)  // En cada iteración del bucle se copiará el valor de [i] excepto si es el valor a borrar.
            {
               tablaCopia=Arrays.copyOf( tablaCopia, tablaCopia.length+1);
               tablaCopia[tablaCopia.length-1]=tabla[i];
            }
        
        }
        return tablaCopia;
     }
     static void rellenarTabla(int tabla[])
    {
        int cont=0;
        int num;

        for(int i=0;i<tabla.length;i++)
        {
            num=(int) (Math.random()*10+1);
            tabla[i]=num;
         }
      }
       
       static int[] eliminarValor(int tablaOriginal[],  int tablaParaborrarEl[]) //En esta función se copiará la tabla original menos el valor que haya puesto el usuario.
    {// PRE: La mtriz tiene que ser válida.
        int tablaCopia[]=new int [0];
        for(int i=0;i<tablaParaborrarEl.length;i++)
        {
            for(int j=0;j<tablaOriginal.length;j++)
            {
                tablaCopia=eliminarValor(tablaParaborrarEl, j);
            }
        
        }
        return tablaCopia;
    }
    public static void main(String[] args)
    {
        Scanner sc=new Scanner(System.in);
        System.out.println("Dime la longitud del primer vector");
        int longitud=sc.nextInt();
        int vector1[]=new int[longitud];
        System.out.println("dime la longitud del segundo vector");
        int longitud2=sc.nextInt();
        int vector2[]=new int[longitud2];
        
        int vectorusuario[];
        
        rellenarTabla(vector1);
        rellenarTabla(vector2);
        
        
        System.out.println(Arrays.toString(vector1));
        System.out.println(Arrays.toString(vector2));
        
        vectorusuario=eliminarValor(vector1, vector2);
        System.out.println(Arrays.toString(vectorusuario));
        
        
        
        
    }
}
