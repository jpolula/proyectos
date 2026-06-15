/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.pokemon;

/**
 *
 * @author Juan Pedro
 */
public class Pichu extends Pokemon
{
    protected String entrenador;
    protected int experiencia=0; //Al comenzar la experiencia del objeto es 0, ya que no ha combatido
    protected int salud=50;
    protected int saludMaxima=50;
    
    public Pichu()
    {
        entrenador="Ash";
    }
    public Pichu(String entrenador) //Constructor de la clase. el usuario solo pondrá el nombre del entrenador ya que la salud no cambiará y la exp se ganara combatiendo.
    {
        this.entrenador=entrenador;
    }
    
    public void setEntrenador(String nuevoEntrenador) //Metodo para cambiar al entrenador 
    {
        entrenador=nuevoEntrenador;
    }
    
    public String getEntrenador()
    {
        return entrenador;
    }
    
    public int getExp() //Metodo que devuelve la experiencia que tiene hasta ese momento
    {
        return experiencia;
    }
    
    public int getSaludMaxima()
    {
        return saludMaxima;
    }
    
    public int getSaludActual()
    {
        return salud;
    }
    
    public void cambiarExperiencia(int exp) //Metodo que usaré cada vez que el pokemon alcance 100 de daño.
    {
        experiencia+=15;
    }
    public int ataqueRapido() //Movimiento ataque rápido, que provoca 10 de daño y tiene un 50% de probabilidad de asestar el doble de daño.
    {
        int daño=10;
        if((int) (Math.random()*100+1)>50)
        {
            daño*=2;
        }
        return daño;
    }
    
    public int bolaRayo() //Movimiento bolaRayo, que hace un daño de entre 10 y 20.
    {
        int daño=10;
        int dañoAñidicional=(int) (Math.random()*10+1);
        daño+=dañoAñidicional;
        return daño;
    }
    
    public void mostrar()
    {
        System.out.println("Nombre: Pichu");
        System.out.println("Nombre del entrenador: " +entrenador);
        System.out.println("Experiencia " +experiencia);
        System.out.println("Salud " +salud);
    }
    
    public void restarSalud(int danio)
    {
        salud=salud-danio;
    }
}
