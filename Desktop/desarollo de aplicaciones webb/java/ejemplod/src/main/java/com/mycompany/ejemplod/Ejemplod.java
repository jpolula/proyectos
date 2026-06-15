/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.ejemplod;

import java.util.Scanner;

/**
 *
 * @author Juan Pedro
 */
public class Ejemplod {

    public static void main(String[] args) {
     Scanner sc=new Scanner(System.in);
     System.out.println("Dime un numero");
     int numero=sc.nextInt();
    char matriz[][]=new char [numero][numero];
     
    for(char c='A';c<='Z' &&c>numero;c++)
    {
        System.out.print(" ");
        System.out.println((int) c+c);
        numero--;
    
    }
     

    }
}
