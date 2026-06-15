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

public class AyudaEntrenadores {

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
        "ELECCION DE UN ENTRENADOR \n" +
        "En la ventana de eleccion del entrenador dispondremos de 14 entrenadores.\n" +
        "12 de ellos serán los que se utilizan en el juego real pokemon que estan formados por Ash, Misty, Brock, Lance, Giovanny, Gary, Erika, Koga, Sabrina, Lt.Surge, Blaine y el Team Rocket. \n"
         + "Dispondremos de un entrenador de tipo aleatorio, que nos dará de 1 a 6 pokemon aleatorios en el equipo. Se Podrá tocar otra vez en el entrenador para que el programa generé"
         + "\n"
         + " otros pokemon aleatorios.\n"
         + "Tambien dispondremos de un entrenador personalizado, que se podrá elegir hasta un máximo de 6 pokemon y un minimo de 1 pokemon \n"
         + "Para elegir a un entrenador tan solo abra que pinchar en el que quisieramos y pinchar en aceptar\n" +
        "Si el usuario no elige a ningun entrenador se jugara por defecto con Ash\n" +
        "Tambien podremos mirar la pokedex con el boton que tenemos en la ventana para consutar cualquier cosa de un pokemon\n"
         + "Al pinchar en aceptar volveremos al menu con el entrenador escogido.";
   
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
            fxmlLoader.setLocation(getClass().getResource("entrenadores.fxml"));
            
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
