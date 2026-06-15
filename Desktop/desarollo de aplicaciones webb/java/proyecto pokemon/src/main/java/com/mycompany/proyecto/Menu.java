package com.mycompany.proyecto;


import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.RadioButton;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class Menu  
{
    @FXML
    private Button btnBaseDeDatos;
    
    @FXML
    private Button btnIniciarPartida;
    
    @FXML
    private Button btnPokedex;
    
    @FXML
    private Button btnAyuda;
    
    @FXML
    private Button btnAbrirEntrenadores;
    @FXML
    private Button btnCalculadora;
    
    @FXML
    private RadioButton rdbtDificultad1; //Dificultad recluta
    
    @FXML
    private RadioButton rdbtDificultad2; //Dificultad marine
     
    @FXML
    private RadioButton rdbtDificultad3; //Dificultad veterano
      
    @FXML
    private RadioButton rdbtDificultad4; //Dificultad pesadilla

    @FXML
    private void initialize() //En esta función guardaré toda la informacion relacionada con la base de datos.
    {          
    //método que es llamado internamente justo después del constructor
            
    //En el constructor NO SE tiene acceso a las variables enlazadas con @FXML
            
    //En initialize() ya están las variables creadas y son accesibles
            
    //Creo varias variables para tener la información de los datos de la base de datos
        
    }
    
    @FXML
    public void seleccionarDificultad() //Metodo que me devuelve la dificultad que ha escogido el usuario
    {
        // Aquí obtienes la dificultad seleccionada
        int dificultadSeleccionada = obtenerDificultadSeleccionada(); //Guardo la dificultad que ha escogido el usuario

        // Guardo la dificultad seleccionada en el Singleton para su posterior uso en la pantalla principal
        Singleton intercambio = Singleton.getInstancia();
        intercambio.setDificultad(dificultadSeleccionada);
    }
    
    @FXML
    private void volver()//Metodo que me lleva a la conexion de la base de datos
    {
        try
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("BaseDeDatos.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
            
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'btnIniciarPartida'
            Stage stageAcerrar = (Stage) btnIniciarPartida.getScene().getWindow(); 
        
            stageAcerrar.close();
           
        } 
        catch (Exception e)
        {
            System.out.println(e.getMessage());
        }
    }
    
    @FXML
    public void iniciarPartida() //Metodo que me lleva a la ventana de la batalla
    {
        try
        {
            seleccionarDificultad(); //En este metodo ya tengo la dificultad guardada en el singleton para su posterior uso.
            if(Singleton.getInstancia().getEntrenador()==0) //Si el usuario no ha escogido un entrenador jugará por defecto con Ash.
            {
                Singleton.getInstancia().setEntrenador(1);
            }
           
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("principal.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
            
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'btnBaseDeDatos'
            Stage stageAcerrar = (Stage) btnBaseDeDatos.getScene().getWindow(); 
        
            stageAcerrar.close();
            
        } 
        catch (Exception e)  
        {
            System.out.println(e.getMessage());
        }
    }
    
    @FXML
    public void abrirAyuda() //Metodo que abre la ventana ayuda y me cierra la actual
    {
        try
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("ayuda.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
  
            Stage stageAcerrar = (Stage) btnIniciarPartida.getScene().getWindow(); 
        
            stageAcerrar.close();
                
        } 
        catch (Exception e) 
        {
            System.out.println(e.getMessage());
        }
    }
    
    @FXML
    public void abrirPokedex() //Metodo que me abre la pokedex y me cierra el menu
    {
        try
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("pokedex.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
            
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'iniciar partida'
            Stage stageAcerrar = (Stage) btnIniciarPartida.getScene().getWindow(); 
        
            stageAcerrar.close();
            
        } catch (Exception e) 
        {
            System.out.println(e.getMessage());
        }
    }
    
    @FXML
    public void abrirEntrenadores() //Metodo que me abre la ventana de entrenadores cerrandome la de menu
    {  
        try
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("entrenadores.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
            
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'iniciar partida'
            Stage stageAcerrar = (Stage) btnIniciarPartida.getScene().getWindow(); 
        
            stageAcerrar.close(); 
        } 
        catch (Exception e) 
        {
            System.out.println(e.getMessage());
        }
    }
    
    @FXML
    public void abrirCalculadora() //Metodo que me abre la ventana de entrenadores cerrandome la de menu
    {  
        try
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("calculadora.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
            
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'iniciar partida'
            Stage stageAcerrar = (Stage) btnIniciarPartida.getScene().getWindow(); 
        
            stageAcerrar.close(); 
        } 
        catch (Exception e) 
        {
            System.out.println(e.getMessage());
        }
    }
    
    private int obtenerDificultadSeleccionada()  //METODO QUE UTILIZO PARA OBTENER LA DIFICULTAD QUE HA ESCOGIDO EL USUARIO
    {
        if (rdbtDificultad1.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 1
        {
            return 1;
        } 
        else if (rdbtDificultad2.isSelected()) ////Si el usuario ha escogido este radio button devuelvo un 2
        {
            return 2;
        } 
        else if (rdbtDificultad3.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 3
        {
            return 3;
        }
        else if (rdbtDificultad4.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 4
        {
            return 4;
        } else 
        {
            // En caso de que ningún botón esté seleccionado, devuelvo directamente un 1.
            return 1;
        }
}
}

