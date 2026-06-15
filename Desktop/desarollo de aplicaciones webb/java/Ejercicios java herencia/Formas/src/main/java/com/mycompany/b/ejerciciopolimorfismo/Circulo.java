/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.b.ejerciciopolimorfismo;

/**
 *
 * @author usuario
 */
public class Circulo extends Elipse
{
    private double radio;

    public Circulo( String c, double x, double y, String n, double ra)
    {
        super(ra, ra, c, x, y, n);
        radio=ra;
    }
    
    
}
