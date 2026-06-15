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

public class AyudaPrincipal {

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
        "BATALLA\n" +
        "Una vez que tenemos la dificultad escogida y el entrenador pincharemos en el botón  iniciar batalla.\n" +
        "Dentro de la batalla el Pokemon con mayor velocidad será el que ataque primero.\n" +
        "Si la velocidad de los 2 Pokemon es igual, el ataque se hará aleatoriamente 50% y 50%.\n" +
        "Cada Pokémon tiene un único ataque a su disposición, que podrá ser normal o especial, usándose para el cálculo del daño los correspondientes valores de sus \n"
                + "habilidades.\n" +
        " - El jugador puede usar un ataque especial en cualquier momento, pero dejará a nuestro Pokémon fuera de combate por un turno. Si fuera el único Pokémon a \n"
                + "nuestra disposición, lo dejaría a merced de TUX y podría recibir daño dos veces seguidas: la respuesta de TUX y el siguiente turno. \n" +
        "- TUX tiene una probabilidad de un 20% de realizar un ataque especial, de forma que así puede poner a prueba la defensa especial de su oponente. TUX responde \n"
                + "igual a los ataques normales o especiales de sus oponentes (mirar tabla de estadísticas).\n" +
        " - TUX no tiene un tipo asociado de Pokémon. Al iniciarse la partida tomará uno de los valores posibles existentes en la BD de forma aleatoria. Esto afectará según\n"
                + " los componentes del equipo del jugador.\n" +
        " - Todo lo que ocurre durante el juego quedará recogido en el historial que hay en la parte inferior de la interfaz como diferentes entradas de texto. \n" +
        "- Si logramos derrotar TUX, aparecerá otro del mismo nivel y nuestros Pokémon deberán continuar la batalla con las pocas energías que les queden.\n" +
        "MUCHO ANIMO!!!!";
        
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
            fxmlLoader.setLocation(getClass().getResource("principal.fxml"));            
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
