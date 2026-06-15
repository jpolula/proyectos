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
public class ComparaEx implements Comparator 
{
    public int compare(Object o1,Object o2)
    {
        
    {
        Pichu p1=(Pichu) o1;
        Pichu p2=(Pichu) o2;
        if(p1.getExp()<p2.getExp())
        {
            return -1;
        }
        else
        {
            if(p1.getExp()>p2.getExp())
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

}
