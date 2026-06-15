/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.proyecto;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.TreeMap;

/**
 *
 * @author Juan Pedro
 */
public class Modelo 
{
    private String driver;
    private String ip;
    private String baseDeDatos;
    private String puerto;
    private String usuario;
    private String contraseña;
    private Connection conexion;
    
    public Modelo()
    {
        
    }
    
    private boolean open() //Metodo para conectarme a la base de datos
    {
        driver=Singleton.getInstancia().getDriver();
        ip=Singleton.getInstancia().getIp();
        baseDeDatos=Singleton.getInstancia().getBd();
        puerto=Singleton.getInstancia().getPuerto();
        usuario=Singleton.getInstancia().getUsuario();
        contraseña=Singleton.getInstancia().getContraseña();
        String url = driver + ":mysql" + "://" + ip + ":" + puerto + "/" + baseDeDatos;
        
        try 
        {
            conexion = DriverManager.getConnection(url, usuario, contraseña);
            return true;
        } catch (Exception ex)
        {
           System.out.println(ex.getMessage());
           return false;
        }
    }
    
    private void close() throws SQLException //Metodo para cerrar la conexion a la base de datos
    {
        conexion.close();
    }
    
   public boolean test()  //Metodo para comprobar si la conexion ha sido exitosa o no
   {
        try 
        {
            if (open()==true)
            {
                // Si la conexión se abre correctamente, devolvemos true
                close(); // Cerramos la conexión
                return true;
            }
            else 
            {
                // Si no se puede abrir la conexión, devolvemos false
                return false;
            }
        } catch (SQLException ex) {
            // Si ocurre alguna excepción durante el proceso, imprimimos el mensaje de error y devolvemos false
            System.out.println("Error al probar la conexión: " + ex.getMessage());
            return false;
    }
}
    public List<Map<String,String>> obtenerTodosPokemon() throws SQLException //Metodo que me devuelve una lista de mapas con toda la informacion de todos los pokemon
    {
            List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
            String query = "SELECT * FROM pokemon";
            statement = conexion.createStatement();
            resultSet = statement.executeQuery(query);
            while (resultSet.next()) 
             { 
                 Map<String,String>mapa=new TreeMap<>();
                 mapa.put("ID_Pokemon",resultSet.getString("ID_Pokemon"));
                 mapa.put("Pokemon",resultSet.getString("Pokemon"));
                 mapa.put("HP",resultSet.getString("HP"));
                 mapa.put("Attack",resultSet.getString("Attack"));
                 mapa.put("Defense",resultSet.getString("Defense"));
                 mapa.put("Special_Attack",resultSet.getString("Special_Attack"));
                 mapa.put("Special_Defense",resultSet.getString("Special_Defense"));
                 mapa.put("Speed",resultSet.getString("Speed"));
                 lista.add(mapa);
             }
            
            close();
            
            return lista;
    }
    
    public List<Map<String,String>> obtenerInformacionPokemon(String pokemon) throws SQLException
            //Metodo que me devuelve  la información de un pokemon mediante el argumento pasado como parametro
    {
            List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
            String query = "SELECT * FROM pokemon WHERE pokemon = '" + pokemon + "'";
            statement = conexion.createStatement();
            resultSet = statement.executeQuery(query);
            while (resultSet.next()) 
             { 
                 Map<String,String>mapa=new TreeMap<>();
                 mapa.put("ID_Pokemon",resultSet.getString("ID_Pokemon"));
                 mapa.put("Pokemon",resultSet.getString("Pokemon"));
                 mapa.put("HP",resultSet.getString("HP"));
                 mapa.put("Attack",resultSet.getString("Attack"));
                 mapa.put("Defense",resultSet.getString("Defense"));
                 mapa.put("Special_Attack",resultSet.getString("Special_Attack"));
                 mapa.put("Special_Defense",resultSet.getString("Special_Defense"));
                 mapa.put("Speed",resultSet.getString("Speed"));
                 lista.add(mapa);  
             }
            
            close();
            
            return lista;
    }
    
