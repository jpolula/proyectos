/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.formas;

/**
 *
 * @author Juan Pedro
 */
public class Punto2d 
{
    private  double x;
    private double y;
    
    public Punto2d(double x,double y) //Constructor completo
    {
        this.x=x;
        this.y=y;
    }
    
    public double getX() //Obtengo el valor de x
    {
        return x;
    }
    
    public double getY() //Obtengo el valor de y
    {
        return y;
    }
    
    public void setXY(double x,double y) //Metodo para cambiar tanto el eje x como el y.
    {
        this.x=x;
        this.y=y;
    }
    
    public void setX(double x) //Metodo para cambiat el eje x
    {
        this.x=x;
    }
    
    public void setY(double y) //Metodo para cambiar el eje y
    {
        this.y=y;
    }
    public void mostrar()
    {
        System.out.println("eje x: " + x);
        System.out.println("eje y : " +y);
    }
}
