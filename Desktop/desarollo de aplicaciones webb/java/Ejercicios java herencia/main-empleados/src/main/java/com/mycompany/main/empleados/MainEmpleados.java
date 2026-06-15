/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.main.empleados;

/**
 *
 * @author Juan Pedro
 */
public class MainEmpleados {

    public static void main(String[] args) {
       Empleado e1=new Empleado("Rodrigo", "Perez", "Ramirez", "8778787s", "Calle noseque", 5, 654545487, 1000.00);
      
       Empleado e2=new Secretario("Pepe", "Granados", "Castro", "875443464S", "Calle daigual", 8, 6454, 1520.00, e1, 89, 98648464);
       
       
    }
}
