/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.pokemon;

/**
 *
 * @author Juan Pedro
 */
public class Entrenador 
{
    private String nombre;
    private double dinero;
    private int medallasObtenidas;
   // Pokemon p[]=new Pokemon[6];
    
    public Entrenador()
    {
    
    }
    public Entrenador(String nom,double din,int medallas)
    {
        nombre=nom;
        dinero=din;
        medallasObtenidas=medallas;
       
    }
    
    public String getNombre()
    {
        return nombre;
    }
    
    public void setNombre(String nom)
    {
        nombre=nom;
    }
    
    public double getDinero()
    {
        return dinero;
    }
    
    public void gastarDinero(int din)
    {
        dinero-=din;
    }
    
    public void ganarDinero(int din)
    {
        dinero+=din;
    }
    
    public int getMedallas()
    {
        return medallasObtenidas;
    }
    
    
    
   /* public void cambiarPokemon(Pokemon cambiar,int pos) //Metodo para cambiar un pokemon de los 6 pasandole el pokemon que quiero sacar y en en que posición irá.
    {
        p[pos]=cambiar;
    }*/
    
   /* public void cambiarTodos(Pokemon pokemones[]) //Metodo para cambiar los pokemon que tengo por otros 6 diferentes.
    {
        for(int i=0;i<pokemones.length;i++)
        {
            p[i]=pokemones[i];
        }
    }*/
    
    public void mostrar()
    {
        System.out.println("Nombre: " +nombre);
        System.out.println("dinero: " +dinero);
        System.out.println("Medallas obtenidas " +medallasObtenidas);
    }
}