    public List<Map<String,String>> obtenerTodosEntrenadores() throws SQLException//Metodo que me devuelve la informacion de todos los entrenadores
    {
            List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
            String query = "SELECT * FROM Trainers";
            statement = conexion.createStatement();
            resultSet = statement.executeQuery(query);
            while (resultSet.next()) 
             { 
                 Map<String,String>mapa=new TreeMap<>();
                 mapa.put("ID_Trainer",resultSet.getString("ID_Trainer"));
                 mapa.put("Trainer",resultSet.getString("Trainer"));
                 mapa.put("Badges",resultSet.getString("Badges"));
                 lista.add(mapa);
             }
            
            close();
            
            return lista;
    }
    
    public List<Map<String,String>> obtenerPokemon1Entrenador(String entrenador) throws SQLException //Metodo que me devuelve una lista de mapas con los pokemon de 1 entrenador pasado como argumento el nombre del entrenador
    {
            List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
            String query = "SELECT p.Pokemon FROM Trainers t JOIN Rel_Trainer_Pokemon rtp ON t.ID_Trainer = rtp.ID_Trainer JOIN Pokemon p ON rtp.ID_Pokemon = p.ID_Pokemon WHERE t.Trainer = '" + entrenador + "'";
            statement = conexion.createStatement();
            resultSet = statement.executeQuery(query);
            
            while (resultSet.next())  //Para cada entrenador....
             { 
                 Map<String,String>mapa=new TreeMap<>();
                 mapa.put("Pokemon",resultSet.getString("Pokemon"));
                 lista.add(mapa);
             }
            
            close();
            
            return lista;
    }
    
    public int obtenerIdEntrenador(String entrenadorEle) throws SQLException  //Metodo que me devuelve el id de un entrenaodr pasado como argumento.
    {
        int valor = 0;
        Statement statement = null;
        ResultSet resultSet = null;
        
        open();

        String query = "SELECT ID_Trainer FROM Trainers WHERE Trainer = '" + entrenadorEle + "'";
        statement = conexion.createStatement();
        resultSet = statement.executeQuery(query);

        while(resultSet.next()) { 
            valor = resultSet.getInt("ID_Trainer");
        }

        close();
        return valor;
}
    
     public String obtenerTipoPokemon(String pokemonEle) throws SQLException  //Metodo que me devuelve el tipo de pokemon pasando su nombre como argumento
    {
        String tipo="";
        Statement statement = null;
        ResultSet resultSet = null;

        open();

        String query = "SELECT Types.Type FROM Pokemon JOIN Rel_Pokemon_Type ON Pokemon.ID_Pokemon = Rel_Pokemon_Type.ID_Pokemon JOIN Types ON Rel_Pokemon_Type.ID_Type = Types.ID_Type WHERE Pokemon.Pokemon = '" + pokemonEle + "'";
        statement = conexion.createStatement();
        resultSet = statement.executeQuery(query);

        while(resultSet.next()) { 
            tipo = resultSet.getString("Type");
        }

        close();
        return tipo;
}
     
     public List<Map<String,String>> obtenerEstadisticasPokemonDeEntrenador(int entrenadorId) throws SQLException //Metodo que me devuelve una lista de mapas con los pokemon de 1 entrenador pasado como argumento el id del entrenador.
    {
            List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
           String query = "SELECT p.ID_Pokemon, p.Pokemon, p.HP, p.Attack, p.Defense, p.Special_Attack, p.Special_Defense, p.Speed " +
               "FROM Trainers t " +
               "JOIN Rel_Trainer_Pokemon rtp ON t.ID_Trainer = rtp.ID_Trainer " +
               "JOIN Pokemon p ON rtp.ID_Pokemon = p.ID_Pokemon " +
               "WHERE t.ID_Trainer = '" + entrenadorId + "'";
            statement = conexion.createStatement();
            resultSet = statement.executeQuery(query);
            
            while (resultSet.next())  //Para cada entrenador....
             { 
                 Map<String,String>mapa=new TreeMap<>();
                 mapa.put("ID_Pokemon",resultSet.getString("ID_Pokemon"));
                 mapa.put("Pokemon",resultSet.getString("Pokemon"));
                 mapa.put("HP",resultSet.getString("HP"));
                 mapa.put("Attack",resultSet.getString("Attack"));
                 mapa.put("Defense",resultSet.getString("Defense"));
                 mapa.put("Special_Attack",resultSet.getString("Special_Attack"));
                 mapa.put("Special_Defense",resultSet.getString("Special_Defense"));
                 mapa.put("Speed",resultSet.getString("Speed"));
                 lista.add(mapa);
             }
            
            close();
            
            return lista;
    }
     
