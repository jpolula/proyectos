/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.pokemon;

/**
 *
 * @author Juan Pedro
 */
public class Pikachu extends Pichu 
{
    protected  int salud=75;
    protected int saludMaxima=75;
    public Pikachu()
    {
       super(); //Llamo al constructor del padre
    }
    
    public Pikachu(int experienciaInicial) {
        super(); // Llama al constructor de la clase base (Pichu)
        super.experiencia = experienciaInicial; // Inicializa la experiencia con el valor proporcionado
    }
    
    public int getSaludActual()
    {
        return salud;
    }
    
    public int getSaludMaxima()
    {
        return saludMaxima;
    }
     
    public int ataqueRapido() //Movimiento ataque rápido, que genera 15 puntos de daño y tiene un 50% de probabilidades de hacer 10 puntos más de daño.
    {
        int daño=15;
        if((int) (Math.random()*100+1)>50)
        {
            daño+=10;
        }
        return daño;
    }
    
    public int bolaVoltio()
    {
        int daño=10; //Daño mínimo que hará el ataque.
        int dañoAdicional= (int) (Math.random()*40+1); 
        daño+=dañoAdicional; //El daño que hará el movimiento va desde 10 a 50.
        return daño;
    }
    
     public void mostrar()
    {
        System.out.println("Nombre : Pikachu");
        System.out.println("Entrenador:  " + super.getEntrenador());
        System.out.println("Experiencia " + super.getExp());
        System.out.println("Salud: " +getSaludActual());
    }
     
     
    @Override
    public void cambiarExperiencia(int exp) //Metodo que usaré cada vez que el pokemon alcance 100 de daño.
    {
        super.experiencia+=15;
    }
    
    public void restarSalud(int danio)
    {
        salud=salud-danio;
    }
}
