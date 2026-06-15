/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.b.ejerciciopolimorfismo;

/**
 *
 * @author usuario
 */
public class Punto {
    protected double x;
    protected double y;
    
    Punto(double posiX, double posiY)
    {
        x=posiX;
        y=posiY;
    }
    public double getx()
    {
        double resul;
        resul=x;
        return resul;
    }
    public double gety()
    {
        double resul;
        resul=y;
        return resul;
    }
    
    public void desplazaX(double newX)
    {
        x=newX;
    }
    public void desplazaY(double newY)
    {
        y=newY;
    }
    public void desplazaXY(double newx, double newy)
    {
        x=newx;
        y=newy;
    }
    public String getdistancia(double puntox, double puntoy)
    {
        double distanciax;
        double distanciay;
        String resul;
        
        distanciax=(x)-(puntox);
        distanciay=(y)-(puntoy);
        
        resul=String.valueOf(distanciax)+ String.valueOf(distanciay);
        
        return resul;
    }
    
    public void getinfo()
    {
        System.out.print("La posición en la que se encuentra el punto es: " + x + ", " + y);
    }
    
}
