/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.empleados;

import java.util.Arrays;

/**
 *
 * @author Juan Pedro
 */
public class JefeZona extends Empleado{
    Despacho despac;
    Secretario secre;
    Vendedor vendedores[];
    Coche coche;
    protected int aumento=20;
    
    public JefeZona(String nom,String ape1,String ape2,String id,String adress,int anioEmp,int telephone,double sueldito,Despacho d,Secretario s,Coche c)
    {
        super(nom, ape1, ape2, id, adress, anioEmp, telephone, sueldito);
        despac=d;
        secre=s;
        coche=c;
        vendedores=new Vendedor[0];
    }
    
    public Secretario getSecretario()
    {
        return secre;
    }
    
    public void setSecretario(Secretario nuevoSecretario)
    {
        secre=nuevoSecretario;
    }
    
    public void setCoche(Coche nuevoCoche)
    {
        coche=nuevoCoche;
    }
    
    public int getAumento()
    {
        return aumento;
    }
    public void mostrar()
    {
        System.out.println("Puesto: Jefe de zona");
        super.mostrar();
    }
    
    public void addVendedor(Vendedor v)
    {
        vendedores=Arrays.copyOf(vendedores, vendedores.length+1);
        vendedores[vendedores.length-1]=v;
    }
    
    public void borrarVendedor(Vendedor v)
    {
        Vendedor copia[]=new Vendedor[0];
        for(int i=0;i<vendedores.length;i++)
        {
            if(vendedores[i]!=v)
            {
                copia=Arrays.copyOf(copia, copia.length+1);
                copia[copia.length-1]=vendedores[i];
            }
        }
        vendedores=copia;
    }
    
}
