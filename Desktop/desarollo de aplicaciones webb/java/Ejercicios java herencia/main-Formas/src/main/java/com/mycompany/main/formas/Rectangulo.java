/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.formas;

/**
 *
 * @author Juan Pedro
 */
public class Rectangulo extends Forma
{
    private double ladoMenor;
    private double ladoMayor;
   
    public Rectangulo()
    {
        super();
    }
    
    public Rectangulo(double l,double L, Punto2d p1,String nom,String co)
    {
        super(); //Llamo al constructor del padre.
        ladoMayor=L;
        ladoMenor=l;
        super.p2d=p1; //Asigno el objeto que me pasan como armugento.
        super.nombre=nom; //Asigno el nombre que me pasan como argumento
        super.color=co; //Asigno el color que me pasan como argumento.
    } 
    
    public double getLadoMenor()
    {
        return ladoMenor;
    }
    
    public double getLadoMayor()
    {
        return ladoMayor;
    }
    
    public void setLadoMenor(double medida)
    {
        //PRE:Los numeros pasados como argumentos deberán ser positivos
        ladoMenor=medida;
    }
    
    public void setLadoMayor(double nuevaMedida) //Metodo que me cambia el lado mayor
    {
        //PRE:Los numeros pasados como argumentos deberán ser positivos
        ladoMayor=nuevaMedida;
    }
    
    public double getArea(double ladoMa,double ladoMe) //Metodo que me devuelve el area de un rectangulo
    {
        //PRE:Los numeros pasados como argumentos deberán ser positivos
        return ladoMa*ladoMe;
    }
    
    public double perimetro(double l,double L) //Metodo que me calcula el perimetro de un rectangulo.
            //PRE:Los numeros pasados como argumentos deberán ser positivos
    {
        return (2*l)+(2*L);
    }
    
    public void cambiarTamaño(double factor)
    {
        ladoMayor*=factor;
        ladoMenor*=factor;
    }
    
     public void mostrar()
    {
        System.out.println("El lado mayor del rectangulo  es de " + ladoMayor);
        System.out.println("El lado menor del rectangulo  es de " + ladoMayor);
    }
}
