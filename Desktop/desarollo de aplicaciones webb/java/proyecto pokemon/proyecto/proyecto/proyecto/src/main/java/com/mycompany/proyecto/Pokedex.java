package com.mycompany.proyecto;

import java.io.IOException;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.ResourceBundle;
import java.util.Set;
import java.util.TreeMap;
import java.util.TreeSet;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.ListView;
import javafx.scene.control.TextArea;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class Pokedex implements Initializable
{
    List<Map<String,String>>lista=new ArrayList<Map<String,String>>(); //Lista donde meteré a todos los pokemon.
    @FXML
    private ImageView imgPokemon; //Control que utilizaré para las fotos de cada pokemon
    
    @FXML
    private ListView listPokemon; //Lista donde guardaré el nombre de todos los pokemon
   
    @FXML
    private TextArea infoPokemon; //Control que usare para poner la informacion de cada pokemon

    @FXML
    private Button btnVolverMenu; //Boton que usaré para volver al menu
   
    @FXML
    public void initialize(URL location, ResourceBundle resources)
    {
        
        //rellenamos la lista de Pokemon consultando en la BD
        Set<String> pokemones=new TreeSet<>();
        
        Modelo modelo=new Modelo();
        try
        {
            lista=modelo.obtenerTodosPokemon();

            for(int i=0;i<lista.size();i++) //para cada Pokémon...
            {
                Map<String,String>m=lista.get(i); //obtenemos el mapa asociado a un pokémon
                pokemones.add(m.get("Pokemon")); //metemos su nombre en el Set
            }

        } catch (Exception ex)
        {
            System.out.println(ex.getMessage());
        }

        ObservableList observableList = FXCollections.observableArrayList();
        observableList.setAll(pokemones);
        listPokemon.setItems(observableList);


        if(pokemones.size()>0)
        {
            listPokemon.getSelectionModel().select(0);
            updateInfo();
        }
                      
    }
   
    @FXML
    private void updateInfo()
    {
        String pokemonElegido = (String) listPokemon.getSelectionModel().getSelectedItem();
        
        String ruta = "file:.//imagenes//" + pokemonElegido + ".jpg";
        
        imgPokemon.setImage(new Image(ruta));
        
        obtenerDatos1Pokemon(pokemonElegido, infoPokemon);
    }
    

    
    private void obtenerDatos1Pokemon(String pokemonElegido,TextArea t)
            //Metodo en el que le paso como argumento el nombre del pokemon y un textArea que es donde irá la innformación de mi consulta
    {
        Modelo modelo=new Modelo();
        try
        {
            lista=modelo.obtenerInformacionPokemon(pokemonElegido); //Relleno la lista con la consulta para obtener la informacion de 1 pokemon.
            
             Map<String,String>m=lista.get(0); //Pongo directamente un 0 ya que este mapa siempre tendrá solo un pokemon.
             String  identificador=m.get("ID_Pokemon");//Creo una variable String para convertirla en int
             int id=Integer.parseInt(identificador);//Convierto el String a entero
             String valorId=String.format("%03d", id); //Asigno 3 cifras al numero.
             String tipo=modelo.obtenerTipoPokemon(pokemonElegido);
             
             String  info = "ID: " + valorId + "\n";
             info += "Nombre: " +m.get("Pokemon") + "\n";
             info += "HP: "+ m.get("HP") + "\n";
             info += "Ataque: " + m.get("Attack") + "\n";
             info += "Defensa: " + m.get("Defense") + "\n";
             info += "Ataque Especial: " + m.get("Special_Attack") + "\n";
             info += "Defensa Especial: " + m.get("Special_Defense") + "\n";
             info += "Velocidad: " + m.get("Speed") + "\n";
             info += "Tipo: " + tipo + "\n";
                          
              t.setText(info);
            
        } catch (Exception ex)
        {
            System.out.println(ex.getMessage());
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
         
        } catch (IOException e) {}
    }
    
}
