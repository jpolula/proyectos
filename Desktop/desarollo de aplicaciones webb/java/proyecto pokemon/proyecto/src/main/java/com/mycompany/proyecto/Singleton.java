/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.proyecto;

import java.util.Arrays;

/**
 *
 * Clase para el intercambio de información entre diferentes ventanas
 * En nombre de la clase puede ser el que se quiera
 */
public class Singleton 
{
   private final static Singleton INSTANCIA = new Singleton();
   
   private Singleton(){}
   //El constructor es privado para evitar que se puedan crear instancias de esta clase
   
   public static Singleton getInstancia()
   {
       return INSTANCIA;
   }
   
   //Ahora definimos atributos, getters y setters que queramos usar para intercambiar información
   //INFORMACION DE LA BASE DE DATOS
   private String driver;
   private String ip;
   private String bd;
   private String puerto;
   private String usuario;
   private String contraseña;
   private int puertoBase;
   
   //INFORMACION DEL ENTRENADOR
   private int entrenador;
   
   //INFORMACION DE  LA DIFICULTAD
   private int dificultad;
   //NOMBRES DE LOS POKEMON ALEATORIOS
   private String pokemonAleatorios[]=new String [0]; //Vector que usare para los pokemon aleatorios
   
   private String pokemonPersonalizados[]=new String[6]; //Vector donde metere los nombres de los pokemon personalizados
   
   private String nombrePokemonPersonalizado; //Nombre del pokemon que guardo cuando el usuario elige un pokemon personalizado
   
   public String getPokemonPersonalizado()
   {
       return nombrePokemonPersonalizado;
   }
   
   public void setPokemonPersonalizado(String n) 
   {
      nombrePokemonPersonalizado=n;
   }
     
    public void añadirPokemon(String nombrePokemon) //Metodo que utilizo para añadir pokemon de forma aleatoria reservandole memoria.
    {
         pokemonAleatorios = Arrays.copyOf(pokemonAleatorios, pokemonAleatorios.length+1);
         pokemonAleatorios[pokemonAleatorios.length-1] = nombrePokemon;
    }
    
     public void limpiarPokemonAleatorios()  //Metodo que me limpia el array de string
    {
         pokemonAleatorios=new String[0];
    }
     
      public void añadirPokemonPersonalizado(String nombrePokemon,int pos)
    {
         pokemonPersonalizados[pos]=nombrePokemon;
    }
    
      public String[] getPokemonPersonalizados()
      {
          return pokemonPersonalizados;
      }
     public void limpiarPokemonPersonalizados()  //Metodo que me limpia el array de string
    {
         pokemonPersonalizados=new String[6];
    }
     
    public String[] getPokemonAleatorios()
    {
        return pokemonAleatorios;
    }
     
   public String getDriver()
   {
       return driver;
   }
   
   public void setDriver(String d)
   {
       driver=d;
   }
   
   public String getIp()
   {
       return ip;
   }
   
   public void setIp(String nueva)
   {
       ip=nueva;
   }
   
   public String getBd()
   {
       return bd;
   }
   
   public void setBd(String nueva)
   {
       bd=nueva;
   }
   
   public String getPuerto()
   {
       return puerto;
   }
    
   public void setPuerto(String p)
   {
       puerto =p;
   }
   
   public String getUsuario()
   {
       return usuario;
   }
   
   public void setUsuario(String nuevoUsuario)
   {
       usuario=nuevoUsuario;
   }
   
   public String getContraseña()
   {
       return contraseña;
   }
   
   public void setContraseña(String nuevaContraseña)
   {
       contraseña=nuevaContraseña;
   }
   
   public void setPuertoBase(int puerto)
   {
       puertoBase=puerto;
   }
   
   public int getPuertoBase()
   {
       return puertoBase;
   }
   
   public int getEntrenador()
   {
       return entrenador;
   }
   
   public void setEntrenador(int ent)
   {
       entrenador=ent;
   }
   
    public int getDifficultad()
   {
       return dificultad;
   }
   
   public void setDificultad(int dif)
   {
       dificultad=dif;
   }
}
