/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.mavenproject3punto;

/**
 *
 * @author Juan Pedro
 */
public class Punto3D extends Punto2D 
{
    protected double z;
    
    
   public  Punto3D(double x,double y, double z)
    {
        super(x, y); //LLamo al padre para que me los ejes x e y
        this.z=z;
    }
   
   public double getZ()
   {
       return z;
   }
   public void setZ(double z)
   {
       this.z=z;
   }
   
   public void mostrar()
   {
       super.mostrar(); //LLamo al metodo del padre para mostrar sus ejes x e y
       System.out.println("eje z " +z);
   }
}
