/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.main.formas;

/**
 *
 * @author Juan Pedro
 */
public class MainFormas {

    public static void main(String[] args) 
    {
       Punto2d p1=new Punto2d(Math.random()*10, Math.random()*10);
       Forma f1[]=new Forma[4];
       
       Rectangulo r1=new Rectangulo(Math.random()*10, Math.random()*10, p1, "Rectangulo", "Rojo");
       Circulo c1=new Circulo("Circulo", "Azul", Math.random()*10, p1);
       Cuadrado cua1=new Cuadrado("Cuadrado", "Naranja", p1, Math.random()*10);
       Elipse e1= new Elipse("Elipse", "Zurripardo", p1, Math.random()*10, Math.random()*10);
       
       f1[0]=r1;
       f1[1]=c1;
       f1[2]=cua1;
       f1[3]=e1;
       
       for(int i=0;i<f1.length;i++)
       {
           f1[i].color="Rojo";
           f1[i].setPuntoXY(Math.random()*10, Math.random()*10);
       }
       
        for(int i=0;i<f1.length;i++)
        {
                f1[i].imprimir();
        }
        
        for(int i=0;i<f1.length;i++)
        {
            
        }
    } 
}
