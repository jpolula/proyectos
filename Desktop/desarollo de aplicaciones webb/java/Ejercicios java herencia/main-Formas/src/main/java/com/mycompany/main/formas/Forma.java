/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.formas;

/**
 *
 * @author Juan Pedro
 */
public  class Forma extends FormaAbstracta
{
    protected String color;
    protected Punto2d p2d;
    protected String nombre;
    
    public double getArea()
    {
       return 0;
    }
    
   
   public Forma()
   {
       color="";
       nombre="";
   }
   
   public Forma(String co,Punto2d p2d,String nomb) //Constructor lleno
   {
       color=co;
       this.p2d=p2d;
       nombre=nomb;
   }
   
   public void setPuntoXY(double x,double y) //Metodo para cambiar el eje x y el eje y (Lo uso llamando al que ya tengo echo en la clase punto 2D)
   {
       p2d.setXY(x,y);
   }
   
  public void setPuntoX(double x)//Metodo para cambiar el eje x llamando al método de la clase Punto2d
  {
      p2d.setX(x);
  }
  
  public void setPuntoY(double y)//Metodo para cambiar el eje y llamando al metodo de la clase Punto2d
  {
      p2d.setY(y);
  }
   public void imprimir()
   {
       System.out.println("nombre: " +nombre);
       System.out.println("Color: " +color);
       System.out.println("eje x: " +p2d.getX());
       System.out.println("eje y: " +p2d.getY());
   }
   
   public Punto2d getPunto2d() //Obtengo un objeto de Punto2d
   {
       return p2d;
   }
}
