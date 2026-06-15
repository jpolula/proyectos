package com.mycompany.proyecto;
import java.util.Arrays;
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
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.ListView;
import javafx.scene.control.Tooltip;
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
    
    Tooltip tooltipPokemon1=new Tooltip();
    Tooltip tooltipPokemon2=new Tooltip();
    Tooltip tooltipPokemon3=new Tooltip();
    Tooltip tooltipPokemon4=new Tooltip();
    Tooltip tooltipPokemon5=new Tooltip();
    Tooltip tooltipPokemon6=new Tooltip();
    Tooltip toolPokemon[]=new Tooltip[6];
    
    @FXML
    public void initialize(URL location, ResourceBundle resources) 
    { 
        toolPokemon[0]=tooltipPokemon1;
        toolPokemon[1]=tooltipPokemon2;
        toolPokemon[2]=tooltipPokemon3;
        toolPokemon[3]=tooltipPokemon4;
        toolPokemon[4]=tooltipPokemon5;
        toolPokemon[5]=tooltipPokemon6;
        //Asigno los imageview en el vector
         fotosPokemon[0] = imgPokemon1;
         fotosPokemon[1] = imgPokemon2;
         fotosPokemon[2] = imgPokemon3;
         fotosPokemon[3] = imgPokemon4;
         fotosPokemon[4] = imgPokemon5;
         fotosPokemon[5] = imgPokemon6;
         for (int i = 0; i < fotosPokemon.length; i++)
        {
            final int indice = i; // Variable final para usar dentro del manejador de eventos
            fotosPokemon[i].setOnMouseClicked(event -> 
        {
            // Llama a la función pokemonSeleccionado() cuando se haga clic en la imagen
            pokemonSeleccionado(indice);
        });
    }
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
            
              entrenadores.add("aleatorio"); //Añado una cadena aparte llamada aleatorio, donde el usuario tendra  pokemon aleatorios
              entrenadores.add("personalizado");
        } 
        catch (Exception ex) 
        {
            System.out.println(ex.getMessage());
        }
        
         ObservableList observableList = FXCollections.observableArrayList();
         observableList.setAll(entrenadores);
         listEntrenador.setItems(observableList);
         
          if(entrenadores.size()>0)
        {
            listEntrenador.getSelectionModel().select(0);
            try 
            {
                updateInfo();
            } 
            catch (SQLException ex) 
            {
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
            String entrenadorElegido = listEntrenador.getSelectionModel().getSelectedItem();
            if (entrenadorElegido.equals("aleatorio"))  //Si el entrenador elegido es el aleatorio...
            {
                Singleton.getInstancia().limpiarPokemonAleatorios();
                
                lista=modelo.obtenerEstadisticasPokemonAleatorio(); //Obtengo pokemon aleatorios
                
                limpiarImagenesPokemon();
                 
                for (int i = 0; i < lista.size(); i++)  //creo un bucle para asignar las fotos del entrenadory su tooltip.
                {
                    Map<String, String> pokemon = lista.get(i);
                    String rutaPokemon = "file:.//imagenes//" + pokemon.get("Pokemon") + ".jpg";
                    fotosPokemon[i].setImage(new Image(rutaPokemon));  
                    toolPokemon[i].setText(pokemon.get("Pokemon"));
                    //El usuario vera el nombre del pokemon cuando pase el rato hasta la foto del pokemon
                    Tooltip.install(fotosPokemon[i], toolPokemon[i]);
                    Singleton.getInstancia().añadirPokemon(pokemon.get("Pokemon"));
                }     
            }
            else
                if(entrenadorElegido.equals("personalizado"))
                {
                    limpiarImagenesPokemon();
                    
                    for (int i = 0; i <fotosPokemon.length; i++) 
                    {
                        String rutaPokemon = "file:.//imagenes//pokeball.jpg";
                        fotosPokemon[i].setImage(new Image(rutaPokemon));   
                    }
                }
                else
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
                        toolPokemon[i].setText(pokemon.get("Pokemon"));
                        //El usuario vera el nombre del pokemon cuando pase el rato hasta la foto del pokemon
                        Tooltip.install(fotosPokemon[i], toolPokemon[i]);
                    }
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
        toolPokemon[i].install(fotosPokemon[i], null); //Limpio los tooltip para que si el entrenador tiene menos de 6 pokemon no aparezcan tooltip en las fotos que no se ven
    }
}
 @FXML
public void aceptar() throws SQLException//Metodo que me devuelve al menu cerrando la ventana de entrenadores
{
    try 
    {
        int valorEntrenador;
        String entrenadorElegido=listEntrenador.getSelectionModel().getSelectedItem();;
        Modelo modeloId=new Modelo();
       
        switch (entrenadorElegido)  //Creo un switch para guardar el valor del entrenador en el singleton
        {
            case "aleatorio":
                 valorEntrenador=13; //Si el usuario escoge el aleatorio guardaremos el valor del entrenador como 13
                 Singleton.getInstancia().setEntrenador(valorEntrenador);
                break;
                
            case "personalizado":
                valorEntrenador=14; //Si el usuario escoge el personalizado guardaremos el valor del entrenador como 14
                Singleton.getInstancia().setEntrenador(valorEntrenador);
                break;
                
            default: //Si el enternador no es el aleatorio ni el personalizado ya sabemos que son los de la base de datos
                entrenadorElegido = listEntrenador.getSelectionModel().getSelectedItem();
                valorEntrenador=modeloId.obtenerIdEntrenador(entrenadorElegido);  //Funcion que me da el id del entrenador seleccionado.
                // Guardo el valor del entrenador seleccionado en el Singleton para usarlo despues
                Singleton.getInstancia().setEntrenador(valorEntrenador);  
        }
        
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
    catch (Exception e) 
    {
        System.out.println(e.getMessage());
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
    
    @FXML
private void pokemonSeleccionado(int indice) 
{
    try 
    {
        // Abre la ventana de la Pokédex
        FXMLLoader fxmlLoader = new FXMLLoader(getClass().getResource("pokedex.fxml"));
        Parent root = fxmlLoader.load();
      //  Pokedex controller = fxmlLoader.getController();
        Stage stage = new Stage();
        stage.initStyle(StageStyle.UNDECORATED);
        stage.setScene(new Scene(root));
        stage.showAndWait(); // Espera hasta que la ventana de la Pokédex se cierre

        // Obtiene el Pokémon seleccionado de la Pokédex
        String pokemonSeleccionado = Singleton.getInstancia().getPokemonPersonalizado();

        // Actualiza la imagen del Pokémon en la interfaz del entrenador personalizado
        if (indice >= 0 && indice < fotosPokemon.length)  //Condicion para saber en que lugar va la foto correspondiente.
        {
            Image imagenPokemon = new Image("file:.//imagenes//" + pokemonSeleccionado + ".jpg");
            fotosPokemon[indice].setImage(imagenPokemon); // Actualiza la imagen del Pokémon seleccionado en la imagen correspondiente
            Singleton.getInstancia().añadirPokemonPersonalizado(pokemonSeleccionado, indice);
        }
    } 
    catch (Exception e) 
    {
        System.out.println(e.getMessage());
    }
  }
}
