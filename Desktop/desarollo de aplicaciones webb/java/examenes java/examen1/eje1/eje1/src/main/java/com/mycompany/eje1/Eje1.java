/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.eje1;

import java.util.Scanner;

/**
 *
 * @author Juan Pedro
 */
public class Eje1 {

    public static void main(String[] args) 
    {
        boolean capicua = false;
        boolean simetrico = false;
      System.out.println("Escriba un número:");
      int num = new Scanner(System.in).nextInt();
      
      int u = num % 10;
      num /= 10;
      int d = num %10;
      num /= 10;
      int c = num%10;
      num /= 10;
      int um = num;
      
      if (u == um && d==c)
      {
         capicua = true;
      } 
      else if (um==0 && u==c) 
      {
         capicua = true;
      }
      else if (um==0 && c==0 && d==u)
      {
         capicua = true;
      } else if (um==0 && c==0 && d==0) 
      {
         capicua = true;
      }else if(c==2||c==5&&u==2||u==5)
      {
          simetrico=true;
      }
      else if(c==2||c==5&&d==2||d==5)
      {
          simetrico=true;
      }
      else if(u==2||u==5&&d==2||d==5)
      {
          simetrico=true;
    
            if (capicua==true)
            {
               System.out.println("El número es capicua");
            } 
            else
            {
               System.out.println("No es capicua");
            }

            if(simetrico==true)
            {
                System.out.println("Es simetrico");
            }
            else
            {
                System.out.println("No es simétrico");
             }
      }
    }
}
