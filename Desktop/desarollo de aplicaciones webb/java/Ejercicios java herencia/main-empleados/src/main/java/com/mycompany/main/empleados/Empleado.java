/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.empleados;

/**
 *
 * @author Juan Pedro
 */
public class Empleado implements EmpleadoInterfaz
{
    protected String nombre;
    protected String apellido1;
    protected String apellido2;
    protected String dni;
    protected String direccion;
    protected int aniosAntiguedad; //Atributo que usaré para calcular el salario de todos los empleados dependiendo del porcentaje
    protected int telefono;
    protected double salario;
    Empleado supervisor;
    
    
    public Empleado()
    {
    
    }
    
    public Empleado(String name,String a1,String a2,String nif,String direcc,int aniosEnEmpresa,int tel,double salar,Empleado superv)
    {
        nombre=name;
        apellido1=a1;
        apellido2=a2;
        dni=nif;
        direccion=direcc;
        aniosAntiguedad=aniosEnEmpresa;
        telefono=tel;
        salario=salar;
        supervisor=superv;
    }
    
    public Empleado(String name,String a1,String a2,String nif,String direcc,int aniosEnEmpresa,int tel,double salar)
    {
        nombre=name;
        apellido1=a1;
        apellido2=a2;
        dni=nif;
        direccion=direcc;
        aniosAntiguedad=aniosEnEmpresa;
        telefono=tel;
        salario=salar;
    }
    
    public String getNombre()
    {
        return nombre;
    }
    
    public void setNombre(String nuevoNombre)
    {
        nombre=nuevoNombre;
    }
    
    public String getApellido1()
    {
        return apellido1;
    }
    
    public void setApellido1(String nuevoApellido)
    {
        apellido1=nuevoApellido;
    }
    
    public String getApellido2()
    {
        return apellido2;
    }
    
    public void setApellido2(String nuevoApellido)
    {
        apellido2=nuevoApellido;
    }
    
    public String getDireccion()
    {
        return direccion;
    }
    
    public void setDireccion(String nuevaDireccion)
    {
        direccion=nuevaDireccion;
    }
    
    public int getTelefono()
    {
        return telefono;
    }
    
    public void setTelefono(int nuevoTelefono)
    {
        telefono=nuevoTelefono;
    }
    
    public double getSalario()
    {
        return salario;
    }
    public Empleado getSupervisor()
    {
        return supervisor;
    }
    
    public int getAntiguedad()
    {
        return aniosAntiguedad;
    }
    
    public void setEmpleado(Empleado nuevoSupervisor)
    {
        supervisor=nuevoSupervisor;
    }
    
    public void mostrar()
    {
        System.out.println("Nombre: " +nombre);
        System.out.println("Apellido 1 : " +apellido1);
        System.out.println("Apellido 2: " +apellido2);
        System.out.println("DNI: " +dni);
        System.out.println("direccion: " +direccion);
        System.out.println("Años en la empresa: " +aniosAntiguedad);
        System.out.println("Telefono: " +telefono);
        System.out.println("Salario: " +salario);
    }
    
    /*public void mostrarSupervisor()
    {
        supervisor.mostrar();
    }*/
    
    public void incrementarSalario(int porcentajeAño)
    {
        double porcentajeTotal=aniosAntiguedad*porcentajeAño;
        double incremento=salario/100*porcentajeTotal;
        salario+=incremento;
    } 
}

