/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.empleados;

/**
 *
 * @author Juan Pedro
 */
public class Secretario extends Empleado
{
    protected int numFax;
    protected int aumento=5;
    Despacho despacho;
    
    
    public Secretario(String nom,String ape1,String ape2,String id,String adress,int anioEmp,int telephone,double sueldito,Empleado supervi,int despacho,int fax)
    {
        super(nom, ape1, ape2, id, adress, anioEmp, telephone, sueldito, supervi);
        numFax=fax;
    }
    
    public int getnumeroFax()
    {
        return numFax;
    }
    
    public int getAumento()
    {
        return aumento;
    }
    public void setNumeroFax(int nuevoFax)
    {
        numFax=nuevoFax;
    }
    
    public void setDespacho(Despacho nuevo)
    {
        despacho=nuevo;
    }
    @Override
    public void mostrar()
    {
        super.mostrar();
        System.out.println("Puesto en la empresa : Secretario");
        System.out.println("numero de FAX " +numFax);
    }
    
}
