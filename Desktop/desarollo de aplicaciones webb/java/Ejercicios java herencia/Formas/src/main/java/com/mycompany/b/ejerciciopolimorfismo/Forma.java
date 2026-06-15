/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.b.ejerciciopolimorfismo;

/**
 *
 * @author usuario
 */
//public class Forma extends FormaAbstracta //Esta forma se utiliraría si creamos una clase abstracta.
public class Forma extends FormaAbstracta implements Comparable
{
    @Override
    
    public double getarea() //sobreescribir el método abstracto del padre
    {
        return 0;
    }
   
    @Override
    public double getLado()
    {
        return 0;
    }
    
    @Override
    public int compareTo(Object otro) //Metodo para comparar un objeto con otro
    {
        return 0;
    }
    
    @Override
   public double getPerimetro()
   {
       return 0;
   }
   
   public double getx()
   {
       return 0;
   }
    
    protected String color;
    protected Punto punto;
    protected String nombre;
    
    public Forma(String c, double x,double y, String n)
    {
        color=c;
        punto=new Punto(x,y);
        nombre=n;
    }
    
    public void setforma(double newx,double newy)
    {
        punto.desplazaXY(newx, newy);
    }
    
    public void setcolor(String c)
    {
        color=c;
    }
    
    public String getcolor()
    {
        String resul;
        resul=color;
        return resul;
    }
    
    public String getnombre()
    {
        String resul;
        resul=nombre;
        return resul;
    }
    
    public Punto getpunto()
    {
        Punto resul;
        resul=punto;
        return resul;
    }
    
    public void mostrar()
    {
        System.out.println("ATRIBUTOS DE FORMA:");
        System.out.println("Nombre: " + nombre);
        System.out.println("Color: " + color);
        System.out.println("Punto: " + punto.getx() + " " + punto.gety());
    }
    
    protected void mostrarComun()
    //permite a las subclases mostrar los atributos comunes
    {
        System.out.println("ATRIBUTOS DE FORMA:");
        System.out.println("Nombre: " + nombre);
        System.out.println("Color: " + color);
        System.out.println("Punto: " + punto.getx() + " " + punto.gety());
    }
    
    
    
}
