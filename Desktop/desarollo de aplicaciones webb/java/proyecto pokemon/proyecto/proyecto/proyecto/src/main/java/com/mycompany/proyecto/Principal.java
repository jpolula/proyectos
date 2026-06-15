package com.mycompany.proyecto;

import java.io.IOException;
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
import javafx.scene.control.Button;
import javafx.scene.control.ProgressBar;
import javafx.scene.control.TextArea;
import javafx.scene.control.Tooltip;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

public class Principal implements Initializable 
{
    int contVictorias=0; //Con esta variable sabremos cuantas veces hemos acabado con tux.
    int dificultad = Singleton.getInstancia().getDifficultad(); //Recupero la dificultad que ha escogido el usuario
    int idEntrenador = Singleton.getInstancia().getEntrenador(); //Recupero el entrenador que ha escogido el usuario.
    List<Map<String,String>> listaEstadisticasPokemon = new ArrayList<Map<String,String>>(); // Lista donde meteré a todos los pokemon de cada entrenador
    List<Map<String,String>> listaEstadisticasPokemonAleatorio = new ArrayList<Map<String,String>>(); //Lista donde metere a los pokemon del entrenador aleatorio.
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
    private Button btnPokemonReserva1;
    @FXML
    private Button btnPokemonReserva2;
    @FXML
    private Button btnPokemonReserva3;
    @FXML
    private Button btnPokemonReserva4;
    @FXML
    private Button btnPokemonReserva5;
    
    Pokemon tux;
    Pokemon pokemonActivo;
    Pokemon equipoPokemon[];
    private ImageView imagenesPokemonReserva[]=new ImageView[5];
    private TextArea infoReserva[]=new TextArea[5] ;
    private ProgressBar vidaPokemon[]=new ProgressBar[5];
    private Button btnPokemonReserva[]=new Button[5];
    
    Tooltip toolPokemonReserva[]=new Tooltip[5];
    
    Tooltip tooltipPokemonActivo=new Tooltip();
    Tooltip tooltipTux=new Tooltip();
 
  
   
