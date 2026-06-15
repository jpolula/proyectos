/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.eje2;

import java.util.Arrays;
import java.util.Scanner;

/**
 *
 * @author Juan Pedro
 */
public class Eje2 {
    
    static void rellenarBordesVerticales(int matriz[][])
    {
        int fila=0;
        int col=0;
        for()
        {
            
        
        
        }
    
    }
    
    static void rellenarBordesHorizontales(int matriz[][])//Función que me rellena los bordes horizontales
    {
       int  fila=0;
        int columna=0;
        for(int fil=0;fil<matriz.length;fil++)
        {
            if(fil==0||matriz.length-1==1)
            {
                 for(int col=0;col<matriz[0].length;col++)
                 matriz[fila][columna]=0;
            } 
        }
    }
    
    static void rellenarmatriz(int matriz[][])//Función que me rellena una matriz de 1
    {
        for(int fil=0;fil<matriz.length;fil++)
        {
           
            for(int col=0;col<matriz[0].length;col++)
            {
                matriz[fil][col]=1;
            }
            
        }
    }
    
    static void rellenarDiagonalPrincipal1(int matriz[][])//Función que me rellena una diagonal principal de 0
    {
        int fila=0;
        int columna=0;
       
        for(int fil=0;fil<matriz.length;fil++)
        {
            matriz[fila][columna]=0; 
            fila++;
            columna++;
        }
    }
    
    static void rellenarPrincipal2(int matriz[][]) //Función que me rellena la diagonal inversa de 0
    {
        int fila=0;
        int columna=matriz[0].length-1;
        
        for(int fil=0;fil<matriz.length;fil++)
        {
            matriz[fila][columna]=0; 
            fila--;
            columna++;
        }
    }

    public static void main(String[] args) {
        System.out.println("Dime la longitud de la matriz");
        Scanner sc=new Scanner(System.in);
        int longitud=sc.nextInt();
        int matriz[][]=new int[longitud][longitud];
        int opcion=0;
        rellenarmatriz(matriz);
        System.out.println(Arrays.deepToString(matriz));
        
        switch (opcion) { //Switch con las diferentes opciones.
            case 1:
                
                break;
            case 2:
                break;
                
            case 3:rellenarBordesHorizontales(matriz);
                break;
            
            case 4:rellenarDiagonalPrincipal1(matriz);
                break;
                
            case 5:rellenarPrincipal2(matriz);
                break;
                
               
            default:
                throw new AssertionError();
        }
        
    }
}
