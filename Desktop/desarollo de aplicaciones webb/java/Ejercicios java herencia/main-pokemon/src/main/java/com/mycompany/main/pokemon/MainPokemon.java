/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.main.pokemon;

/**
 *
 * @author Juan Pedro
 */
public class MainPokemon
{

    public static void main(String[] args) 
    {
        Pichu pichu=new Pichu("Ash");
        
        pichu.mostrar();
        
        System.out.println("--------------------------------------------");
        
        int danio=0; //Variable que usaremos para guardar el daño
        
        while(pichu.getExp()<=49)
        {
            int dañoMovimiento=pichu.ataqueRapido(); //variable que usaremos para saber el daño que haremos con el movimiento de Pichu.
            danio+=dañoMovimiento;
            if(danio>=100) //Si el daño es mayor a 100 sumaremos 15 de experiencia al objeto creado.
            {
                pichu.cambiarExperiencia(15);
                danio=0; //Le restamos 100 ya que al conseguir experiencia se reinicia el daño y dejamos el resto de lo que haya quedado.
                if(pichu.getExp()>=50)
                {
                    pichu=new Pikachu(pichu.getExp()); //En el argumento le paso la experiencia que tengo hasta ahora.
                }
            } 
        }
        
        System.out.println("-----------------------------------------------------");
        
        pichu.mostrar(); //Muestro a Pichu evolucionado
        
         while(pichu.getExp()<75)
        {
            int dañoMovimiento=pichu.ataqueRapido(); //variable que usaremos para saber el daño que haremos con el movimiento de Pichu.
            danio+=dañoMovimiento;
            if(danio>=100) //Si el daño es mayor a 100 sumaremos 15 de experiencia al objeto creado.
            {
                pichu.cambiarExperiencia(15);
                danio=0; //Le restamos 100 ya que al conseguir experiencia se reinicia el daño y dejamos el resto de lo que haya quedado.
                if(pichu.getExp()>=75)
                {
                    pichu=new Raichu(pichu.getExp()); //Si la experiencia es mayor a 75 evoluciona a Raichu. Le pasamos la exp que tiene hasta ahora el Pokemon.
                }
            } 
        }
         System.out.println("---------------------------------------------------------------------------");
         pichu.mostrar(); //Muestro a Pikachu evolucionado.
        
         //Haremos un ejemplo de combate entre 2 pokemon.
         
         Pichu raichu=new Raichu();
         
         Pichu pikachu=new Pikachu();
         while(raichu.getSaludActual()>0||pikachu.getSaludActual()>0)
         {
             int daño;
             daño=raichu.ataqueRapido(); //raichu hace un ataque
             pikachu.restarSalud(daño); //pikachu recibe el golpe y le baja la defensa
             
             daño=pikachu.ataqueRapido();//Turno de Pikachu.
             raichu.restarSalud(daño); //Le restamos la vida a Raichu.
         }
         
         if(pikachu.getSaludActual()>=0) //Si Pikachu no tiene vida ya sabemos que ha ganado Raichu. 
         {
             System.out.println("Gano Raichu");
         }
         else //Si Raichu no tiene vida a ganado Pikachu
         {
             System.out.println("Gano Pikachuuuuu");
         }
         
        Pichu pokemons[]=new Pichu[6];
        for(int i=0;i<pokemons.length;i++)
        {
            pokemons[i]=new Raichu();
        }
         Entrenador e1=new Entrenador("Brock", 700.00, 2, pokemons);
         e1.mostrar();
    }
}