    @FXML
    public void initialize(URL location, ResourceBundle resources) 
    {
         
        
         Tooltip tooltipPokemonReserva1=new Tooltip();
         Tooltip tooltipPokemonReserva2=new Tooltip();
         Tooltip tooltipPokemonReserva3=new Tooltip();
         Tooltip tooltipPokemonReserva4=new Tooltip();
         Tooltip tooltipPokemonReserva5=new Tooltip();
         
         toolPokemonReserva[0]=tooltipPokemonReserva1;
         toolPokemonReserva[1]=tooltipPokemonReserva2;
         toolPokemonReserva[2]=tooltipPokemonReserva3;
         toolPokemonReserva[3]=tooltipPokemonReserva4;
         toolPokemonReserva[4]=tooltipPokemonReserva5;
     
        //pongo la informacion de cada pokemon reserva en un vector para poder recorrer todos textarea.
        infoReserva[0]=infoReserva1;
        infoReserva[1]=infoReserva2;
        infoReserva[2]=infoReserva3;
        infoReserva[3]=infoReserva4;
        infoReserva[4]=infoReserva5;
        
        //Pongo las imagnes de los pokemon que descansan en un vector.
        imagenesPokemonReserva[0]=imgPokemonreserva1;
        imagenesPokemonReserva[1]=imgPokemonreserva2;
        imagenesPokemonReserva[2]=imgPokemonreserva3;
        imagenesPokemonReserva[3]=imgPokemonreserva4;
        imagenesPokemonReserva[4]=imgPokemonreserva5;
        //Asigno al array la vida de cada pokemon.
        vidaPokemon[0]=pbVidaPokemonReserva1;
        vidaPokemon[1]=pbVidaPokemonReserva2;
        vidaPokemon[2]=pbVidaPokemonReserva3;
        vidaPokemon[3]=pbVidaPokemonReserva4;
        vidaPokemon[4]=pbVidaPokemonReserva5;
        //Asigno los botones de cambiar pokemon al array
        btnPokemonReserva[0]=btnPokemonReserva1;
        btnPokemonReserva[1]=btnPokemonReserva2;
        btnPokemonReserva[2]=btnPokemonReserva3;
        btnPokemonReserva[3]=btnPokemonReserva4;
        btnPokemonReserva[4]=btnPokemonReserva5;
       
        try 
        {
            if(idEntrenador==13) //Si el id del entrenador es el 13 significa que es el aleatorio.
            {
                
                listaEstadisticasPokemonAleatorio=modelo.obtenerEstadisticasPokemonAleatorio();
                equipoPokemon = new Pokemon[6];
                tux=new Pokemon(); //nuestro contrincante
                
                for(int i=0;i<equipoPokemon.length;i++)
                {
                    Map<String,String> m = listaEstadisticasPokemonAleatorio.get(i);
                    int vida = Integer.parseInt(m.get("HP")); //Convierto a entero las estadisticas excepto su id y su nombre.
                    int ataque = Integer.parseInt(m.get("Attack"));
                    int defensa = Integer.parseInt(m.get("Defense"));
                    int ataqueEspecial = Integer.parseInt(m.get("Special_Attack"));
                    int defensaEspecial = Integer.parseInt(m.get("Special_Defense"));
                    int velocidad = Integer.parseInt(m.get("Speed"));
                    String nombrePokemon=m.get("Pokemon");
                    
                    equipoPokemon[i] = new Pokemon();
                    equipoPokemon[i].setId(m.get("ID_Pokemon"));
                    equipoPokemon[i].setNombrePokemon(m.get("Pokemon"));
                    equipoPokemon[i].setTipo(modelo.obtenerIdTipo(nombrePokemon));    //Obtengo el id del tipo del pokemon.
                    equipoPokemon[i].setVida(vida);
                    equipoPokemon[i].setVidaActual(vida);
                    equipoPokemon[i].setAtaque(ataque);
                    equipoPokemon[i].setDefensa(defensa);
                    equipoPokemon[i].setAtaqueEspecial(ataqueEspecial);
                    equipoPokemon[i].setDefensaEspecial(defensaEspecial);
                    equipoPokemon[i].setVelocidad(velocidad);   
                }
                int numAleatorio=(int) (Math.random()*equipoPokemon.length); //Numero aleatorio que me dara el pokemon que lucha. 
                
                pokemonActivo=equipoPokemon[numAleatorio];
                
                subirEstadisticasPokemonDependiendoDificultad(dificultad, equipoPokemon); 
            
                tux=asignarEstadisticasTuxiSegunDificultad(dificultad, tux); //ASigno las estadisticas al pokemon tux
            
                informacionTux.setText(obtenerInformacionPokemonActivo(tux, imgTux));
                
                informacionPokemonActivo.setText(obtenerInformacionPokemonActivo(equipoPokemon[numAleatorio], imgPokemonActivo)); //RElleno la informacion del pokemon activo junto con su imagen
            
                mostrarImagenesPokemonReserva(numAleatorio, equipoPokemon); //Coloco las imagenes de los pokemon que estan en reserva
            
                llenarInformacionPokemonReserva(infoReserva, equipoPokemon, numAleatorio); //Lleno en los textarea la informacion de cada pokemon que esta en la reserva
                
                asignarVidaABarraDeProgreso(pbVidaPokemonActivo);
                
                añadirToolTipBarraDeProgreso();
                
                System.out.println("Vida de tux antes de atacar: " +tux.getVidaActual());
                
                System.out.println("Vida del pokemon antes de atacar: " +equipoPokemon[numAleatorio].getVidaActual());
            }
            else
            {
                 listaEstadisticasPokemon = modelo.obtenerEstadisticasPokemonDeEntrenador(idEntrenador); //Asigno a la lista creada la consulta de las estadisticas de los pokemon dependiendo de su id
            
                //Con esto obtendre los pokemon que tiene el entrenador que ha elegido el usuario
                int numPokemon = listaEstadisticasPokemon.size();

                // IInicializo el vector y le asigno memoria dependiendo del tamaño de la lista.
                equipoPokemon = new Pokemon[numPokemon];
                tux=new Pokemon(); //nuestro contrincante

                // Hago un bucle para recorrer toda la lista y asignar las estadisticas de cada pokemon que tiene nuestro entrenador.
                 for(int i = 0; i < numPokemon; i++) 
            {
                Map<String,String> m = listaEstadisticasPokemon.get(i);
                int vida = Integer.parseInt(m.get("HP")); //Convierto a entero las estadisticas excepto su id y su nombre.
                int ataque = Integer.parseInt(m.get("Attack"));
                int defensa = Integer.parseInt(m.get("Defense"));
                int ataqueEspecial = Integer.parseInt(m.get("Special_Attack"));
                int defensaEspecial = Integer.parseInt(m.get("Special_Defense"));
                int velocidad = Integer.parseInt(m.get("Speed"));
                String nombrePokemon=m.get("Pokemon");
                //Le asigno los valores proporcionados por la base de datos a los pokemon.
                equipoPokemon[i] = new Pokemon();
                equipoPokemon[i].setId(m.get("ID_Pokemon"));
                equipoPokemon[i].setNombrePokemon(m.get("Pokemon"));
                equipoPokemon[i].setTipo(modelo.obtenerIdTipo(nombrePokemon));    //Obtengo el id del tipo del pokemon.
                equipoPokemon[i].setVida(vida);
                equipoPokemon[i].setVidaActual(vida);
                equipoPokemon[i].setAtaque(ataque);
                equipoPokemon[i].setDefensa(defensa);
                equipoPokemon[i].setAtaqueEspecial(ataqueEspecial);
                equipoPokemon[i].setDefensaEspecial(defensaEspecial);
                equipoPokemon[i].setVelocidad(velocidad);   
            }
                 int numAleatorio=(int) (Math.random()*numPokemon); //Numero aleatorio que me dara el pokemon que lucha. 
                pokemonActivo=equipoPokemon[numAleatorio];
           
                System.out.println(numAleatorio);
                subirEstadisticasPokemonDependiendoDificultad(dificultad, equipoPokemon); 
            
                tux=asignarEstadisticasTuxiSegunDificultad(dificultad, tux); //ASigno las estadisticas al pokemon tux
            
                informacionTux.setText(obtenerInformacionPokemonActivo(tux, imgTux));
            
                informacionPokemonActivo.setText(obtenerInformacionPokemonActivo(equipoPokemon[numAleatorio], imgPokemonActivo)); //RElleno la informacion del pokemon activo junto con su imagen
            
                mostrarImagenesPokemonReserva(numAleatorio, equipoPokemon); //Coloco las imagenes de los pokemon que estan en reserva
            
                llenarInformacionPokemonReserva(infoReserva, equipoPokemon, numAleatorio); //Lleno en los textarea la informacion de cada pokemon que esta en la reserva
                asignarVidaABarraDeProgreso(pbVidaPokemonActivo);
                desactivarBotonesReserva(equipoPokemon.length-1);
                añadirToolTipBarraDeProgreso();
                System.out.println("Vida de tux antes de atacar: " +tux.getVidaActual());
                System.out.println("Vida del pokemon antes de atacar: " +equipoPokemon[numAleatorio].getVidaActual());
            }
          
        } 
        catch (Exception ex) {
           System.out.println(ex.getMessage());
        }
    }
    
