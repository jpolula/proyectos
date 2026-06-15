/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.mavenproject1;

import java.text.SimpleDateFormat;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;

public class Mavenproject1 {
    
    public static void main(String[] args)
    {
       
                
        TextoENG te = new TextoENG();
        
        System.out.println(te.contarVocales("En un velero bergantín"));
        
        TextoES tes = new TextoES();
        
        System.out.println(tes.contarVocales("En un velero bergantín"));

    }
    
    
}
