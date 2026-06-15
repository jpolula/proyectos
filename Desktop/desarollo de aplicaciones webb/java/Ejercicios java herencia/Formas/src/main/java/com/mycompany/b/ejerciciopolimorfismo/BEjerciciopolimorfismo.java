/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.b.ejerciciopolimorfismo;

/**
 *
 * @author usuario
 */
public class BEjerciciopolimorfismo {

    public static void main(String[] args) 
    {
        Forma f = new Forma("Verde", -3, 5.2, "Forma1");
        
        f.mostrar();
        
        System.out.println("-------------");
        
        Rectangulo r = new Rectangulo("Rojo", 0, 0.1, "Rect1", 5, 5);
        
        r.mostrar();
        
        System.out.println("-------------");
        
        Cuadrado c = new Cuadrado("Ceniza", -1, 1, "Cuad1", 7);
        
        c.mostrar();
        
        
        //Segundo uso de la herencia
        //REGLA DE... cascada, hundimiento, descendencia, cavar, herencia, pozo
        //manivela, gravedad
        Forma f2 = new Cuadrado("Azul", -3, -3, "CuadForma", 5.0);
        
        f2.setcolor("Violeta"); //accedemos a los métodos de Forma porque la variable es de tipo Forma
        f2.getLado(); //NO nos deja a Cuadrado aunque f2 apunta a un Cuadrado
        f2.getarea(); //NO nos deja a los del padre
        f2.getx();//Metodo abstracto de la clase punto.
        
        //1ª solución: funciones abstractas/virtuales
        //PRO: arreglo el problema del polimorfismo
        //CON: no se pueden crear objetos de la clase que tiene un método abstracto
        
        //2ª solución: clase abstracta/virtual
        //PRO: arreglo poli y puedo crear instancias de cualquier clase MENOS la abstracta
        //CON: cada método poli debe ser creado y sobreescrito 
        
        //3ª solución: interfaces
        //PRO: poli, puedo crear instancias de la jerarquía, herencia múltiple
        //CON: su creación
        
//        String[] colores={"rojo","azul","negro","blanco"}; 
//       
//        Forma[] vector=new Forma[4];
//        
//        
//        Rectangulo r=new Rectangulo(colores[2] ,Math.random()*100, Math.random()*100, "Rectangulo",Math.random()*20 +1, Math.random()*20 +1);
//        Elipse e=new Elipse(Math.random()*20 +1, Math.random()*20 +1, colores[1], Math.random()*100, Math.random()*100, "Elipse");
//        Cuadrado c=new Cuadrado(colores[3], Math.random()*100, Math.random()*100, "Cuadrado",Math.random()*20 +1);
//        Circulo cir=new Circulo(colores[0], Math.random()*100, Math.random()*100, "Circulo", Math.random()*20 +1);
//        
//        vector[0]=r;
//        vector[1]=e;
//        vector[2]=c;
//        vector[3]=cir;
//        
//        double x=Math.random()*100 +1;
//        double y=Math.random()*100 +1;
//        
//        for(int i=0;i<vector.length;i++)
//        {
//            vector[i].setcolor(colores[1]);
//            vector[i].setforma(x, y);
//        }
//        double ladoMayor=Math.random()*20 +1;
//        double ladoMenor=Math.random()*20 +1;
//        double puntox = Math.random()*100;
//        double puntoy = Math.random()*100;
//        
//        Rectangulo rec1=new Rectangulo(colores[1],puntox,puntoy,"Rectangulo",ladoMenor,ladoMayor);
//        Rectangulo rec2=new Rectangulo(colores[1],puntox,puntoy,"Rectangulo",ladoMenor,ladoMayor);
//        
//        System.out.println(rec1.equals(rec2));
        
    }
}
