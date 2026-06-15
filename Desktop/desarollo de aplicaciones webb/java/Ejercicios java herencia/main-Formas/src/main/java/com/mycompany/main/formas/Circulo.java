/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.formas;

/**
 *
 * @author Juan Pedro
 */
public class Circulo extends Elipse
{
    public Circulo()
    {
        super();
    }
    
    public Circulo(String nom,String co,double ra,Punto2d p2)
    {
        super(nom, co, p2, ra, ra);
    }
    
    public void setRadio(double nuevoRadio) //Es necesario llamar a los dos set ya que de no hacerlo el atributo radio de la clase puede cambiar y dar info erronea
    {
        super.setRadioMayor(nuevoRadio);
        super.setRadioMenor(nuevoRadio);
    }
    
    public double getRadio()
    {
        return super.getRadioMenor(); //Nos da igual cual devolver ya que los dos tienen el mismo valor.
    }
}
