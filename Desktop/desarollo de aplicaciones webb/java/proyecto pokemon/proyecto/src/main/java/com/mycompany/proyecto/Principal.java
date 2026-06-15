package com.mycompany.proyecto;
import java.util.Arrays;
import java.net.URL;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.ResourceBundle;
import java.util.logging.Level;
import java.util.logging.Logger;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.ProgressBar;
import javafx.scene.control.TextArea;
import javafx.scene.control.Tooltip;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.input.ClipboardContent;
import javafx.scene.input.Dragboard;
import javafx.scene.input.TransferMode;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class Principal implements Initializable 
{
    int contVictorias = 0; // Con esta variable sabremos cuantas veces hemos acabado con tux.
    int dificultad;
    int idEntrenador;

    List<Map<String, String>> listaEstadisticasPokemon = new ArrayList<Map<String, String>>(); 
// Lista donde meteré todos los pokemon de cada entrenador
    List<Map<String, String>> listaEstadisticasPokemonAleatorio = new ArrayList<Map<String, String>>(); 
// Lista donde metere los pokemon del equipo aleatorio.
    List<Map<String, String>> listaEstadisticasPokemonPersonalizado = new ArrayList<Map<String, String>>();
    // Lista donde metre a los pokemon del entrenador personalizado.                                                                                                
    Modelo modelo = new Modelo();
    @FXML
    private Button volverMenu;
    @FXML
    private ImageView imgTux;
    @FXML
    private TextArea informacionTux;
    @FXML
    private TextArea informacionPokemonActivo;
    @FXML
    private ImageView imgPokemonActivo;
    @FXML
    private Button btnAtaque;
    @FXML
    private Button ataqueEspecial;
    @FXML
    private ImageView imgPokemonreserva1;
    @FXML
    private ImageView imgPokemonreserva2;
    @FXML
    private ImageView imgPokemonreserva3;
    @FXML
    private ImageView imgPokemonreserva4;
    @FXML
    private ImageView imgPokemonreserva5;
    @FXML
    private TextArea infoReserva1;
    @FXML
    private TextArea infoReserva2;
    @FXML
    private TextArea infoReserva3;
    @FXML
    private TextArea infoReserva4;
    @FXML
    private TextArea infoReserva5;
    @FXML
    private ProgressBar pbVidaPokemonActivo;
    @FXML
    private ProgressBar pbVidaTux;
    @FXML
    private ProgressBar pbVidaPokemonReserva1;
    @FXML
    private ProgressBar pbVidaPokemonReserva2;
    @FXML
    private ProgressBar pbVidaPokemonReserva3;
    @FXML
    private ProgressBar pbVidaPokemonReserva4;
    @FXML
    private ProgressBar pbVidaPokemonReserva5;
    @FXML
    private TextArea txtLogs;
    @FXML
    private Button btnAyuda;

    Pokemon tux;
    Pokemon pokemonActivo;
    Pokemon equipoPokemon[];

    private ImageView imagenesPokemonReserva[] = new ImageView[5];
    private TextArea infoReserva[] = new TextArea[5];
    private ProgressBar vidaPokemon[] = new ProgressBar[5];
    private Button btnPokemonReserva[] = new Button[5];

    Tooltip toolPokemonReserva[] = new Tooltip[5];
    Tooltip tooltipPokemonActivo = new Tooltip();
    Tooltip tooltipTux = new Tooltip();

    @FXML
    public void initialize(URL location, ResourceBundle resources) 
    {
        txtLogs.setText("CONEXION CON LA BASE DE DATOS DE MANERA EXITOSA \n");
        dificultad = Singleton.getInstancia().getDifficultad(); // Recupero la dificultad que ha escogido el usuario
        idEntrenador = Singleton.getInstancia().getEntrenador(); // Recupero el entrenador que ha escogido el usuario.

        Tooltip tooltipPokemonReserva1 = new Tooltip();
        Tooltip tooltipPokemonReserva2 = new Tooltip();
        Tooltip tooltipPokemonReserva3 = new Tooltip();
        Tooltip tooltipPokemonReserva4 = new Tooltip();
        Tooltip tooltipPokemonReserva5 = new Tooltip();

        toolPokemonReserva[0] = tooltipPokemonReserva1;
        toolPokemonReserva[1] = tooltipPokemonReserva2;
        toolPokemonReserva[2] = tooltipPokemonReserva3;
        toolPokemonReserva[3] = tooltipPokemonReserva4;
        toolPokemonReserva[4] = tooltipPokemonReserva5;

        // pongo la informacion de cada pokemon reserva en un vector para poder recorrer
        // todos textarea.
        infoReserva[0] = infoReserva1;
        infoReserva[1] = infoReserva2;
        infoReserva[2] = infoReserva3;
        infoReserva[3] = infoReserva4;
        infoReserva[4] = infoReserva5;

        // Pongo las imagnes de los pokemon que descansan en un vector.
        imagenesPokemonReserva[0] = imgPokemonreserva1;
        imagenesPokemonReserva[1] = imgPokemonreserva2;
        imagenesPokemonReserva[2] = imgPokemonreserva3;
        imagenesPokemonReserva[3] = imgPokemonreserva4;
        imagenesPokemonReserva[4] = imgPokemonreserva5;
        // Asigno al array la vida de cada pokemon.
        vidaPokemon[0] = pbVidaPokemonReserva1;
        vidaPokemon[1] = pbVidaPokemonReserva2;
        vidaPokemon[2] = pbVidaPokemonReserva3;
        vidaPokemon[3] = pbVidaPokemonReserva4;
        vidaPokemon[4] = pbVidaPokemonReserva5;
      
        for (int i = 0; i < imagenesPokemonReserva.length; i++) 
        {
            final int index=i; //Guarda el índice actual para usarlo dentro del lambda
            ImageView imgPokemonReserva = imagenesPokemonReserva[i]; //Obtengo el ImageView del Pokémon de reserva actual
            imgPokemonReserva.setOnDragDetected(event -> 
            {
                //Inicio el arrastre cuando el usuario presiona el mouse sobre la imagen
             Dragboard dragboard = imgPokemonReserva.startDragAndDrop(TransferMode.MOVE);
             ClipboardContent content = new ClipboardContent();
             content.putImage(imgPokemonReserva.getImage()); // Agrega la imagen del Pokémon al contenido
             content.putString(Integer.toString(index)); // Información adicional (puede ser útil para identificar el Pokémon)
             dragboard.setContent(content);
             event.consume();// Marca el evento como consumido para evitar que otros nodos lo procesen
            });
        }
           // Configura el comportamiento cuando el usuario arrastra sobre el ImageView del Pokémon activo
            imgPokemonActivo.setOnDragOver(event -> 
            {
                if (event.getGestureSource() != imgPokemonActivo &&
                        event.getDragboard().hasImage()) 
                {
                    event.acceptTransferModes(TransferMode.MOVE);// Acepta el modo de transferencia MOVE si se cumple la condición
                }
                event.consume();
            });

         // Configuro el comportamiento cuando el usuario suelta el arrastre sobre el ImageView del Pokémon activo
        imgPokemonActivo.setOnDragDropped(event -> 
        {
            Dragboard dragboard = event.getDragboard();// Obtiene el contenido del portapapeles del arrastre
            boolean success = false;
            if (dragboard.hasImage()) {// Verifica si el contenido del portapapeles contiene una imagen
                int reservaIndex = Integer.parseInt(dragboard.getString());
                cambiarPokemonReserva(reservaIndex); // Llamo al método existente para cambiar el Pokémon activo por el de reserva
                success = true;
            }
            event.setDropCompleted(success); // Me dicxe si el soltar el arrastre fue exitoso o no
            event.consume(); //Marco el evento com oconsumido
        });
        
        try
        {
                if (idEntrenador == 13) // Si el id del entrenador es el 13 significa que es el aleatorio.
            {
                 txtLogs.appendText("HAS ELEGIDO AL ENTRENADOR ALEATORIO  \n");
                // Funcion que utilizo para limpiar la lista de pokemon aleatorios y no se me
                // metan en el arrays los anteriores.
                String pokemon[] = Singleton.getInstancia().getPokemonAleatorios(); // Obtengo el array con el nombre de los pokemon del equipo aleatorio
                listaEstadisticasPokemonAleatorio = modelo.obtenerInformacionPokemon(pokemon); // Creo una lista y le asigno el resultado de la consulta
                                                                              
                equipoPokemon = new Pokemon[listaEstadisticasPokemonAleatorio.size()]; // Mi equipo pokemon es igual al tamaño de la lista
               
                tux = new Pokemon(); // nuestro contrincante

                for (int i = 0; i < equipoPokemon.length; i++) // Relleno la informacion de todos los pokemon de mi equipo aleatorio
                {
                    Map<String, String> m = listaEstadisticasPokemonAleatorio.get(i);
                    int vida = Integer.parseInt(m.get("HP")); // Convierto a entero las estadisticas excepto su id y su nombre
                    int ataque = Integer.parseInt(m.get("Attack"));
                    int defensa = Integer.parseInt(m.get("Defense"));
                    int ataqueEspecial = Integer.parseInt(m.get("Special_Attack"));
                    int defensaEspecial = Integer.parseInt(m.get("Special_Defense"));
                    int velocidad = Integer.parseInt(m.get("Speed"));
                    String nombrePokemon = m.get("Pokemon");

                    equipoPokemon[i] = new Pokemon();
                    equipoPokemon[i].setId(m.get("ID_Pokemon"));
                    equipoPokemon[i].setNombrePokemon(m.get("Pokemon"));
                    equipoPokemon[i].setTipo(modelo.obtenerIdTipo(nombrePokemon)); // Obtengo el id del tipo del pokemon
                    equipoPokemon[i].setVida(vida);
                    equipoPokemon[i].setVidaActual(vida);
                    equipoPokemon[i].setAtaque(ataque);
                    equipoPokemon[i].setDefensa(defensa);
                    equipoPokemon[i].setAtaqueEspecial(ataqueEspecial);
                    equipoPokemon[i].setDefensaEspecial(defensaEspecial);
                    equipoPokemon[i].setVelocidad(velocidad);
                }
                int numAleatorio = (int) (Math.random() * equipoPokemon.length); // Numero aleatorio que me dara el pokemon que lucha

               pokemonActivo = equipoPokemon[numAleatorio];
                
               equipoPokemon[numAleatorio]=equipoPokemon[equipoPokemon.length-1];
                
               equipoPokemon[equipoPokemon.length-1]=pokemonActivo;
                
               pokemonActivo= equipoPokemon[equipoPokemon.length-1];
               
               equipoPokemon=Arrays.copyOf(equipoPokemon,equipoPokemon.length-1);
               
               txtLogs.appendText("TUX HA ELEGIDO PARA LUCHAR AL POKEMON " + pokemonActivo.getNombre() + "\n");
                             
                subirEstadisticasPokemonDependiendoDificultad(dificultad, equipoPokemon); // subo las estadisticas de los pokemon en funcion de la dificultad escogida
                
                tux = asignarEstadisticasTuxiSegunDificultad(dificultad, tux); // ASigno las estadisticas al pokemon tux

                informacionTux.setText(obtenerInformacionPokemonActivo(tux, imgTux)); // asigno la imagen y el textarea con la informacion de tux
                                                                       
                informacionPokemonActivo.setText(obtenerInformacionPokemonActivo(pokemonActivo, imgPokemonActivo)); // RElleno
                                                                                                                 
                mostrarImagenesPokemonReserva( equipoPokemon); // Coloco las imagenes de los pokemon que estan en reserva
                
                llenarInformacionPokemonReserva(infoReserva, equipoPokemon); // Lleno en los textarea la informacion de los pokemon que estan en la reserva  
                
                asignarVidaABarraDeProgreso(pbVidaPokemonActivo); // asigno la barra de progreso al pokemon activo
                
                añadirToolTipBarraDeProgreso(); // añado un tooltip para qu el usuario pueda ver la vida actual del pokemon
            }
              

            if (idEntrenador == 14) 
            {
                 txtLogs.appendText("HAS ELEGIDO AL ENTRENADOR PERSONALIZADO \n");
                // Funcion que utilizo para limpiar la lista de pokemon aleatorios y no se me
                // metan en el arrays los anteriores.
                String pokemon[] = Singleton.getInstancia().getPokemonPersonalizados(); // Obtengo el array con el nombre de los pokemon del equipo aleatorio
                                                        
                listaEstadisticasPokemonPersonalizado = modelo.obtenerInformacionPokemonPersonalizado(pokemon); // Creo una lista con los pokemon personalizados del usuario y obvio los nulos en la consulta
                                                                                                              
                equipoPokemon = new Pokemon[listaEstadisticasPokemonPersonalizado.size()]; // Mi equipo pokemon es igual al tamaño de la lista
                tux = new Pokemon(); // nuestro contrincante

                for (int i = 0; i < equipoPokemon.length; i++) // Relleno la informacion de todos los pokemon de mi equipo aleatorio
                {
                    Map<String, String> m = listaEstadisticasPokemonPersonalizado.get(i);
                    int vida = Integer.parseInt(m.get("HP")); // Convierto a entero las estadisticas excepto su id y su
                                                              // nombre.
                    int ataque = Integer.parseInt(m.get("Attack"));
                    int defensa = Integer.parseInt(m.get("Defense"));
                    int ataqueEspecial = Integer.parseInt(m.get("Special_Attack"));
                    int defensaEspecial = Integer.parseInt(m.get("Special_Defense"));
                    int velocidad = Integer.parseInt(m.get("Speed"));
                    String nombrePokemon = m.get("Pokemon");

                    equipoPokemon[i] = new Pokemon();
                    equipoPokemon[i].setId(m.get("ID_Pokemon"));
                    equipoPokemon[i].setNombrePokemon(m.get("Pokemon"));
                    equipoPokemon[i].setTipo(modelo.obtenerIdTipo(nombrePokemon)); // Obtengo el id del tipo del pokemon.
                    equipoPokemon[i].setVida(vida);
                    equipoPokemon[i].setVidaActual(vida);
                    equipoPokemon[i].setAtaque(ataque);
                    equipoPokemon[i].setDefensa(defensa);
                    equipoPokemon[i].setAtaqueEspecial(ataqueEspecial);
                    equipoPokemon[i].setDefensaEspecial(defensaEspecial);
                    equipoPokemon[i].setVelocidad(velocidad);
                }
              int numAleatorio = (int) (Math.random() * equipoPokemon.length); // Numero aleatorio que me dara el pokemon que lucha

              pokemonActivo = equipoPokemon[numAleatorio];
               
              equipoPokemon[numAleatorio]=equipoPokemon[equipoPokemon.length-1];
                
              equipoPokemon[equipoPokemon.length-1]=pokemonActivo;
                
              pokemonActivo= equipoPokemon[equipoPokemon.length-1];
               
              equipoPokemon=Arrays.copyOf(equipoPokemon,equipoPokemon.length-1);
              
              txtLogs.appendText("TUX HA ELEGIDO PARA LUCHAR AL POKEMON " + pokemonActivo.getNombre() + "\n");
                
              subirEstadisticasPokemonDependiendoDificultad(dificultad, equipoPokemon); // subo las estadisticas de los pokemon en funcion de la dificultad escogida
                                                                                        

              tux = asignarEstadisticasTuxiSegunDificultad(dificultad, tux); // ASigno las estadisticas al pokemon tux

              informacionTux.setText(obtenerInformacionPokemonActivo(tux, imgTux)); // asigno la imagen y el textarea con la informacion de tux
                                                                                
              informacionPokemonActivo.setText(obtenerInformacionPokemonActivo(pokemonActivo, imgPokemonActivo)); // RElleno la informacion del pokemon activo junto con su imagen
                                                                                                               
              mostrarImagenesPokemonReserva( equipoPokemon); // Coloco las imagenes de los pokemon que estan en la reserva

              llenarInformacionPokemonReserva(infoReserva, equipoPokemon); // Lleno en los textarea la informacion de cada pokemon que esta en la reserva
                                                                                         
              asignarVidaABarraDeProgreso(pbVidaPokemonActivo); // asigno la barra de progreso al pokemon activo
                                         
              añadirToolTipBarraDeProgreso(); // añado un tooltip para qu el usuario pueda ver la vida actual de los pokemon
            } 
            
           if(idEntrenador!=13&&idEntrenador!=14)  //Si el id del entrenador no es ni el 13 ni el 14 ya sabemos que son los entrenadores que hay en la base de datos
            {
               Modelo modelo=new Modelo();
               
               String entrenador=modelo.obtenerNombreEntranador(idEntrenador);
               
               txtLogs.appendText("HAS ELEGIDO AL ENTRENADOR " + entrenador + "\n");
               
               listaEstadisticasPokemon = modelo.obtenerEstadisticasPokemonDeEntrenador(idEntrenador);   // Asigno a laz lista creada la consulta de las estadisticas de los pokemon dependiendo de su id
                                                                                                   
               int numPokemon = listaEstadisticasPokemon.size();  // Con esto obtendre los pokemon que tiene el entrenador que ha elegido el usuario

                // IInicializo el vector y le asigno memoria dependiendo del tamaño de la lista.
                equipoPokemon = new Pokemon[numPokemon];
                tux = new Pokemon(); // nuestro contrincante

                // Hago un bucle para recorrer toda la lista y asignar las estadisticas de cada
                // pokemon que tiene nuestro entrenador.
                for (int i = 0; i < numPokemon; i++) 
                {
                    Map<String, String> m = listaEstadisticasPokemon.get(i);
                    int vida = Integer.parseInt(m.get("HP")); // Convierto a entero las estadisticas excepto su id y su nombre
                    int ataque = Integer.parseInt(m.get("Attack"));
                    int defensa = Integer.parseInt(m.get("Defense"));
                    int ataqueEspecial = Integer.parseInt(m.get("Special_Attack"));
                    int defensaEspecial = Integer.parseInt(m.get("Special_Defense"));
                    int velocidad = Integer.parseInt(m.get("Speed"));
                    String nombrePokemon = m.get("Pokemon");
                    // Le asigno los valores proporcionados por la base de datos a los pokemon.
                    equipoPokemon[i] = new Pokemon();
                    equipoPokemon[i].setId(m.get("ID_Pokemon"));
                    equipoPokemon[i].setNombrePokemon(m.get("Pokemon"));
                    equipoPokemon[i].setTipo(modelo.obtenerIdTipo(nombrePokemon)); // Obtengo el id del tipo del pokemon
                    equipoPokemon[i].setVida(vida);
                    equipoPokemon[i].setVidaActual(vida);
                    equipoPokemon[i].setAtaque(ataque);
                    equipoPokemon[i].setDefensa(defensa);
                    equipoPokemon[i].setAtaqueEspecial(ataqueEspecial);
                    equipoPokemon[i].setDefensaEspecial(defensaEspecial);
                    equipoPokemon[i].setVelocidad(velocidad);
                }
                
               int numAleatorio = (int) (Math.random() * numPokemon); // Numero aleatorio que me dara el pokemon que lucha.
               pokemonActivo = equipoPokemon[numAleatorio]; 
                
               equipoPokemon[numAleatorio]=equipoPokemon[equipoPokemon.length-1];
                
               equipoPokemon[equipoPokemon.length-1]=pokemonActivo; // Cambiamos los lugares del activo y del numAleatorio para poder borrarlo
                                                                                                                            
               pokemonActivo= equipoPokemon[equipoPokemon.length-1];
               
               equipoPokemon=Arrays.copyOf(equipoPokemon,equipoPokemon.length-1); //Quitamos el ultimo objeto que es el pokemon activo, ya que lo tenemos fuera del vector
               
               txtLogs.appendText("TUX HA ELEGIDO PARA LUCHAR AL POKEMON " + pokemonActivo.getNombre() + "\n");
                    
              subirEstadisticasPokemonDependiendoDificultad(dificultad, equipoPokemon);
              
              tux = asignarEstadisticasTuxiSegunDificultad(dificultad, tux); // ASigno las estadisticas al pokemon tux

              informacionTux.setText(obtenerInformacionPokemonActivo(tux, imgTux));

              informacionPokemonActivo.setText(obtenerInformacionPokemonActivo(pokemonActivo, imgPokemonActivo)); 
              // RElleno la informacion del opkemon activo
               
              mostrarImagenesPokemonReserva(equipoPokemon); // Coloco las imagenes de los pokemon que estan en reserva

              llenarInformacionPokemonReserva(infoReserva, equipoPokemon);   // Lleno en los textarea la informacion de cad pokemon que esta en la reserva
           
              asignarVidaABarraDeProgreso(pbVidaPokemonActivo); //Asigno la vida ala barra de progreso del pokemon activo
            
              añadirToolTipBarraDeProgreso(); //En este funcion asigno un tooltip al activo y a los reservas para ver la vida que nos queda actualmente
            }

        } 
        catch (Exception ex) 
        {
            System.out.println(ex.getMessage());
        }
    }

    private void subirEstadisticasPokemonDependiendoDificultad(int dificultad, Pokemon equipoPokemon[]) 
    // Metodo que utilizo para subir las estadisticas de los pokemon en funcion de la dificultad
    {
        switch (dificultad) 
        {
            case 1: // Recluta
                txtLogs.appendText("LA DIFICULTAD ESCOGIDA ES RECLUTA\n");
                int porcentaje = (int) (Math.random() * 25 + 1) + 75;
                for (int i = 0; i < equipoPokemon.length; i++) {
                    equipoPokemon[i].porcentajeMejora(porcentaje);
                    equipoPokemon[i].setNivel((int) (Math.random() * 25 + 1) + 75); // Cambio el nivel del pokemon
                }
                pokemonActivo.porcentajeMejora(porcentaje);
                pokemonActivo.setNivel((int) (Math.random() * 25 + 1) + 75);
                break;

            case 2: // Marine
                 txtLogs.appendText("LA DIFICULTAD ESCOGIDA ES VETERANO\n");
                porcentaje = (int) (Math.random() * 25 + 1) + 50;
                for (int i = 0; i < equipoPokemon.length; i++) {
                    equipoPokemon[i].porcentajeMejora(porcentaje);
                    equipoPokemon[i].setNivel((int) (Math.random() * 25 + 1) + 50); // Cambio el nivel del pokemon
                }
                pokemonActivo.porcentajeMejora(porcentaje);
                pokemonActivo.setNivel((int) (Math.random() * 25 + 1) + 50);
                break;

            case 3: // Veterano
                txtLogs.appendText("LA DIFICULTAD ESCOGIDA ES VETERANO\n");
                porcentaje = (int) (Math.random() * 25 + 1) + 25;
                for (int i = 0; i < equipoPokemon.length; i++) {
                    equipoPokemon[i].porcentajeMejora(porcentaje);
                    equipoPokemon[i].setNivel((int) (Math.random() * 25 + 1) + 25); // Cambio el nivel del pokemon
                }
                pokemonActivo.porcentajeMejora(porcentaje);
                pokemonActivo.setNivel((int) (Math.random() * 25 + 1) + 25);
                break;

            case 4: // Pesadilla
                txtLogs.appendText("LA DIFICULTAD ESCOGIDA ES PESADILLA\n");
                porcentaje = (int) (Math.random() * 25 + 1);
                for (int i = 0; i < equipoPokemon.length; i++) {
                    equipoPokemon[i].porcentajeMejora(porcentaje);
                    equipoPokemon[i].setNivel((int) (Math.random() * 25 + 1)); // Cambio el nivel del pokemon
                }
                pokemonActivo.porcentajeMejora(porcentaje);
                pokemonActivo.setNivel((int) (Math.random() * 25 + 1));
                break;
            default:
                System.out.println("Error"); // Tal y como esta el programa nunca saldrá este mensaje
                break;
        }
    }

    private Pokemon asignarEstadisticasTuxiSegunDificultad(int dificultad, Pokemon tuxito) { //Funcion que me devuelve un objeto pokemon con las estadisticas de tux dependiendo de la dificuktad
        switch (dificultad) {
            case 1: // Recluta
                // VALORES DE TUX EN MODO RECLUTA
                tuxito = new Pokemon("152", "tux",  (int) (Math.random() * 17 + 1), 180, 25, 112, 112, 112, 112, 112);
                asignarVidaABarraDeProgreso(pbVidaTux);
                break;

            case 2: // Marine
                // VALORES DE TUX EN DIFICULTAD MARINE
                tuxito = new Pokemon("152", "tux", (int) (Math.random() * 17 + 1), 225, 50, 189, 189, 189, 189, 189);
                asignarVidaABarraDeProgreso(pbVidaTux);
                break;

            case 3: // Veterano
                // VALORES DE TUX EN DIFICULTAD VETERANO
                tuxito = new Pokemon("152", "tux", (int) (Math.random() * 17 + 1), 350, 75, 220, 220, 220, 220, 220);
                asignarVidaABarraDeProgreso(pbVidaTux);
                break;

            case 4: // Pesadilla
                // VALORES DE TUX EN DIFICULTAD PESADILLA
                tuxito = new Pokemon("152", "tux", (int) (Math.random() * 17 + 1), 440, 100, 372, 372, 372, 372, 372);
                asignarVidaABarraDeProgreso(pbVidaTux);
                break;
            default:
                System.out.println("Error"); // Tal y como esta el programa nunca saldrá este mensaje
                break;
        }
        return tuxito; // Devuelvo el objeto para asignarselo a tux.
    }

    public String obtenerInformacionPokemonActivo(Pokemon pokemon, ImageView image) throws SQLException  // Metodo en el que obtengo la informacion del pokemon activo
    {
        Modelo modelo=new Modelo();
        String tipo= modelo.obtenerTipoDependiendodelIdTipo(pokemon.getTipo());
        StringBuilder info = new StringBuilder();
        info.append("Nombre: ").append(pokemon.getNombre()).append("\n");
        info.append("Nivel: ").append(pokemon.getNivel()).append("\n");
        info.append("Vida maxima: ").append(pokemon.getVida()).append("\n");
        info.append("Ataque: ").append(pokemon.getAtaque()).append("\n");
        info.append("Defensa: ").append(pokemon.getDefensa()).append("\n");
        info.append("Ataque Especial: ").append(pokemon.getAtaqueEspecial()).append("\n");
        info.append("Defensa Especial: ").append(pokemon.getDefensaEspecial()).append("\n");
        info.append("Velocidad: ").append(pokemon.getVelocidad()).append("\n");
        info.append("Tipo: ").append(tipo).append("\n");
        String nombre = pokemon.getNombre();
        String ruta = "file:.//imagenes//" + nombre + ".jpg";
        image.setImage(new Image(ruta));
        return info.toString();
    }

    @FXML
    public void mostrarImagenesPokemonReserva( Pokemon[] equipoPokemon) // Funcion que me muestra las imagenes de los pokemon que no estan combatiendo                                                              
    {
        for (int i = 0; i < equipoPokemon.length; i++)
        {
            // Este Pokémon tiene una imagen asociada
            String nombrePokemon = equipoPokemon[i].getNombre();
            String rutaImagen = "file:.//imagenes//" + nombrePokemon + ".jpg";
            Image imagenPokemon = new Image(rutaImagen);
            imagenesPokemonReserva[i].setImage(imagenPokemon);
        }
    }

    public void llenarInformacionPokemonReserva(TextArea[] infoReserva, Pokemon[] equipoPokemon) throws SQLException//Funcion que utilizo para asignarle la informacion y su vida en la barra de progreso
    {
        for (int i = 0; i < equipoPokemon.length; i++) 
        {
           mostrarInformacionPokemonReserva(equipoPokemon[i], infoReserva[i]);
           asignarVidaABarraDeProgreso(vidaPokemon[i]); // le asigno una barra de progreso a los pokemon de reserva
        }
    }

    private void mostrarInformacionPokemonReserva(Pokemon pokemon, TextArea infoReserva) throws SQLException// Funcion que utilizo para mostrar la informacion del pokemon en el textarea
    {
        Modelo modelo=new Modelo();
        String tipo= modelo.obtenerTipo(pokemon.getNombre());
        StringBuilder info = new StringBuilder();
        info.append("Nombre: ").append(pokemon.getNombre()).append("\n");
        info.append("Nivel: ").append(pokemon.getNivel()).append("\n");
        info.append("Vida maxima: ").append(pokemon.getVida()).append("\n");
        info.append("Ataque: ").append(pokemon.getAtaque()).append("\n");
        info.append("Defensa: ").append(pokemon.getDefensa()).append("\n");
        info.append("Ataque Especial: ").append(pokemon.getAtaqueEspecial()).append("\n");
        info.append("Defensa Especial: ").append(pokemon.getDefensaEspecial()).append("\n");
        info.append("Velocidad: ").append(pokemon.getVelocidad()).append("\n");
        info.append("Tipo: ").append(tipo).append("\n");
        infoReserva.setText(info.toString());  
    }

    private void asignarVidaABarraDeProgreso(ProgressBar progressBar) // Funcion que utilizo para asignar una barra de progreso a un pokemon
    {
        progressBar.setProgress(1.0);
    }

    @FXML
    private void realizarAtaqueNormal() // Funcoin que utilizo para realizar un ataque normal
    // PRE:El pokemon que ataque no podra estar descansando, si no no podra ejecutar
    // el ataque.
    {
        if(tux.getTipo()==1&&pokemonActivo.getTipo()==14||tux.getTipo()==14&&pokemonActivo.getTipo()==1)
        {
            mostrarAlertaPartidaInfinita();
            volverAlMenu();
        }
        int numeroAleatorio = (int) (Math.random() * 100 + 1); // Numero aleatrio que dependiendo de este numero tux hara un ataque especial o no
        if (tux.getVelocidad() > pokemonActivo.getVelocidad()) {
            if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false) {
                if (numeroAleatorio >= 20) {
                    realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                } 
                else
                {
                    realizarAtaqueYBajarVidaPokemon(tux, pokemonActivo);
                }
            }

            if (pokemonActivo.getVidaActual() > 0) {
                if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false) {
                    realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
                }
            }
        } 
        else if (tux.getVelocidad() < pokemonActivo.getVelocidad()) 
        {
            if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false) {
                realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
            }
            if (tux.getVida() > 0) 
            {
                if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false) 
                {
                    if (numeroAleatorio >= 20)
                    {
                        realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                    } 
                    else
                    {
                        realizarAtaqueYBajarVidaPokemon(tux, pokemonActivo);
                    }
                }
            }
        }
        else 
        {
            if (Math.random() < 0.5) 
            {
                if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false) {
                    if (numeroAleatorio >= 20) 
                    {
                        realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                    }
                    else 
                    {
                        realizarAtaqueYBajarVidaPokemon(tux, pokemonActivo);
                    }
                }

                if (pokemonActivo.getVidaActual() > 0) 
                {
                    if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false) 
                    {
                        realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
                    }

                }
            } 
            else 
            {
                if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false)
                {
                    realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
                }

                if (tux.getVidaActual() > 0) 
                {
                    if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false)
                    {
                        if (numeroAleatorio >= 20) {
                            realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                        } 
                        else 
                        {
                            realizarAtaqueYBajarVidaPokemon(tux, pokemonActivo);
                        }
                    }
                }
            }
        }

        if (tux.getVidaActual() <= 0) 
        { // Si la vida tux es menor a 0 quiere decir que lo hemos derrotado

            txtLogs.appendText( tux.getNombre() + "HA SIDO DERROTADO" + "\n");
          
            tux = asignarEstadisticasTuxiSegunDificultad(dificultad, tux); // Si hemos derrotado a tux llamaremos a la funcion para que nos salga otro con las mismas caracteristicas que el anterior
                                                                        
            contVictorias++; // Cada Vez que derrotemos a tux el contador se incrementara.
            
            txtLogs.appendText("HAS DERROTADO A " + tux.getNombre() + " UN TOTAL DE " + contVictorias + " VECES.\n");
        }

        if (pokemonActivo.getVidaActual() <= 0) { // Si nuestro pokemon activo tiene 0 de vida a perdido.
            txtLogs.appendText( pokemonActivo.getNombre() + " HA SIDO DERROTADO" + "\n");
            verificarEquipoSinVida(); // Verifico si hay algun pokemon con vida
            cambiarPokemonReservaDisponible();
        }
        
        pokemonActivo.setDescansado(false); // El pokemon al llegar aqui ya habra descansado
        pokemonActivo.setAtaqueEspecialRealizado(false);
        añadirToolTipBarraDeProgreso(); //Actualizo el tooltip
    }

    @FXML
    private void realizarAtaqueEspecial() // Funcion que me realiza un pokemon pra hacer un ataque especial
    // PRE:El pokemon no puede haber realizado un ataque especial en el turno
    // anterior.
    // POS:El pokemon descansara en el siguiente turno
    {
        if(tux.getTipo()==1&&pokemonActivo.getTipo()==14||tux.getTipo()==14&&pokemonActivo.getTipo()==1)
        {
            mostrarAlertaPartidaInfinita();
            volverAlMenu();
        }
        if (tux.getVelocidad() > pokemonActivo.getVelocidad()) {
            if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false) {
                realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
            }

            if (pokemonActivo.getVidaActual() > 0) {
                if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false) {
                    realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
                }

            }
        }
        else if (tux.getVelocidad() < pokemonActivo.getVelocidad())
        {
            if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false&&pokemonActivo.getVidaActual()>0)
            {
                realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
            }
            if (tux.getVidaActual() > 0) 
            {
                if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false) {
                    realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                }
            }

        } 
        else 
        {
            if (Math.random() < 0.5)
            {
                if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false&&tux.getVidaActual()>0) 
                {
                    realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                }

                if (pokemonActivo.getVidaActual() > 0) 
                {
                    if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false) {
                        realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
                    }

                }
            } 
            else
            {
                if (pokemonActivo.getDescansado() == false && pokemonActivo.getAtaqueEspecialRealizado() == false) 
                {
                    realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
                }

                if (tux.getVidaActual() > 0) 
                {
                    if (tux.getDescansado() == false && tux.getAtaqueEspecialRealizado() == false) 
                    {
                        realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                    }
                }
            }
        }

        if (tux.getVidaActual() <= 0)  // Si la vida tux es menor a 0 quiere decir que lo hemos derrotado  
        { 
            txtLogs.appendText( tux.getNombre() + "HA SIDO DERROTADO" + "\n");
            tux = asignarEstadisticasTuxiSegunDificultad(dificultad, tux); // Si hemos derrotado a tux invocaremos a la funcion para que nos devuelva otro igual que el de antes
            contVictorias++;
            txtLogs.appendText("HAS DERROTADO A " + tux.getNombre() + " UN TOTAL DE " + contVictorias + " VECES.\n");
        }
        if (pokemonActivo.getVidaActual() <= 0)// Si nuestro pokemon activo tiene 0 de vida a perdido.
        { 
            txtLogs.appendText( pokemonActivo.getNombre() + " HA SIDO DERROTADO" + "\n");
            cambiarPokemonReservaDisponible();
            verificarEquipoSinVida(); // Verifico si hay algun pokemon con vida
        }
        else
        {
             pokemonActivo.setDescansado(true); //Si un pokemon de nuestro equipo realiza un ataque especial tiene que descansar un turno
             pokemonActivo.setAtaqueEspecialRealizado(true); 
             txtLogs.appendText( pokemonActivo.getNombre() + " HA REALIZADO UN ATAQUE ESPECIAL Y TIENE QUE DESCANSAR UN TURNO" + "\n");
             
        }
        añadirToolTipBarraDeProgreso();
    }

    private void realizarAtaqueYBajarVidaPokemon(Pokemon atacante, Pokemon defensor) 
    // Funcion que realiza la parte de  atacar a un pokemon y bajarle la vida al otro. En esta funcion tambien actualizamos la barra de vida
    {
        int danio = atacante.dañoNormalPokemon(defensor);
        
        txtLogs.appendText(atacante.getNombre() + " HA REALIZADO UN TOTAL DE " + danio + " PUNTOS DE DAÑO\n");
        defensor.setVidaActual(defensor.getVidaActual() - danio);

        double nuevaVidaDefensor = (double) defensor.getVidaActual() / defensor.getVida();
        
        txtLogs.appendText("LA NUEVA VIDA DE " + defensor.getNombre() + " ES " + defensor.getVidaActual() + "\n");

        if (defensor == pokemonActivo) 
        {
            pbVidaPokemonActivo.setProgress(nuevaVidaDefensor);
        } 
        else if (defensor == tux) 
        {
            pbVidaTux.setProgress(nuevaVidaDefensor);
        }
    }

    private void realizarAtaqueEspecialYbajarVidaPokemon(Pokemon atacante, Pokemon defensor)
    {
        int danio = atacante.dañoEspecialPokemon(defensor); //Esta variable me dara el daño que ha echo el pokemon

        txtLogs.appendText(atacante.getNombre() + " HA REALIZADO UN TOTAL DE " + danio + " PUNTOS DE DAÑO\n");

        defensor.setVidaActual(defensor.getVidaActual() - danio);

        double nuevaVidaDefensor = (double) defensor.getVidaActual() / defensor.getVida();

        txtLogs.appendText("LA NUEVA VIDA DE " + defensor.getNombre() + " ES " + defensor.getVidaActual() + "\n");

        if (defensor == pokemonActivo) 
        {
            pbVidaPokemonActivo.setProgress(nuevaVidaDefensor);
        }
        else if (defensor == tux) 
        {
            pbVidaTux.setProgress(nuevaVidaDefensor);
        }
    }

    public void cambiarPokemonReserva(int id) // Funcion que cambia el pokemon activo por una de la reserva, si lo hay.
    {
        // PRE:NECESARIO QUE EL ENTRENADOR TENGA MINIMO 2 POKEMON
        Pokemon pokemonReserva = equipoPokemon[id];
        
        pokemonActivo = pokemonReserva;

        // HAGO LA COPIA DEL POKEMON ACTIVO
        Pokemon copiaP = pokemonActivo;
        String copiaS = informacionPokemonActivo.getText();
        Image copiaI = imgPokemonActivo.getImage();

        informacionPokemonActivo.setText(infoReserva[id].getText());
        imgPokemonActivo.setImage(imagenesPokemonReserva[id].getImage());

        equipoPokemon[id] = copiaP;
        imagenesPokemonReserva[id].setImage(copiaI);
        infoReserva[id].setText(copiaS);

        double ProgressBarActivo = pbVidaPokemonActivo.getProgress();
        // Guardo el valor del ProgressBar del Pokémon en reserva
        double ProgressBarReserva = vidaPokemon[id].getProgress();

        // Asigno el valor del ProgressBar del Pokémon activo al ProgressBar de la
        // reserva
        vidaPokemon[id].setProgress(ProgressBarActivo);

        // Asigno el valor del ProgressBar del Pokémon en reserva al ProgressBar del
        // Pokémon activo
        pbVidaPokemonActivo.setProgress(ProgressBarReserva);

        // Me creo una copia del texto tanto del activo como el de la reserva
        String copiaReserva = toolPokemonReserva[id].getText();
        String CopiaA = tooltipPokemonActivo.getText();

        // Asigno el texto del reserva en el ahora pokemon activo
        tooltipPokemonActivo.setText(copiaReserva);
        // Asigno el texto del activo en el que ahora esta de reserva.
        toolPokemonReserva[id].setText(CopiaA);

        Tooltip.install(pbVidaPokemonActivo, tooltipPokemonActivo);
        Tooltip.install(vidaPokemon[id], toolPokemonReserva[id]);
        
         txtLogs.appendText("EL USUARIO HA CAMBIADO DE POKEMON Y A ELEGIDO A  " + pokemonActivo.getNombre() + "\n");
        /*int n=(int) (Math.random()*100+1);
        if(n>=20)
        {
            realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
            añadirToolTipBarraDeProgreso();
        }
        else
        {
            realizarAtaqueYBajarVidaPokemon(tux, pokemonActivo);
            añadirToolTipBarraDeProgreso();
        }*/
    }

    private void cambiarPokemonReservaDisponible() 