   private void subirEstadisticasPokemonDependiendoDificultad(int dificultad, Pokemon equipoPokemon[]) //Metodo que utilizo para saber las estadisticas de mejora que tienen los pokemon. dependiendo de la dificultad
    {
        switch (dificultad)
        {
            case 1: // Recluta
               int porcentaje=(int) (Math.random()*25+1)+75;
               for(int i=0;i<equipoPokemon.length;i++)
               {
                   equipoPokemon[i].porcentajeMejora(porcentaje);
                   equipoPokemon[i].setNivel((int) (Math.random()*25+1)+75); //Cambio el nivel del pokemon
               }
                break;
                
            case 2: // Marine
                 porcentaje=(int) (Math.random()*25+1)+50;
               for(int i=0;i<equipoPokemon.length;i++)
               {
                   equipoPokemon[i].porcentajeMejora(porcentaje);
                   equipoPokemon[i].setNivel((int) (Math.random()*25+1)+50); //Cambio el nivel del pokemon
               }
                break;
                
            case 3: // Veterano
                porcentaje=(int) (Math.random()*25+1)+25;
               for(int i=0;i<equipoPokemon.length;i++)
               {
                   equipoPokemon[i].porcentajeMejora(porcentaje);
                   equipoPokemon[i].setNivel((int) (Math.random()*25+1)+50); //Cambio el nivel del pokemon
               }
                break;
                
            case 4: // Pesadilla
                porcentaje=(int) (Math.random()*25+1);
               for(int i=0;i<equipoPokemon.length;i++)
               {
                   equipoPokemon[i].porcentajeMejora(porcentaje);
                   equipoPokemon[i].setNivel((int) (Math.random()*25+1)); //Cambio el nivel del pokemon
               }
                break;
            default:
                    System.out.println("Error"); //Tal y como esta el programa nunca saldrá este mensaje
                break;
        }
    }
   
