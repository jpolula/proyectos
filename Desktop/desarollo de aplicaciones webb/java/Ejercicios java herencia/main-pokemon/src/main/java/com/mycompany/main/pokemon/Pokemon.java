/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.pokemon;

/**
 *
 * @author Juan Pedro
 */
public abstract class Pokemon
{
    public abstract void mostrar();
    public abstract int ataqueRapido();
    public abstract void cambiarExperiencia(int e); //Metodo que usaré cada vez que el pokemon alcance 100 de daño.
    public abstract void restarSalud(int d); //Metodo que utilizaré para restar la vida de un pokemon.
}