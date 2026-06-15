/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.b.ejerciciopolimorfismo;

/**
 *
 * @author usuario
 */
public class Cuadrado extends Rectangulo {
    
    //private double lado;
    
    public Cuadrado(String c, double x, double y, String n, double la)
    {
        super(c, x, y, n,la,la); //repetimos el atributo lado porque un cuadrado es un rectángulo con los dos lados iguales
        //lado=la;
    }
    
    public double getLado()
    {
        return ladoMayor; //nos da igual qué lado devolver porque son iguales
    }
    
    public void setLado(double v)
    {
        ladoMayor = v;
        ladoMenor = v;
    }
    
    @Override
    public void mostrar()
    {
       //super.mostrar();
       mostrarComun();
       System.out.println("Lado: " + ladoMayor);
    }
    
}