   private Pokemon asignarEstadisticasTuxiSegunDificultad(int dificultad,Pokemon tuxito)
   {
       switch (dificultad)
        {
            case 1: // Recluta   
               //VALORES DE TUX EN MODO RECLUTA
               tuxito=new Pokemon("152", "tux",(int)( Math.random()*17+1), 180, 25, 112, 112, 112, 112, 112);
               asignarVidaABarraDeProgreso( pbVidaTux);
                break;
                
            case 2: // Marine
                //VALORES DE TUX EN DIFICULTAD MARINE
               tuxito=new Pokemon("152", "tux",(int)( Math.random()*17+1), 225, 50, 189, 189, 189, 189, 189);
               asignarVidaABarraDeProgreso(pbVidaTux);
                break;
                
            case 3: // Veterano
               //VALORES DE TUX EN DIFICULTAD VETERANO
               tuxito=new Pokemon("152", "tux",(int)( Math.random()*17+1), 350, 75, 220, 220, 220, 220, 220);
               asignarVidaABarraDeProgreso( pbVidaTux);
                break;
                
            case 4: // Pesadilla
               //VALORES DE TUX EN DIFICULTAD PESADILLA
               tuxito=new Pokemon("152", "tux",(int)( Math.random()*17+1), 440, 100, 372, 372, 372, 372, 372);
               asignarVidaABarraDeProgreso( pbVidaTux);
                break;
            default:
                    System.out.println("Error"); //Tal y como esta el programa nunca saldrá este mensaje
                break;
        }
        return tuxito; //Devuelvo el objeto para asignarselo a tux.
   }
   
   public String obtenerInformacionPokemonActivo(Pokemon pokemon,ImageView image)  //Metodo en el que obtengo la informacion de un pokemon
   {
        StringBuilder info = new StringBuilder();
        info.append("Nombre: ").append(pokemon.getNombre()).append("\n");
        info.append("Nivel: ").append(pokemon.getNivel()).append("\n");
        info.append("Vida maxima: ").append(pokemon.getVida()).append("\n");
        info.append("Ataque: ").append(pokemon.getAtaque()).append("\n");
        info.append("Defensa: ").append(pokemon.getDefensa()).append("\n");
        info.append("Ataque Especial: ").append(pokemon.getAtaqueEspecial()).append("\n");
        info.append("Defensa Especial: ").append(pokemon.getDefensaEspecial()).append("\n");
        info.append("Velocidad: ").append(pokemon.getVelocidad()).append("\n");
        String nombre=pokemon.getNombre();
        String ruta = "file:.//imagenes//" + nombre + ".jpg";
        image.setImage(new Image(ruta));
        return info.toString(); 
}
 
   @FXML
   public void mostrarImagenesPokemonReserva(int numAleatorio, Pokemon[] equipoPokemon)  //Funcion que me muestra las imagnes de los pokemon que no estan combatiendo
   {
        int cont=0;
        for (int i = 0; i <equipoPokemon.length; i++) 
        {
           
                // Si el pokemon no esta en batalla asignamos su foto en la reserva
                if (i!=numAleatorio&&equipoPokemon[i] != null) 
                {
                    // Este Pokémon tiene una imagen asociada
                    String nombrePokemon = equipoPokemon[i].getNombre();
                    String rutaImagen = "file:.//imagenes//" + nombrePokemon + ".jpg";
                    Image imagenPokemon = new Image(rutaImagen);
                    imagenesPokemonReserva[cont].setImage(imagenPokemon);
                    cont++;
                }
            
        }
   }
   
    public void llenarInformacionPokemonReserva(TextArea[] infoReserva, Pokemon[] equipoPokemon, int numAleatorio) {
        int cont = 0;
        for (int i = 0; i < equipoPokemon.length; i++) {
            if (i != numAleatorio && equipoPokemon[i] != null)
            {   
                mostrarInformacionPokemonReserva(equipoPokemon[i], infoReserva[cont]);
                asignarVidaABarraDeProgreso(vidaPokemon[cont]); //Deberia ponerlo en el initialize.
                cont++;
            }
        }
    }
    