// Funcion que utilizo cuando un pokemon de mi equipo ha sido derrotado.
    {
        for (int i = 0; i < equipoPokemon.length; i++) 
        {
            if (equipoPokemon[i].getVidaActual() > 0) 
            {
                cambiarPokemonReserva(i);
                break;
            }
            
        }
    }

    public void añadirToolTipBarraDeProgreso() 
    // Funcion que utilizo para asignar un tooltip con la vida actual que le queda a cada pokemon
    {
        tooltipPokemonActivo.setText("Vida restante: " + pokemonActivo.getVidaActual()); 
        // Este es el mensaje que vera el usuario cuando pase con el raton por la vida
        tooltipTux.setText("Vida restante: " + tux.getVidaActual());

        Tooltip.install(pbVidaPokemonActivo, tooltipPokemonActivo);
        Tooltip.install(pbVidaTux, tooltipTux);
        
        for (int i = 0; i < equipoPokemon.length; i++) 
        {
           toolPokemonReserva[i].setText("Vida restante: " + equipoPokemon[i].getVidaActual());
           Tooltip.install(vidaPokemon[i], toolPokemonReserva[i]);
        }
    }
    
    private void verificarEquipoSinVida() 
    {
        boolean equipoSinVida = true;

        // Verificar si el Pokémon activo está sin vida
        if (pokemonActivo.getVidaActual() > 0) 
        {
            equipoSinVida = false;
        }

        // Verificar si algún Pokémon de reserva está sin vida
        for (int i = 0; i < equipoPokemon.length; i++) 
        {
            if (equipoPokemon[i].getVidaActual() > 0) 
            {
                equipoSinVida = false; // Mientras un Pokémon tenga vida, el equipo no está derrotado
                break;
            }
        }
        if (equipoSinVida) // Si todos los Pokémon han sido derrotados, mostrar alerta y volver al menú
        { 
            txtLogs.appendText("TODOS TUS POKEMON HAN SIDO DERROTADOS" + "\n");
            mostrarAlertaEquipoSinVida();
            volverAlMenu();
        }
}
    private void mostrarAlertaEquipoSinVida() 
    // Funcion que utilizo para avisar al usuario cuando todos los pokemon estan sin vida.
    {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("Equipo derrotado");
        alert.setHeaderText(null);
        alert.setContentText("¡Todos tus Pokémon han sido derrotados!, has conseguido derrotar  a tux un total de " + contVictorias + " vez/es");
        alert.showAndWait();
    }
    
    private void mostrarAlertaPartidaInfinita() 
    // Funcion que utilizo para avisar al usuario cuando todos los pokemon estan sin vida.
    {
         txtLogs.appendText("LA PARTIDA NUNCA SE PUEDE ACABAR " + "\n");
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle("PARTIDA INFINITA");
        alert.setHeaderText(null);
        alert.setContentText("LA PARTIDA NO SE PUEDE ACABAR POR QUE TUX Y UN POKEMON NO SE PUEDEN HACER DAÑO");
        alert.showAndWait();
    }
    
    @FXML
    public void volverAlMenu() { 
    // Metodo que me lleva al menu cerrandome la ventana de batalla
    // POS:LA VENTANA PRINCIPAL SE NOS CERRARA.
        try {
            FXMLLoader fxmlLoader = new FXMLLoader();
            fxmlLoader.setLocation(getClass().getResource("menu.fxml"));

            Scene scene = new Scene(fxmlLoader.load());
            Stage stage = new Stage();
            stage.initStyle(StageStyle.UNDECORATED);
            stage.setScene(scene);
            stage.show();

            Stage stageAcerrar = (Stage) volverMenu.getScene().getWindow();
            stageAcerrar.close();
        } 
        catch (Exception e)
        {
            System.out.println(e.getMessage());
        }
    }
    
    @FXML
    public void AbrirAyuda()
         { 
 
            try 
            {
                FXMLLoader fxmlLoader = new FXMLLoader();
                 fxmlLoader.setLocation(getClass().getResource("ayudaPrincipal.fxml"));

                Scene scene = new Scene(fxmlLoader.load());
                Stage stage = new Stage();
                stage.initStyle(StageStyle.UNDECORATED);
                stage.setScene(scene);
                stage.show();

                Stage stageAcerrar = (Stage) btnAtaque.getScene().getWindow();
                stageAcerrar.close();
            } 
            catch (Exception e)
            {
                System.out.println(e.getMessage());
            }

        }
          
    }
