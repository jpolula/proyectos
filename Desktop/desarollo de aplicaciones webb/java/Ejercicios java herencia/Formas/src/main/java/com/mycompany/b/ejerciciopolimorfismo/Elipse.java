/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.b.ejerciciopolimorfismo;

/**
 *
 * @author usuario
 */
public class Elipse extends Forma {
    
    protected double radioMayor;
    protected double radioMenor;
    
    public Elipse(double radiome, double radioma, String c, double x, double y, String n)
    {
        super(c, x, y, n);
        radioMayor=radioma;
        radioMenor=radiome;
    }
    
    public double getarea()
    {
        double resul;
        resul=Math.PI*(radioMayor*radioMenor);
        return resul;
    }
    
    
}
