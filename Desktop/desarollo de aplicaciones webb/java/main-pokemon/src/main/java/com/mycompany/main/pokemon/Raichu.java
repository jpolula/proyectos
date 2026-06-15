/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.pokemon;

/**
 *
 * @author Juan Pedro
 */
public  class Raichu extends Pikachu
{
    protected int saludMaxima=100;
    protected int salud=100;
    public Raichu()
    {
       super();
    }
    
    public Raichu(int exp) //Constructor que le paso como argumento la experiencia que tiene la evolución anterior
    {
        super();
        super.experiencia=exp;
    }
    
    public int getSaludMaxima() //Solo utilizaremos el metodo get de salud, ya que el set no tendría sentido debido a que el atributo salud no podemos cambiarlo.
    {
        return saludMaxima;
    }
    
    public int getSaludActual()
    {
        return salud; 
    }
    
    public int ataqueRapido()
    {
        int daño=30;
        if((int) (Math.random()*100+1)>50)
        {
            daño+=10;
        }
        return daño;
    }
    
    public boolean equals( Object a)
    {
        Raichu otro=(Raichu) a;
        if(super.getSaludMaxima()!=otro.getSaludMaxima())
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    public void mostrar()
    {
        System.out.println("Nombre : Raichu");
        System.out.println("Entrenador: " +super.getEntrenador());
        System.out.println(" Experiencia: " + super.getExp());
        System.out.println("Salud: " +getSaludMaxima());
    }
    
     public void cambiarExperiencia() //Metodo que usaré cada vez que el pokemon alcance 100 de daño.
    {
        super.experiencia+=15;
    }
     
     public void restarSalud(int danio)
    {
        salud=salud-danio;
    }
}
