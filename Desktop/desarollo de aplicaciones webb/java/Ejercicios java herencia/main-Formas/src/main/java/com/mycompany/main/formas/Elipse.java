/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.formas;

/**
 *
 * @author Juan Pedro
 */
public class Elipse extends Forma
{
    private double radioMayor;
    private double radioMenor;
    
   public Elipse()
   {
      super();
   }
   
   public Elipse(String name,String colo,Punto2d p2d,double radioMa,double radioMe)
   {
       super(colo, p2d, name);
       radioMayor=radioMa;
       radioMenor=radioMe;
   }
   
   public double getRadioMenor()
   {
       return radioMenor;
   }
   
   public double getRadioMayor()
   {
       return radioMayor;
   }
   
   public void setRadioMenor(double nuevoRadio)
   {
       radioMenor=nuevoRadio;
   }
   
   public void setRadioMayor(double nuevoRadio)
   {
       radioMayor=nuevoRadio;
   }
   
   public void mostrar()
   {
       super.imprimir();
       System.out.println("radio menor: " +radioMenor);
        System.out.println("radio menor: " +radioMayor);
   }
   
   public double radio()
   {
       return Math.PI*(radioMayor*radioMenor);
   }
   
   public double getArea()
    {
        double resul;
        resul=Math.PI*(radioMayor*radioMenor);
        return resul;
    }
}
