/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.servivomain;

/**
 *
 * @author Juan Pedro
 */
// Archivo: Pez.java
// Otra subclase que hereda de Animal y puede nadar
class Pez extends Animal
{
    private int hundimiento;
    public Pez(String nombre,int cantSumergida) 
    {
        super(nombre);
        hundimiento=cantSumergida;
    }

    public int compareTo(Object otro)
    {
        Pez otroPez=(Pez) otro;
        if(hundimiento>otroPez.hundimiento)
        {
           return 1;
        }
        if(hundimiento<otroPez.hundimiento)
        {
            return -1;
        }
        else
        {
            return 0;
        }
    }
    public int getHundimiento()
    {
        return hundimiento;
    }
    public void comer() {
        System.out.println(getNombre() + " está comiendo como un pez");
    }

    public void dormir() {
        System.out.println(getNombre() + " está durmiendo como un pez");
    }

    // Método específico de los peces que implementa nadar
    public void nadar() {
        System.out.println(getNombre() + " está nadando");
    }
}

