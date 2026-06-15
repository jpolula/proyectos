/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.formas;

/**
 *
 * @author Juan Pedro
 */
public class Cuadrado extends Rectangulo
{
    public Cuadrado()
    {
        super();
    }
    
    public Cuadrado(String nom,String col,Punto2d p1,double lado)
    {
        super(lado, lado, p1, nom, col);
    }
    
    public void setLado(double l)
    {
        super.setLadoMayor(l);
        super.setLadoMenor(l);
    }
    
    public double getLado()
    {
        return super.getLadoMayor();
    }
    
    
}
