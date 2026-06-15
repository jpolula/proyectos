package com.mycompany.proyecto;

import java.io.IOException;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.paint.Color;
import javafx.scene.text.Font;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class AyudaPokedex {

    @FXML
    private Label lbl;
    @FXML
    private Button btnVolver;
    @FXML
    private void initialize()
    //método que es llamado internamente justo después del constructor
    //En el constructor NO SE tiene acceso a las variables enlazadas con @FXML
    //En initialize() ya están las variables creadas y son accesibles
    {
        String cadena =
       
        " En esta ventana podremos ver todos los pokemon de los que disponemos actualmente. \n"
        + "Cuando pinchemos en un pokemon nos saldra su foto en el medio y sus estadisticas base a la derecha \n" +
        "El boton seleccionar pokemon no nos servira a menos que queramos elegir al entrenador personalizado. Dispondremos de un boton para volver al menu.";
        
        lbl.setText(cadena);
        lbl.setTextFill(Color.WHITE); //Cambio el color del texto a blanco.
        lbl.setFont(Font.font("Calibri", 11)); //Cambio el tamaño de letra y la fuente.
    }
    
    @FXML
    private void volver() //Metodo que me cierra la ventana de ayuda y me lleva al menu
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
            //control (en este caso el botón 'volver'
            Stage stageAcerrar = (Stage) btnVolver.getScene().getWindow(); 
        
            stageAcerrar.close();
            
        } 
        catch (Exception e) 
        {
            System.out.println(e.getMessage());
        }
    }
    
}
