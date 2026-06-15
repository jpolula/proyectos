/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.main.pokemon;

import java.util.Comparator;

/**
 *
 * @author Juan Pedro
 */
public class ComparaSalud implements Comparator
{
    public int compare(Object primero,Object segundo)
    {
        Pichu p1=(Pichu) primero;
        Pichu p2=(Pichu) segundo;
        if(p1.getSaludActual()<p2.getSaludActual())
        {
            return -1;
        }
        else
        {
            if(p1.getSaludActual()>p2.getSaludActual())
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }
    }
}
