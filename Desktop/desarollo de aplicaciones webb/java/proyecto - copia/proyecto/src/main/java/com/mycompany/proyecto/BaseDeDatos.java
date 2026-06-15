package com.mycompany.proyecto;

import com.mycompany.proyecto.Singleton;
import java.sql.Connection;
import java.sql.DriverManager;
import javafx.application.Platform;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.scene.control.Button;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class BaseDeDatos 
{
    @FXML
    private Button abrir; //Botón que utilizo para acceder al menu
    
    @FXML
    private Button btnCerrar; //Botón que utilizo para cerrar la aplicacion a traves de esta ventana
    
    @FXML
    private Button btnTest;
    
    @FXML
    private TextField btnDriver; //En este textfield pondré el driver
    
     @FXML
    private TextField btnIp; //En este text field pondre la direccion ip
     
    @FXML
    private TextField btnBaseDeDatos; //En este text field pondre el nombre de la base de datos
      
    @FXML
    private TextField btnPuerto; //En este textfield pondre el puerto 
    
    @FXML
    private PasswordField btnUsuario; //en este textfield pondre el usuario de nuestra base de datos
    
    @FXML
    private PasswordField btnContraseña; //en este text field pondre la contraseña de nuestro usuario de la base de datos
    
    @FXML 
    private Button btnAyuda;
    
    @FXML
    private void initialize()
    //método que es llamado internamente justo después del constructor
    //En el constructor NO SE tiene acceso a las variables enlazadas con @FXML
    //En initialize() ya están las variables creadas y son accesibles
    {
        
    }
    
      @FXML
    public void aceptar() //En esta funcion abro la ventana de menu y cierro la actual.
    {
        try
        {
            //conexión a la base de datos
            if (testConexion())
            {
                System.out.println("Conexión exitosa");
                
                int puerto=Integer.parseInt(btnPuerto.getText());
                Singleton intercambio = Singleton.getInstancia(); //Con el Singleton guardaré los datos de laconexion a la  base de datos
                
                intercambio.setDriver(btnDriver.getText()); //Guardo  la informacion del driver que ha puesto el usuario en mi clase singleton.
                intercambio.setIp(btnIp.getText()); //Guardo la ip del usuario
                intercambio.setBd(btnBaseDeDatos.getText());
                intercambio.setPuerto(btnPuerto.getText()); //Guardo el puerto como String por si me hiciera falta.
                intercambio.setPuertoBase(puerto); //Guardo el puerto como entero ya que es necesario
                intercambio.setUsuario(btnUsuario.getText()); //Guardo el usuario 
                intercambio.setContraseña(btnContraseña.getText()); //Guardo la contraseña del usuario
                
                FXMLLoader fxmlLoader = new FXMLLoader();
                fxmlLoader.setLocation(getClass().getResource("menu.fxml"));

                Scene scene = new Scene(fxmlLoader.load());
                Stage stage = new Stage();
                stage.initStyle(StageStyle.UNDECORATED);
                stage.setScene(scene);
                stage.show();

                Stage stageAcerrar = (Stage) btnCerrar.getScene().getWindow(); 
                stageAcerrar.close();
            } 
            else
            {
                System.out.println("Error en la conexión a la base de datos");
            }
        } catch (Exception e) 
        {
            e.printStackTrace();
        }
   }
    
     @FXML
    private boolean testConexion() //Metodo que me hace la conexion a la base de datos.
    {
        String driver = btnDriver.getText(); //el driver es jdbc
        String ip = btnIp.getText(); //la ip es localhost
        int puerto = Integer.parseInt(btnPuerto.getText()); //El puerto es el 3306
        String baseDatos = btnBaseDeDatos.getText(); //La base de datos se llama pokemon
        String usuario = btnUsuario.getText(); //El usuario es pokemon
        String contraseña = btnContraseña.getText(); // La contraseña es pokemon
        String url = driver + ":mysql" + "://" + ip + ":" + puerto + "/" + baseDatos; //Para que nos podamos conectar a la base de datos la url tiene que ser correcta.

        try 
        {
            Connection conexion = DriverManager.getConnection(url, usuario, contraseña);
            conexion.close();
            //SI LA CONEXIÓN ES EXITOSA MOSTRAR UNA VENTANA AL USUARIO AVISANDOLE QUE LA CONEXIÓN HA SIDO EXITOSA
            mostrarAlerta("EXITO", "LA CONEXION A LA BASE DE DATOS HA SIDO EXITOSA");
            return true;
        } catch (Exception ex)
        {
            //SI LA CONEXIÓN NO ES EXITOSA MOSTRAR UNA VENTANA AL USUARIO ADVIRTIENDOLE QUE LA CONEXIÓN A LA BASE DE DATOS NO HA SIDO POSIBLE
            mostrarAlerta("ERROR AL CONECTAR CON LA BASE DE DATOS","NO SE PUDO CONECTAR A LA BASE DE DATOS");
            return false;
        }
    }
    
    private void mostrarAlerta(String titulo, String mensaje) //Metodo que uso para avisar al usuario que la conexion ha sido exitosa o no.
    {
        Alert alerta = new Alert(AlertType.INFORMATION);
        alerta.setTitle(titulo);
        alerta.setHeaderText(null); //Lo pongo en null por que no es necesario poner un texto de cabecera aunque se podria poner
        alerta.setContentText(mensaje);
        alerta.showAndWait();
    }
    
    @FXML
    private void cerrar() //Metodo que me cierra el programa 
    {
        Platform.exit();
    }     
    
    @FXML
    public void abrirAyuda() //Metodo que me abre la ventana de entrenadores cerrandome la de menu
    {  
        try
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("ayudaBaseDeDatos.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
            
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'iniciar partida'
            Stage stageAcerrar = (Stage) btnCerrar.getScene().getWindow(); 
        
            stageAcerrar.close(); 
        } 
        catch (Exception e) 
        {
            System.out.println(e.getMessage());
        }
    }
}
    


