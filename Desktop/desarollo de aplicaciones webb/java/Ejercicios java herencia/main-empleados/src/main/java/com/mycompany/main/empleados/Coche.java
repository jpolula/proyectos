/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.empleados;

/**
 *
 * @author Juan Pedro
 */
public class Coche 
{
    private String marca;
    private String matricula;
    private String modelo;
    
    public Coche(String mar,String matri,String model)
    {
        marca=mar;
        matricula=matri;
        modelo=model;
    }
    
    public String getMarca()
    {
        return marca;
    }
    
    public String getMatricula()
    {
        return matricula;
    }
    
    public String getModelo()
    {
        return modelo;
    }
    
    public void setMatricula(String nuevaMatricula)
    {
        matricula=nuevaMatricula;
    }
}