     public int obtenerIdTipo(String pokemonEle) throws SQLException  //Metodo que me devuelve el tipo de pokemon pasando su nombre como argumento
    {
        int tipo=0;
        Statement statement = null;
        ResultSet resultSet = null;

        open();

        String query = "SELECT t.ID_Type FROM Pokemon p JOIN Rel_Pokemon_Type t ON p.ID_Pokemon = t.ID_Pokemon WHERE p.Pokemon = '" + pokemonEle + "'";
        statement = conexion.createStatement();
        resultSet = statement.executeQuery(query);

        while(resultSet.next()) { 
            tipo = resultSet.getInt("ID_Type");
        }

        close();
        return tipo;
}
     
     public List<Map<String,String>> obtenerEstadisticasPokemonAleatorio() throws SQLException //Funcion que me devuelve una lista de mapas con un pokemon dependiendo del id que me pasen como argumentoq
     {
          int numAleatorio=(int) (Math.random()*6+1);
          List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
           String query = "SELECT ID_Pokemon, Pokemon, HP, Attack, Defense, Special_Attack, Special_Defense, Speed FROM Pokemon ORDER BY RAND() LIMIT " +numAleatorio;
            statement = conexion.createStatement();
            resultSet = statement.executeQuery(query);
            
            while (resultSet.next())  //Para cada Pokemon.
             { 
                 Map<String,String>mapa=new TreeMap<>();
                 mapa.put("ID_Pokemon",resultSet.getString("ID_Pokemon"));
                 mapa.put("Pokemon",resultSet.getString("Pokemon"));
                 mapa.put("HP",resultSet.getString("HP"));
                 mapa.put("Attack",resultSet.getString("Attack"));
                 mapa.put("Defense",resultSet.getString("Defense"));
                 mapa.put("Special_Attack",resultSet.getString("Special_Attack"));
                 mapa.put("Special_Defense",resultSet.getString("Special_Defense"));
                 mapa.put("Speed",resultSet.getString("Speed"));
                 lista.add(mapa);
             }
            
            close();
            
            return lista;
     }
     
     public List<Map<String,String>> obtenerInformacionPokemon(String pokemon[]) throws SQLException
            //Metodo que me devuelve  la información de un pokemon mediante el argumento pasado como parametro
    {
            List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
            for(int i=0;i<pokemon.length;i++)
            {
                String query = "SELECT * FROM pokemon WHERE pokemon = '" + pokemon[i] + "'";
                statement = conexion.createStatement();
                resultSet = statement.executeQuery(query);
                while (resultSet.next()) 
             { 
                 Map<String,String>mapa=new TreeMap<>();
                 mapa.put("ID_Pokemon",resultSet.getString("ID_Pokemon"));
                 mapa.put("Pokemon",resultSet.getString("Pokemon"));
                 mapa.put("HP",resultSet.getString("HP"));
                 mapa.put("Attack",resultSet.getString("Attack"));
                 mapa.put("Defense",resultSet.getString("Defense"));
                 mapa.put("Special_Attack",resultSet.getString("Special_Attack"));
                 mapa.put("Special_Defense",resultSet.getString("Special_Defense"));
                 mapa.put("Speed",resultSet.getString("Speed"));
                 lista.add(mapa);  
             }
                
            }
           
            close();
            
            return lista;
    }
     
