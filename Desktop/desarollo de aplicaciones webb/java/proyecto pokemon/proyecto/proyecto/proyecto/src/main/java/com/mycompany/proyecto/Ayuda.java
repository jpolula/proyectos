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

public class Ayuda {

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
        "El juego consiste en combatir con TUX, un Pokemon ficticio con el que debemos combatir.\n" +
        "CONEXIÓN A LA BASE DE DATOS\n" +
        "Antes de jugar nos conectaremos a la base de datos donde tenemos toda la información relacionada con el juego (pokemon que tenemos, tipos… etc).\n" +
        "Una vez ejecutaba la aplicación tendremos que poner el driver que será mysql, el nombre de la base de datos que utilizaremos, nuestra ip que será localhost \n"
                + "si estamos en un servidor local o la ip de nuestro pc si estamos en un servidor web. También tendremos que  poner el puerto que escucha la base de\n"
                + " datos y el usuario y la contraseña de la base de datos que tenemos en nuestro servidor. Cualquier error en poner la información de la base de datos convendrá \n"
                + "en un fallo y por lo consecuente no se podrá abrir la aplicación. Tendremos un botón de test para saber si la conexión a la base de datos ha sido exitosa. Si no \n"
                + "queremos continuar con el juego tendremos un botón de salir para cerrar la aplicación.\n" +
        "MENÚ\n" +
        "Una vez que nos hemos conectado a nuestra base de datos nos saldrá la ventana de menú, en el que debemos de elegir una dificultad:\n" +
        "Recluta: En la dificultad recluta el nivel de TUX será de 25 y el nivel de nuestros Pokemon será entre 75-100.  El % de mejora de las habilidades de nuestros\n"
                + " Pokemon variará entre 75-100%.\n" +
        "Marine: En la dificultad marine el nivel de TUX será de 50 y el nivel de nuestros Pokemon será entre 50-75.  El % de mejora de las habilidades de nuestros Pokemon\n"
                + " variará entre 50-75%.\n" +
        "Veterano: En la dificultad marine el nivel de TUX será de 75 y el nivel de nuestros Pokemon será entre 25-50.  El % de mejora de las habilidades de nuestros Pokemon\n"
                + " variará entre 25-50%.\n" +
        "Veterano: En la dificultad marine el nivel de TUX será de 100 y el nivel de nuestros Pokemon será entre 1-25.  El % de mejora de las habilidades de nuestros Pokemon \n"
                + "variará entre 1-25%.\n" +
        "Si no elige ninguna dificultad por defecto se jugará al nivel recluta.\n" +
        "También tendremos que elegir un entrenador de los 12 disponibles que tenemos. Si no se escoge un entrenador la aplicación por defecto escogerá a un entrenador \n"
                + "por defecto. Si no deseamos seguir jugando dispondremos de una opción llamada volver, que nos llevará a la conexión de la base de datos.\n" +
        "Dispondremos del botón de ayuda que es la ventana que está viendo ahora mismo.\n" +
        "Tendremos otro botón que es para abrir la Pokedex, donde tendremos toda la información de los pokemon (nombre, salud, ataque, defensa… etc).\n" +
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
            fxmlLoader.setLocation(getClass().getResource("menu.fxml"));
            
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
        catch (IOException e) {}
    }
    
}
