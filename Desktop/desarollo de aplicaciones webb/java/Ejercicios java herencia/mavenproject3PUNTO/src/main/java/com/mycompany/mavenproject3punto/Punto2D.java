/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.mavenproject3punto;

/**
 *
 * @author Juan Pedro
 */
public class Punto2D implements Comparable
{
    protected double x;
    protected double y;
    
    public Punto2D()
    {
        x=0.2;
        y=0.7;
    }
    
    public int compareTo(Object otro)
    {
        Punto2D otroPunto= (Punto2D) otro;
        if(distanciaCentro()>otroPunto.distanciaCentro())
        {
            return 1;
        }
        if(distanciaCentro()<otroPunto.distanciaCentro())
        {
            return -1;
        }
        else
        {
             return 0;
        }
       
    }
    
    public boolean equals(Object otro)
    {
        Punto2D otroPunto=(Punto2D) otro;
        if(distanciaCentro()==otroPunto.distanciaCentro()) //Si la distancia del centro de un objeto es igual al otro nos dará true el resultado.
        {
            return true;
        }
        else
        {
            return false;
        }
        
    }
    public Punto2D(double x, double y)
    {
        this.x=x;
        this.y=y;
    }
    
    public double distanciaCentro()
    {
        double resul=(x*x)+(y+y);
        resul=Math.sqrt(resul);
        return resul;
    }
    
    public double getX()
    {
        return x;
    }
    
    public double getY()
    {
        return y;
    }
    
    public void set(double x,double y)
    {
        this.x=x;
        this.y=y;
    }
    
    public void mostrar()
    {
        System.out.println("eje x: " +x);
        System.out.println("eje y: " +y);
    }
    
}

    