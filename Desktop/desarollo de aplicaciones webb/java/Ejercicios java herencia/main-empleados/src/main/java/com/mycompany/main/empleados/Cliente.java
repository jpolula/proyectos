/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.empleados;

/**
 *
 * @author Juan Pedro
 */
public class Cliente 
{
    private String nombre;
    private int telefono;
    private String apellidos;
    
    public Cliente(String nom,int tel,String ape)
    {
        nombre=nom;
        telefono=tel;
        apellidos=ape;
    }
    
    public String getNombre()
    {
        return nombre;
    }
    
    public int getTelefono()
    {
        return telefono;
    }
    
    public void setTelefono(int nuevoTelefono)
    {
        telefono=nuevoTelefono;
    }
    
    public void mostrar()
    {
        System.out.println("Nombre: " +nombre);
        System.out.println("telefono " +telefono);
        System.out.println("Apellidos: " +apellidos);
    }
}
