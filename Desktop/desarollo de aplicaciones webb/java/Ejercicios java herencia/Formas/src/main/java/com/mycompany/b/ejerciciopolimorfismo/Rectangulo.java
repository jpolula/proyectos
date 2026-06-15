/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.b.ejerciciopolimorfismo;

import java.text.DecimalFormat;

/**
 *
 * @author usuario
 */
public class Rectangulo extends Forma {
    protected double ladoMayor;
    protected double ladoMenor;

    public Rectangulo(String c, double x, double y, String n,double lme,double lma)
    {
        super(c, x, y, n);
        ladoMayor=lma;
        ladoMenor=lme;
    }
    
    public double getLadoMayor()
    {
        return ladoMayor;
    }
    
    public double getLadoMenor()
    {
        return ladoMenor;
    }
    
    public void setLadoMayor(double v)
    {
        ladoMayor = v;
    }
    
    public void setLadoMenor(double v)
    {
        ladoMenor = v;
    }
    
    @Override
    public void mostrar()
    {
        super.mostrar();
        System.out.println("Lado mayor: " + ladoMayor);
        System.out.println("Lado menor: " + ladoMenor);
    }
    
    @Override
    public double getarea()
    {
        double resul;
        resul=ladoMayor*ladoMenor;
        return resul;
    }
    
    public double getperimetro()
    {
        double resul;
        resul=((2*ladoMayor)+(2*ladoMenor));
        return resul;
    }
    
    public void settamano(double factor)
    {
        if(factor==2)
        {
            ladoMayor=ladoMayor*2;
            ladoMenor=ladoMenor*2;
        }
        else if(factor==0.5)
        {
            ladoMayor=ladoMayor*0.5;
            ladoMenor=ladoMenor*0.5;
        }
    }
    @Override
    public boolean equals(Object otro)
    {
        DecimalFormat df = new DecimalFormat("#.00");
        Rectangulo otroRectangulo=(Rectangulo) otro;
        boolean iguales = false;
        if(getnombre() == otroRectangulo.getnombre())
        {
            if(getcolor() == otroRectangulo.getcolor())
            {
                if(df.format(punto.getx()) == df.format(otroRectangulo.punto.getx()) && df.format(punto.gety())== df.format(otroRectangulo.punto.gety()))
                {
                    iguales=true;
                }
            }
        }
        else
        {
            iguales=false;
        }
        return iguales;
    }
    
    
}
