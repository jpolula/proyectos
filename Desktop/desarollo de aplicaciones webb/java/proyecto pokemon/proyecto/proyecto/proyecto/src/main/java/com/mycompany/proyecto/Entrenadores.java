package com.mycompany.proyecto;

import java.io.IOException;
import java.net.URL;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.ResourceBundle;
import java.util.Set;
import java.util.TreeSet;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.ListView;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class Entrenadores implements Initializable 
{
    List<Map<String,String>>lista=new ArrayList<Map<String,String>>(); //Lista donde meteré a todos los entrenadores
    
    @FXML
    ImageView[] fotosPokemon = new ImageView[6]; //En este vector añadiré las 6 fotos 
    @FXML
    private ImageView imgEntrenador;
    @FXML
    private ImageView imgPokemon1;
    @FXML
    private ImageView imgPokemon2;
    @FXML
    private ImageView imgPokemon3;
    @FXML
    private ImageView imgPokemon4;
    @FXML
    private ImageView imgPokemon5;
    @FXML
    private ImageView imgPokemon6;
    @FXML
    private ListView<String> listEntrenador;
    @FXML
    private Button btnAceptar;
    @FXML
    private Button btnPokedex;

    
    @FXML
    public void initialize(URL location, ResourceBundle resources) 
    {   //Asigno los imageview en el vector
         fotosPokemon[0] = imgPokemon1;
         fotosPokemon[1] = imgPokemon2;
         fotosPokemon[2] = imgPokemon3;
         fotosPokemon[3] = imgPokemon4;
         fotosPokemon[4] = imgPokemon5;
         fotosPokemon[5] = imgPokemon6;
        // Creamos un conjunto para meter todos los entrenadores de la base de datos
        Set<String> entrenadores = new TreeSet<>();
        
        Modelo modelo=new Modelo(); //Creo un objeto de tipo modelo 
       
        try 
        {
             lista=modelo.obtenerTodosEntrenadores(); //Asigno a la lista los mapas con todos los entrenadores
             
              for(int i=0;i<lista.size();i++) //para cada entrenador...
            {

                Map<String,String>m=lista.get(i); //obtenemos el mapa asociado a un entrenador
                entrenadores.add(m.get("Trainer")); //metemos su nombre en el Set
            }  
            
              entrenadores.add("aleatorio");
        } catch (Exception ex) 
        {
            System.out.println(ex.getMessage());
        }
        
         ObservableList observableList = FXCollections.observableArrayList();
         observableList.setAll(entrenadores);
         listEntrenador.setItems(observableList);
         
          if(entrenadores.size()>0)
        {
            listEntrenador.getSelectionModel().select(0);
            try {
                updateInfo();
            } catch (SQLException ex) {
                Logger.getLogger(Entrenadores.class.getName()).log(Level.SEVERE, null, ex);
            }
        }
    }

    @FXML
    private void updateInfo() throws SQLException  //Metodo que me actualiza toda la informacion relacionada con el entrenador
    {
        String entrenadorElegido = listEntrenador.getSelectionModel().getSelectedItem();
        String rutaFotoEntrenador = "file:.//imagenes//" + entrenadorElegido + ".jpg";
     
        //Cambio la imagen del entrenador
        Image imagenEntrenador = new Image(rutaFotoEntrenador);
        imgEntrenador.setImage(imagenEntrenador);
        obtenerPokemones1Entrenador(entrenadorElegido);
    }
    
    public void obtenerPokemones1Entrenador(String entrenadorEl) throws SQLException //Metodo en donde obtengo los pokemon del entrenador que haya pasado como parametro
    {
        //Asigno las imagenes de cada pokemon que tiene el entrenador
        
        Modelo modelo = new Modelo();
        try 
        {
            lista = modelo.obtenerPokemon1Entrenador(entrenadorEl);

            // Limpiamos todas las imágenes de Pokémon
            limpiarImagenesPokemon();

            // Iteramos sobre la lista de Pokémon y mostramos las imágenes correspondientes
            for (int i = 0; i < lista.size(); i++) 
            {
                Map<String, String> pokemon = lista.get(i);
                String rutaPokemon = "file:.//imagenes//" + pokemon.get("Pokemon") + ".jpg";
                fotosPokemon[i].setImage(new Image(rutaPokemon));   
            }
        } 
        catch (Exception ex) 
        {
            System.out.println(ex.getMessage());
        }
}

private void limpiarImagenesPokemon() //Metodo que me limpia las fotos de los pokemon
{
    for(int i=0;i<fotosPokemon.length;i++)
    {
        fotosPokemon[i].setImage(null); 
    }
    
}
 @FXML
public void aceptar() throws SQLException//Metodo que me devuelve al menu cerrando la ventana de entrenadores
{
    try 
    {
        int valorEntrenador;
        String entrenadorElegido=" ";
        Modelo modeloId=new Modelo();
       
        if(listEntrenador.getSelectionModel().getSelectedItem().equals("aleatorio"))
        {
             entrenadorElegido="aleatorio";
             valorEntrenador=13;
             Singleton.getInstancia().setEntrenador(valorEntrenador);
             System.out.println("El valor del entrenador es "+valorEntrenador);
        }
        else
        {
            entrenadorElegido = listEntrenador.getSelectionModel().getSelectedItem();
            valorEntrenador=modeloId.obtenerIdEntrenador(entrenadorElegido); 
            System.out.println("El valor del entrenador es "+valorEntrenador);
            // Guardo el valor del entrenador seleccionado en el Singleton para usarlo despues
            Singleton.getInstancia().setEntrenador(valorEntrenador);
        }
        // Obtener el id del entrenador seleccionado
       
        // Abrir el menú
        FXMLLoader fxmlLoader = new FXMLLoader();
        fxmlLoader.setLocation(getClass().getResource("menu.fxml"));

        Scene scene = new Scene(fxmlLoader.load());
        Stage stage = new Stage();
        stage.initStyle(StageStyle.UNDECORATED);
        stage.setScene(scene);
        stage.show();

        // Cerrar la ventana actual buscando su referencia a través de algún control (en este caso el botón 'btnAceptar')
        Stage stageAcerrar = (Stage) btnAceptar.getScene().getWindow();
        stageAcerrar.close();

    } 
    catch (IOException e) 
    {
        e.printStackTrace(); // Manejo de errores
    }
}

    @FXML
    public void abrirPokedex() 
    {  //Metodo que me abre la pokedex y me cierra la ventana de entrenadores
        try 
        {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("pokedex.fxml"));

            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();

            // Cerramos la ventana actual buscando su referencia a través de algún
            // control (en este caso el botón 'btnAceptar')
            Stage stageAcerrar = (Stage) btnAceptar.getScene().getWindow();
            stageAcerrar.close();

        } 
        catch (IOException e) 
        {
            e.printStackTrace(); // Manejo de errores
        }
    }
}
