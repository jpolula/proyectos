package com.mycompany.variasventanas;

import java.io.IOException;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.TextField;
import javafx.stage.Modality;
import javafx.stage.Stage;

public class PrimaryController {

   
    @FXML
    private Button abrir1;
    @FXML
    private Button abrir2;
    @FXML
    private Button abrir3;
    @FXML
    private TextField txtEnviado;
    @FXML
    private TextField txtDevuelto;
    
   @FXML
   private void abrir1()
   //abre la nueva ventana dejando abierta la principal
   {
        try {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("secondary1.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.setTitle("Ventana hija");
            stage.setScene(scene);
            stage.show();
        } catch (IOException e) {}
   }

   @FXML
   private void abrir2()
   //abre la nueva ventana cerrando la principal
   {
        try {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("secondary2.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.setTitle("Ventana hija");
            stage.setScene(scene);
            stage.show();
            
            //cerramos la ventana actual buscando su referencia a través de algún 
            //control (en este caso el botón 'abrir1'
            Stage stageAcerrar = (Stage) abrir1.getScene().getWindow();
        
            stageAcerrar.close();
            
        } catch (IOException e) {}
   }
   
    @FXML
   private void abrir3()
   //abre la nueva ventana dejando abierta la principal, pero en formato MODAL
   {
        try {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("secondary1.fxml"));
            
            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.setTitle("Ventana hija");
            stage.setScene(scene);
            
            stage.initModality(Modality.APPLICATION_MODAL);
            
            //stage.show();
            stage.showAndWait();
            
            //dependiendo del método show usado, esta línea se ejecuta inmediatamente al abrir la nueva ventana o espera a que se cierre la nueva ventana para mostrarla
            System.out.println("hola"); 
            
            
            
        } catch (IOException e) {} 
   }
   
   @FXML
   private void abrir4()
   //abre la nueva ventana cerrando la principal y pasando datos (instancia de la clase Info)
   {
        try {
            Singleton intercambio = Singleton.getInstancia();
            intercambio.setDato(Integer.valueOf(txtEnviado.getText()));
            
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("secondary3.fxml"));
           
            
            Scene scene = new Scene(fxmlLoader.load());
            
            Stage stage = new Stage();
            stage.setTitle("Ventana hija con datos");
            stage.setScene(scene);
            
            stage.initModality(Modality.APPLICATION_MODAL);
            
        
            stage.showAndWait();
            
            txtDevuelto.setText(Integer.toString(intercambio.getDato()));
            
            
        } catch (IOException e) {
            System.out.println(e.getMessage());
        }
   }
}
