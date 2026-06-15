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

public class AyudaBaseDeDatos {

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
        "CONEXIÓN A LA BASE DE DATOS\n" +
        "Antes de jugar nos conectaremos a la base de datos donde tenemos toda la información relacionada con el juego (pokemon que tenemos, tipos… etc).\n" +
        "Una vez ejecutaba la aplicación tendremos que poner el driver que será mysql, el nombre de la base de datos que utilizaremos, nuestra ip que será localhost \n"
                + "si estamos en un servidor local o la ip de nuestro pc si estamos en un servidor web. También tendremos que  poner el puerto que escucha la base de\n"
                + " datos y el usuario y la contraseña de la base de datos que tenemos en nuestro servidor. Cualquier error en poner la información de la base de datos convendrá \n"
                +"En un fallo y, por lo tanto, no se podrá abrir la aplicación. Tendremos un botón de prueba para saber si la conexión a la base de datos ha sido exitosa.\n "
                +"Además, tendremos un botón para salir de la aplicación si es necesario.";

                
        lbl.setText(cadena);
        lbl.setTextFill(Color.WHITE); //Cambio el color del texto a blanco.
        lbl.setFont(Font.font("Calibri", 13)); //Cambio el tamaño de letra y la fuente.
    }
    
    @FXML
    private void volver() //Metodo que me cierra la ventana de ayuda y me lleva al menu
    {
        try
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("baseDeDatos.fxml"));
            
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