    private void mostrarInformacionPokemonReserva(Pokemon pokemon, TextArea infoReserva) {
        StringBuilder info = new StringBuilder();
        info.append("Nombre: ").append(pokemon.getNombre()).append("\n");
        info.append("Nivel: ").append(pokemon.getNivel()).append("\n");
        info.append("Vida maxima: ").append(pokemon.getVida()).append("\n");
        info.append("Ataque: ").append(pokemon.getAtaque()).append("\n");
        info.append("Defensa: ").append(pokemon.getDefensa()).append("\n");
        info.append("Ataque Especial: ").append(pokemon.getAtaqueEspecial()).append("\n");
        info.append("Defensa Especial: ").append(pokemon.getDefensaEspecial()).append("\n");
        info.append("Velocidad: ").append(pokemon.getVelocidad()).append("\n");
        infoReserva.setText(info.toString());
    }
    
    private void asignarVidaABarraDeProgreso(ProgressBar progressBar) 
    {
        progressBar.setProgress(1.0);
    }

    
    @FXML
    private void realizarAtaqueNormal() 
            //PRE:El pokemon que ataque no podra estar descansando, si no no podra ejecutar el ataque.
    {   
        int numeroAleatorio=(int) (Math.random()*100+1); //Numero aleatrio que dependiendo de este numero tux hara un ataque especial o no
        if (tux.getVelocidad() > pokemonActivo.getVelocidad()) 
         {
             if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
             {
                 if(numeroAleatorio>=20)
                 {
                     realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                 }
                 else
                 {
                     realizarAtaqueYBajarVidaPokemon(tux, pokemonActivo);
                 }
                  
             }
               
             if (pokemonActivo.getVidaActual()> 0) 
              {
                    if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                    {
                        realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
                     }
                } 
            } 
            else if (tux.getVelocidad() < pokemonActivo.getVelocidad())
            {
                 if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                 {
                      realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
                 }
                if (tux.getVida() > 0) 
                {
                     if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
                     {
                         if(numeroAleatorio>=20)
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
                      if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
                      {
                         if(numeroAleatorio>=20)
                         {
                            realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                         }
                         else
                         {
                            realizarAtaqueYBajarVidaPokemon(tux, pokemonActivo);
                          }
                      }
                   
                    if (pokemonActivo.getVidaActual()> 0) 
                    {
                         if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                         {
                               realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
                         }
                      
                    }
                } 
                else
                {
                    if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                    {
                         realizarAtaqueYBajarVidaPokemon(pokemonActivo, tux);
                    }
                   
                    if (tux.getVidaActual()>0) 
                    {
                          if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
                          {
                             if(numeroAleatorio>=20)
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
        }

        if (tux.getVidaActual()<= 0)
        { //Si la vida tux es menor a 0 quiere decir que lo hemos derrotado
            
           System.out.println(tux.getNombre() + " ha sido derrotado.");
           
           tux= asignarEstadisticasTuxiSegunDificultad(dificultad, tux); //Si hemos derrotado a tux llamaremos a la funcion para que nos slaga otro con las mismas caracteristicas que el anterior.
           
           contVictorias++; //Cada Vez que derrotemos a tux el contador se incrementara.
           
           System.out.println("Has derrotado a tux un total de " +contVictorias+ "vec/es");
        }
        
        if (pokemonActivo.getVidaActual()<= 0) 
        { //Si nuestro pokemon activo tiene 0 de vida a perdido.
            System.out.println(pokemonActivo.getNombre() + " ha sido derrotado.");
            cambiarPokemonReservaDisponible();
            
        }
        
        tux.setDescansado(false); //El pokemon ya ha descansado cuando llega aqui.
        tux.setAtaqueEspecialRealizado(false); 
        pokemonActivo.setDescansado(false); //El pokemon al llegar aqui ya habra descansado
        pokemonActivo.setAtaqueEspecialRealizado(false);
        añadirToolTipBarraDeProgreso();
}
     @FXML
    private void realizarAtaqueEspecial()  //Funcion que me realiza un pokemon pra hacer un ataque especial
            //PRE:El pokemon no puede haber realizado un ataque especial en el turno anterior.
            //POS:El pokemon descansara en el siguiente turno
    {
           
            if (tux.getVelocidad() > pokemonActivo.getVelocidad()) 
            {
                if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
                {
                     realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                }
               
                if (pokemonActivo.getVidaActual()> 0) 
                {
                    if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                    {
                         realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
                    }
                   
                } 
            } 
            else if (tux.getVelocidad() < pokemonActivo.getVelocidad())
            {
                 if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                 {
                      realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
                 }
                if (tux.getVidaActual()> 0) 
                {
                     if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
                     {
                         realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                     }
                } 
                
            } 
            else 
            {
                if (Math.random() < 0.5) 
                {
                      if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
                      {
                           realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                      }
                   
                    if (pokemonActivo.getVidaActual()> 0) 
                    {
                         if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                         {
                               realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
                         }
                      
                    }
                } 
                else
                {
                    if(pokemonActivo.getDescansado()==false && pokemonActivo.getAtaqueEspecialRealizado()==false)
                    {
                         realizarAtaqueEspecialYbajarVidaPokemon(pokemonActivo, tux);
                    }
                   
                    if (tux.getVidaActual()> 0) 
                    {
                         if(tux.getDescansado()==false && tux.getAtaqueEspecialRealizado()==false)
                        {
                            realizarAtaqueEspecialYbajarVidaPokemon(tux, pokemonActivo);
                        }
                    } 
                }
        }

        if (tux.getVidaActual()<= 0)
        { //Si la vida tux es menor a 0 quiere decir que lo hemos derrotado
          System.out.println(tux.getNombre() + " ha sido derrotado.");
          tux=asignarEstadisticasTuxiSegunDificultad(dificultad, tux); //Si hemos derrotado a tux invocaremos a la funcion para quue nos devuelva otro tux igual que el de antes
         
          contVictorias++;
           System.out.println("Has derrotado a tux un total de " +contVictorias+ " vec/es");
        }
        if (pokemonActivo.getVidaActual()<= 0) 
        { //Si nuestro pokemon activo tiene 0 de vida a perdido.
            System.out.println(pokemonActivo.getNombre() + " ha sido derrotado.");
            cambiarPokemonReservaDisponible();
            
        }
        
        tux.setDescansado(false); //El pokemon ya ha descansado
        tux.setAtaqueEspecialRealizado(false);
        pokemonActivo.setDescansado(false);
        pokemonActivo.setAtaqueEspecialRealizado(false); //El pokemon ya ha descansado
        añadirToolTipBarraDeProgreso();
}
   private void realizarAtaqueYBajarVidaPokemon(Pokemon atacante, Pokemon defensor) 
   {
        int danio = atacante.dañoNormalPokemon(defensor);
        
        System.out.println("El ataque ha realizado un total de " + danio+ "De daño");
        
        defensor.setVidaActual(defensor.getVidaActual() - danio);
        
        double nuevaVidaDefensor = (double) defensor.getVidaActual() / defensor.getVida(); 
        
        System.out.println("La nueva vida de " + defensor.getNombre() + " es " + defensor.getVidaActual());
        
        if (defensor == pokemonActivo) 
        {
            pbVidaPokemonActivo.setProgress(nuevaVidaDefensor);
        } 
        else if (defensor == tux) 
        {
            pbVidaTux.setProgress(nuevaVidaDefensor);
        }
}
    
   private void realizarAtaqueEspecialYbajarVidaPokemon(Pokemon atacante,Pokemon defensor)
   {
        int danio = atacante.dañoEspecialPokemon(defensor);
        
        System.out.println("El ataque especial ha realizado un total de " + danio+ " De daño");
        
        defensor.setVidaActual(defensor.getVidaActual() - danio);
        
       
        double nuevaVidaDefensor = (double) defensor.getVidaActual() / defensor.getVida(); 
        
        System.out.println("La nueva vida de " + defensor.getNombre() + " es " + defensor.getVidaActual());
        
        if (defensor == pokemonActivo) 
        {
            pbVidaPokemonActivo.setProgress(nuevaVidaDefensor);
        } 
        else if (defensor == tux) 
        {
            pbVidaTux.setProgress(nuevaVidaDefensor);
        }
 } 
    @FXML
    private void cambiarPokemonReserva1() 
    {
        cambiarPokemonReserva(0);//EL 0 OCUPARA La posicion 1 en el vector
    }

    @FXML
    private void cambiarPokemonReserva2() //EL 1SERA EL POKEMON DE RESERVA QUE OCUPA LA SEGUNDA POSICION
    {
        cambiarPokemonReserva(1);
    }

    @FXML
    private void cambiarPokemonReserva3() //EL 2 SERA EL POKEMON DE RESERVA QUE OCUPA LA TERCARA POSICION PO
    {
        cambiarPokemonReserva(2);
    }

    @FXML
    private void cambiarPokemonReserva4() 
    {
        cambiarPokemonReserva(3);
    }

    @FXML
    private void cambiarPokemonReserva5() 
    {
        cambiarPokemonReserva(4);
    }
    
    public void cambiarPokemonReserva(int id)  //Funcion que cambia el pokemon activo por una de la reserva, si lo hay.
    {
        //PRE:NECESARIO QUE EL ENTRENADOR TENGA MINIMO 2 POKEMON    
        Pokemon pokemonReserva = equipoPokemon[id];
    
        Pokemon pokemonAnterior = pokemonActivo;
        pokemonActivo = pokemonReserva;
        
        //HAGO LA COPIA DEL POKEMON ACTIVO
        Pokemon copiaP=pokemonActivo;
        String copiaS=informacionPokemonActivo.getText();
        Image copiaI=imgPokemonActivo.getImage();
        
        informacionPokemonActivo.setText(infoReserva[id].getText());
        imgPokemonActivo.setImage(imagenesPokemonReserva[id].getImage());
        
        
       equipoPokemon[id] =copiaP;
       imagenesPokemonReserva[id].setImage(copiaI);
       infoReserva[id].setText(copiaS);
        
        double ProgressBarActivo = pbVidaPokemonActivo.getProgress(); 
        // Guardo el valor del ProgressBar del Pokémon en reserva
        double ProgressBarReserva = vidaPokemon[id].getProgress();
        
        // Asigno el valor del ProgressBar del Pokémon activo al ProgressBar de la reserva
        vidaPokemon[id].setProgress(ProgressBarActivo);
        
        // Asigno el valor del ProgressBar del Pokémon en reserva al ProgressBar del Pokémon activo
        pbVidaPokemonActivo.setProgress(ProgressBarReserva);
        
        //Me creo una copia del texto tanto del activo como el de la reserva
        String copiaReserva=toolPokemonReserva[id].getText();
        String CopiaA=tooltipPokemonActivo.getText();
        
        //Asigno el texto del reserva en el ahora pokemon activo
        tooltipPokemonActivo.setText(copiaReserva);
        //Asigno el texto del activo en el que ahora esta de reserva.
        toolPokemonReserva[id].setText(CopiaA);
 }
    
    private void desactivarBotonesReserva(int numPokemon)  //Funcion que me desactiva los botones del cambio de pokemon dependiendo del numero de pokemon que tiene el entrenador
    {
        for (int i = 1; i < btnPokemonReserva.length; i++) 
        {
            if (i >= numPokemon) 
            {
                btnPokemonReserva[i].setDisable(true);  
            } 
            else 
            {
                btnPokemonReserva[i].setDisable(false); 
            }
        }
     }
    
    private void cambiarPokemonReservaDisponible()  //Funcion que utilizo cuando un pokemon de mi equipo ha sido derrotado.
    {
        for (int i = 0; i < equipoPokemon.length-1; i++) 
        {
            if (equipoPokemon[i].getVidaActual() > 0) 
            {
                cambiarPokemonReserva(i);
                break;
            }
        }
  }
    
    public void añadirToolTipBarraDeProgreso() //Funcion que utilizo para asignar un tooltip con la vida actual que le queda a cada pokemon
    {
        //PREGUNTAR A ALEJANDRO POR QUENO ME HACE LO QUE YO QUIERO
        tooltipPokemonActivo.setText("Vida restante: " + pokemonActivo.getVidaActual()); //Este es el mensaje que vera el usuario cuando pase con el raton por la vida
        tooltipTux.setText("Vida restante: " + tux.getVidaActual());
        
        Tooltip.install(pbVidaPokemonActivo, tooltipPokemonActivo);
        Tooltip.install(pbVidaTux, tooltipTux);
       
                
        int cont=0;
        
        for (int i = 0; i < equipoPokemon.length; i++) 
        {
            if (equipoPokemon[i].equals(pokemonActivo)==false) 
            {
                toolPokemonReserva[cont].setText("Vida restante: " + equipoPokemon[i].getVidaActual());
                Tooltip.install(vidaPokemon[cont], toolPokemonReserva[cont]);
                cont++;
            }  
        }

    }
  
    @FXML
    public void volverAlMenu() { //Metodo que me lleva al menu cerrandome la ventana de batalla
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
}