     public List<Map<String,String>> obtenerInformacionPokemonPersonalizado(String pokemon[]) throws SQLException
            //Metodo que me devuelve  una lista de mapas con la informacion de los pokemon personalizados obviando los pokemon que no han sido escogidos
             //PRE:/Necesario que el usuario haya escogido al menos 1 pokemon.
    {
            List<Map<String,String>>lista=new ArrayList<Map<String,String>>();
            
            Statement statement = null;
            ResultSet resultSet = null;
            
            open();
            
            for(int i=0;i<pokemon.length;i++)
            {
                if(pokemon[i]!=null)
                {
                  String query = "SELECT * FROM pokemon WHERE pokemon = '" + pokemon[i] + "'";
                  statement = conexion.createStatement();
                  resultSet = statement.executeQuery(query);
                  while (resultSet.next()) 
                   { 
                        Map<String,String>mapa=new TreeMap<>();
                        mapa.put("ID_Pokemon",resultSet.getString("ID_Pokemon"));
                        mapa.put("Pokemon",resultSet.getString("Pokemon"));
                        mapa.put("HP",resultSet.getString("HP"));
                        mapa.put("Attack",resultSet.getString("Attack"));
                        mapa.put("Defense",resultSet.getString("Defense"));
                        mapa.put("Special_Attack",resultSet.getString("Special_Attack"));
                        mapa.put("Special_Defense",resultSet.getString("Special_Defense"));
                        mapa.put("Speed",resultSet.getString("Speed"));
                        lista.add(mapa);  
                    }
                }
           }
            close();
            return lista;
    }
     
     public String obtenerTipo(String n) throws SQLException  //Metodo que me devuelve el tipo de pokemon parasandole el nombre del pokemon
    {
        String tipo="";
        Statement statement = null;
        ResultSet resultSet = null;
        
        open();

        String query = "SELECT Types.Type " +
                         "FROM Pokemon " +
                         "JOIN Rel_Pokemon_Type ON Pokemon.ID_Pokemon = Rel_Pokemon_Type.ID_Pokemon " +
                         "JOIN Types ON Rel_Pokemon_Type.ID_Type = Types.ID_Type " +
                         "WHERE Pokemon.Pokemon = '" + n + "'";
        statement = conexion.createStatement();
        resultSet = statement.executeQuery(query);

        while(resultSet.next())
        { 
            tipo = resultSet.getString("Type");
        }

        close();
        return tipo;
}
     
      public String obtenerTipoDependiendodelIdTipo( int idTipo) throws SQLException  //Metodo que me devuelve el tipo de pokemon parasandole el id del tipo. La he utilizado por tux, ya que no esta en la base de datos
    {
        String tipo="";
        Statement statement = null;
        ResultSet resultSet = null;
        
        open();

        String query = "SELECT Type FROM Types WHERE ID_Type = " + idTipo;
        statement = conexion.createStatement();
        resultSet = statement.executeQuery(query);

        while(resultSet.next())
        { 
            tipo = resultSet.getString("Type");
        }

        close();
        return tipo;
}
      
      public String obtenerNombreEntranador( int idEntrenador) throws SQLException  //Metodo que me devuelve el tipo de pokemon parasandole el id del tipo. La he utilizado por tux, ya que no esta en la base de datos
    {
        String tipo="";
        Statement statement = null;
        ResultSet resultSet = null;
        
        open();
        
        String query = "SELECT Trainer FROM Trainers WHERE ID_Trainer = " +idEntrenador;
        statement = conexion.createStatement();
        resultSet = statement.executeQuery(query);

        while(resultSet.next())
        { 
            tipo = resultSet.getString("Trainer");
        }

        close();
        return tipo;
    }
      
      public Double ObtenerMediaMedallas( ) throws SQLException  //Metodo que me devuelve el tipo de pokemon parasandole el id del tipo. La he utilizado por tux, ya que no esta en la base de datos
    {
        Double resul=0.0;
        Statement statement = null;
        ResultSet resultSet = null;
        
        open();
        
        String query = "SELECT AVG(Medals) AS Average_Medals FROM Trainer";
        statement = conexion.createStatement();
        resultSet = statement.executeQuery(query);

        while(resultSet.next())
        { 
            resul = resultSet.getDouble("Average_Medals");
        }

        close();
        return resul;
    }
  
}
