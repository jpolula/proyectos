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
public class Vendedor extends Empleado
{
    Coche cocheEmpresa;
    protected int telefonoEmpresa;
    protected String areaVenta;
    Cliente clientes[];
    protected int comision;
    protected int aumento=10;
    
    public Vendedor(String nom,String ape1,String ape2,String id,String adress,int anioEmp,int telephone,double sueldito,Coche c,int numEmpresa,String areaVe)
    {
        super(nom, ape1, ape2, id, adress, anioEmp, telephone, sueldito);
        cocheEmpresa=c;
        telefonoEmpresa=numEmpresa;
        areaVenta=areaVe;
        clientes=new Cliente[0];
    }
    
    public Coche getCoche()
    {
        return cocheEmpresa;
    }
    
    public int getTelefonoEmpresa()
    {
        return telefonoEmpresa;
    }
    
    public String getAreaVenta()
    {
        return areaVenta;
    }
    
    public Cliente getCliente(int numCliente)
    {
        return clientes[numCliente-1];
    }
    
    public int getAumento()
    {
        return aumento;
    }
    
    public void imprimir()
    {
        System.out.println("Puesto en la empresa: Vendedor");
        super.mostrar();
    }
    
    public void añadirCliente(Cliente c)
    {
        clientes=Arrays.copyOf(clientes, clientes.length+1);
        clientes[clientes.length-1]=c;
    }
    
    public void borrarCliente(Cliente c) //Preguntar a Alejandro
    {
        Cliente copia[]=new Cliente[0];
        for(int i=0;i<clientes.length;i++)
        {
            if(clientes[i]!=c)
            {
                copia=Arrays.copyOf(copia, copia.length+1);
                copia[copia.length-1]=clientes[i];
            }
        }
        clientes=copia;
    }
    
    public void setCoche(Coche nuevoCoche)
    {
        cocheEmpresa=nuevoCoche;
    }
     
}
