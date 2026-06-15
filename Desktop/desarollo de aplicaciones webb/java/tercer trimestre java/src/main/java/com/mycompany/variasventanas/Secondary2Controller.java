/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.variasventanas;

import java.io.IOException;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.stage.Stage;

public class Secondary2Controller 
{
    @FXML
    private Button cerrar;
    
    @FXML
    private void cerrar()
    {
        //abrimos la ventana principal
        try{
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("primary.fxml"));
            /* 
             * if "fx:controller" is not set in fxml
             * fxmlLoader.setController(NewWindowController);
             */
            Scene scene = new Scene(fxmlLoader.load(), 600, 400);
            Stage stage = new Stage();
            stage.setTitle("Ventana principal");
            stage.setScene(scene);
            stage.show();
        } catch (IOException e) {}
        
        //cerramos la ventana actual a través de algún control de la misma
        Stage stage = (Stage) cerrar.getScene().getWindow();
        
        stage.close();
    }
}
