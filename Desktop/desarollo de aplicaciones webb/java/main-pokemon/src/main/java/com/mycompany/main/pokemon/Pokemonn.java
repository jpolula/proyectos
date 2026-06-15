/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Interface.java to edit this template
 */
package com.mycompany.main.pokemon;

/**
 *
 * @author Juan Pedro
 */
public interface Pokemonn
{
    public  void mostrar();
    public  int ataqueRapido();
    public  void cambiarExperiencia(int e); //Metodo que usaré cada vez que el pokemon alcance 100 de daño.
    public void restarSalud(int d); //Metodo que utilizaré para restar la vida de un pokemon.
    public boolean equals(Object otro);
}
