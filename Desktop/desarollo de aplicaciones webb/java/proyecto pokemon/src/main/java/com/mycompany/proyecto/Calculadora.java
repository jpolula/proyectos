package com.mycompany.proyecto;
import java.net.URL;
import java.sql.SQLException;

import java.util.ResourceBundle;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.control.RadioButton;
import javafx.scene.control.Slider;
import javafx.scene.control.TextField;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class Calculadora implements Initializable
{
    @FXML
    private Button btnVolverMenu; //Boton que usaré para volver al menu
     @FXML
    private RadioButton rdbBonificacion1; 
    
    @FXML
    private RadioButton rdbtBonificacion2; 
     
    @FXML
    private RadioButton rdbBonificacion3; 
      
    @FXML
    private RadioButton rdbtBonificacion4; 
    
    @FXML
    private RadioButton rdbtE1; 
   @FXML
    private RadioButton rdbtE2; 
    @FXML
    private RadioButton rdbtE3; 
   @FXML
    private RadioButton rdbtE4; 
    @FXML
    private RadioButton rdbtE5; 
    @FXML
    private RadioButton rdbtE6; 
    @FXML
    private RadioButton rdbtMediaBadges; 
    @FXML
    private TextField txtAleatorio; 
    @FXML
    private TextField txtNivel; 
    @FXML
    private TextField txtAtaquel; 
    @FXML
    private TextField txtDefensa; 
     @FXML
    private TextField txtConstante; 
     @FXML
    private Label resultado; 
     
     @FXML
    private Button btnValoresAleatorios; //Boton que usaré para volver al menu
     @FXML
    private Button calcularDaño; //Boton que usaré para volver al menu
    @FXML
    public void initialize(URL location, ResourceBundle resources)
    {
        
        
                      
    }
  
     private double obtenerBonificacion()  //METODO QUE UTILIZO PARA OBTENER LA DIFICULTAD QUE HA ESCOGIDO EL USUARIO
    {
        if (rdbBonificacion1.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 1
        {
            return 0;
        } 
        else if (rdbtBonificacion2.isSelected()) ////Si el usuario ha escogido este radio button devuelvo un 2
        {
            return 1;
        } 
        else if (rdbtBonificacion4.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 3
        {
            return 1.25;
        }
        else if (rdbtBonificacion4.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 4
        {
            return 1.75;
        } 
        else 
        {
            // En caso de que ningún botón esté seleccionado, devuelvo directamente un 1.
            return 1;
        }
       
}
     
     private double obtenerE() throws SQLException  //METODO QUE UTILIZO PARA OBTENER LA DIFICULTAD QUE HA ESCOGIDO EL USUARIO
    {
        if (rdbtE1.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 1
        {
            return 0;
        } 
        else if (rdbtE2.isSelected()) ////Si el usuario ha escogido este radio button devuelvo un 2
        {
            return 0.25;
        } 
        else if (rdbtE3.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 3
        {
            return 0.5;
        }
        else if (rdbtE4.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 4
        {
            return 1;
        } 
         else if (rdbtE5.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 4
        {
            return 2;
        } 
         
         else if (rdbtE6.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 4
        {
            return 4;
        } 
        else if (rdbtMediaBadges.isSelected()) //Si el usuario ha escogido este radio button devuelvo un 4
        {
            Modelo modelo=new Modelo();
            return modelo.ObtenerMediaMedallas();
        } 
        
        else 
        {
            // En caso de que ningún botón esté seleccionado, devuelvo directamente un 1.
            return 1;
        }
       
}
    @FXML
    public void volverMenu()
    {
         try
        {           
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("menu.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'abrirBaseDeDatos'
            Stage stageAcerrar = (Stage) btnVolverMenu.getScene().getWindow(); 
            
            stageAcerrar.close();
         
        } 
         catch (Exception e) 
        {
            System.out.println(e.getMessage());
        }
    }   
    
    @FXML
    public void calcularDaño() throws SQLException
    {
       double constante=Double.parseDouble(txtConstante.getText()); 
       double bonificacion=obtenerBonificacion();
       double v = Double.parseDouble(txtAleatorio.getText()); 
       double e=obtenerE();
       double n=Double.parseDouble(txtNivel.getText()); 
       double a=Double.parseDouble(txtAtaquel.getText()); 
       double d=Double.parseDouble(txtDefensa.getText()); 
        
  
       double valor1=constante;
       
       double valor2=bonificacion;
       
       double valor3=e;
       
       double valor4=v;
       
       double valor5=0.2;
       
       double valor6=n;
       
       double valor7=a;
       
       double valor8=25;
       
       double valor9=d;
       
       double valor10=2;
        
      double  daño1=valor1*valor2*valor3*valor4;
      
      double daño2=((valor5 * valor6 + 1) * valor7) / (valor8 * valor9) + valor10;
     
      double dañoTotal=daño1*daño2;
      System.out.println(dañoTotal);
      
     resultado.setText(String.valueOf(dañoTotal)); //Convierto el valor del entero en String. Tambien asigno el resultado de la operacion en el textField que tengo como resultado
    }
}
