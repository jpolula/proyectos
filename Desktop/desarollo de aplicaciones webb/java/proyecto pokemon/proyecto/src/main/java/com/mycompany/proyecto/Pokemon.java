/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.proyecto;

/**
 *
 * @author Juan Pedro
 */
public class Pokemon 
{
    private String id; //Identificador del pokemon en la Pokedex. Llevará tres cifras independientemente del numero que sea.
    
    private String nombrePokemon; //Nombre del Pokemon
    
    private int tipo; //Tipo de Pokemon (Normal,Tierra... etc)
    
    private int nivel;
    
   // private int type; //numero que irá desde el 1 hasta el 17.
    
    private int vida; //Vida que posee el Pokemon.
    
    private int vidaActual; //Vida que se ira bajando en funcion del ataque que nos den.
    
    private int ataque; //Ataque que posee el pokemon. Este valor va asociado a los ataques fisicos
    
    private int defensa; //Defensa que posee el pokemon. Este valor va asociado a la defensa en ataques fisicos.
    
    private int ataqueEspecial; //Ataque especial que posee un pokemon. Este ataque va asociado al daño por ataque de un cierto tipo.
    
    private int defensaEspecial;//Defensa especial de un pokemon. Esta va asociada a la defensa en ataques de un cierto tipo.
    
    private int velocidad; //Velocidad de un pokemon. Este valor va asignado a la velocidad de un pokemon. Si tiene más velocidad que otro pokemon ataca primero
    
    static private double matrizBonificacion[][]=new double[18][18];
    
    private boolean ataqueEspecialRealizado; //Variable que me permite saber si un pokemon ha echo un ataque especial.
    
    private boolean descansado; //Variable que me permite saber si un pokemon ha descansado.
    
    public Pokemon()
    {
        ataqueEspecialRealizado=false;  //Inicio la variable a false puesto que un pokemon no ha realizado ningun ataque en este punto.
        descansado=false;
        for(int fil=0;fil<matrizBonificacion.length;fil++)
        {
            for(int col=0;col<matrizBonificacion.length;col++)
            {
                matrizBonificacion[fil][col]=1; //Lleno la matriz de 1.
            }
        }
        
        for(int i=0;i<matrizBonificacion.length;i++)
        {
            matrizBonificacion[0][i]=0;
            matrizBonificacion[i][0]=0;
        }
        
        //fila 1
        matrizBonificacion[1][13]=1.25;
        matrizBonificacion[1][14]=0;
        matrizBonificacion[1][17]=1.25;
        
        //Fila 2 
        matrizBonificacion[2][2]=1.25;
        matrizBonificacion[2][3]=1.25;
        matrizBonificacion[2][4]=1.75;
        matrizBonificacion[2][6]=1.75;
        matrizBonificacion[2][12]=1.75;
        matrizBonificacion[2][13]=1.25;
        matrizBonificacion[2][15]=1.25;
        matrizBonificacion[2][17]=1.75;
        
        //Fila 3
        matrizBonificacion[3][2]=1.75;
        matrizBonificacion[3][3]=1.25;
        matrizBonificacion[3][4]=1.25;
        matrizBonificacion[3][9]=1.75;
        matrizBonificacion[3][13]=1.75;
        matrizBonificacion[3][15]=1.25;
        
        //Fila 4
        matrizBonificacion[4][2]=1.25;
        matrizBonificacion[4][3]=1.75;
        matrizBonificacion[4][4]=1.25;
        matrizBonificacion[4][8]=1.25;
        matrizBonificacion[4][9]=1.75;
        matrizBonificacion[4][10]=1.25;
        matrizBonificacion[4][12]=1.25;
        matrizBonificacion[4][13]=1.75;        
        matrizBonificacion[4][15]=1.25;
        matrizBonificacion[4][17]=1.25;
        
        //Fila 5
        matrizBonificacion[5][3]=1.75;
        matrizBonificacion[5][4]=1.25;
        matrizBonificacion[5][5]=1.25;
        matrizBonificacion[5][9]=0;
        matrizBonificacion[5][10]=1.75;
        matrizBonificacion[5][15]=1.25;
        
        //Fila 6
        matrizBonificacion[6][2]=1.25;
        matrizBonificacion[6][3]=1.25;
        matrizBonificacion[6][4]=1.75;
        matrizBonificacion[6][6]=1.25;
        matrizBonificacion[6][9]=1.75;
        matrizBonificacion[6][10]=1.75;
        matrizBonificacion[6][15]=1.25;
        matrizBonificacion[6][17]=1.25;      
        
        //Fila 7
        matrizBonificacion[7][1]=1.75;
        matrizBonificacion[7][6]=1.75;
        matrizBonificacion[7][8]=1.25;
        matrizBonificacion[7][10]=1.25;
        matrizBonificacion[7][11]=1.25;
        matrizBonificacion[7][12]=1.25;
        matrizBonificacion[7][13]=1.75;
        matrizBonificacion[7][14]=0;        
        matrizBonificacion[7][16]=1.75;
        matrizBonificacion[7][17]=1.75;
        
        //Fila 8
        matrizBonificacion[8][4]=1.75;
        matrizBonificacion[8][8]=1.25;
        matrizBonificacion[8][9]=1.25;
        matrizBonificacion[8][13]=1.25;
        matrizBonificacion[8][14]=1.25;
        matrizBonificacion[8][17]=0;
        
        //Fila 9
        matrizBonificacion[9][2]=1.75;
        matrizBonificacion[9][4]=1.25;
        matrizBonificacion[9][5]=1.75;
        matrizBonificacion[9][8]=1.75;
        matrizBonificacion[9][10]=0;
        matrizBonificacion[9][12]=1.25;
        matrizBonificacion[9][13]=1.75;
        matrizBonificacion[9][17]=1.75; 

        //Fila 10
        matrizBonificacion[10][4]=1.75;
        matrizBonificacion[10][5]=1.25;
        matrizBonificacion[10][7]=1.75;
        matrizBonificacion[10][12]=1.75;
        matrizBonificacion[10][13]=1.25;
        matrizBonificacion[10][17]=1.25;
        
        //Fila 11
        matrizBonificacion[11][7]=1.75;
        matrizBonificacion[11][8]=1.75;
        matrizBonificacion[11][11]=1.25;
        matrizBonificacion[11][16]=0;
        matrizBonificacion[11][17]=1.25;
        
        //Fila 12
        matrizBonificacion[12][2]=1.25;
        matrizBonificacion[12][4]=1.75;
        matrizBonificacion[12][7]=1.25;
        matrizBonificacion[12][8]=1.25;
        matrizBonificacion[12][10]=1.25;
        matrizBonificacion[12][11]=1.75;
        matrizBonificacion[12][14]=1.25;        
        matrizBonificacion[12][16]=1.75;
        matrizBonificacion[12][17]=1.25;
        
        //Fila 13
        matrizBonificacion[13][2]=1.75;
        matrizBonificacion[13][6]=1.75;
        matrizBonificacion[13][7]=1.25;
        matrizBonificacion[13][9]=1.25;
        matrizBonificacion[13][10]=1.75;        
        matrizBonificacion[13][12]=1.75;
        matrizBonificacion[13][17]=1.25;
        
        //Fila 14
        matrizBonificacion[14][1]=0;
        matrizBonificacion[14][11]=1.75;        
        matrizBonificacion[14][14]=1.75;
        matrizBonificacion[14][16]=1.25;
       
        //Fila 15
        matrizBonificacion[15][15]=1.75;
        matrizBonificacion[15][17]=1.25;
        
        //Fila 16
        matrizBonificacion[16][7]=1.25;
        matrizBonificacion[16][11]=1.75;        
        matrizBonificacion[16][4]=1.75;
        matrizBonificacion[16][16]=1.25;
        
        //Fila 17
        matrizBonificacion[17][2]=1.25;
        matrizBonificacion[17][3]=1.25;
        matrizBonificacion[17][5]=1.25;
        matrizBonificacion[17][6]=1.75;        
        matrizBonificacion[17][13]=1.75;
        matrizBonificacion[17][17]=1.25;
        
        
    }
    
    public Pokemon(String identificador,String nom,int type,int hp,int niv,int atack,int defense,int specialAtack,int specialDefense,int speed)
    {
        this(); //Constrtuctor por defecto.
        id=identificador;
        nombrePokemon=nom;
        tipo=type;
        nivel=niv;
        vida=hp;
        ataque=atack;
        defensa=defense;
        ataqueEspecial=specialAtack;
        defensaEspecial=specialDefense;
        velocidad=speed;
        vidaActual=hp;
    }
    
    public String getId() 
    {
        return id;
    }
    //No existe un metodo para cambiar el id puesto que ese id es unico.
    
    public String getNombre()
    {
        return nombrePokemon;
    }
    //Tampoco podremos cambiar el nombre del pokemon por que su nombre es unico (supuestamente.....)
    
    public int getTipo()
    {
        return tipo;
    }
    
    public int getNivel()
    {
        return nivel;
    }
    
    public void setNivel(int nuevoNivel)
    {
        nivel=nuevoNivel;
    }
    //Un pokemon no puede cambiar el tipo por lo que el set tampoco se hará.
    
    public int getVida()
    {
        return vida;
    }
    
    public void setVida(int n) //Podria hacernos falta si queremos cambiar la vida del pokemon.
    {
        vida=n;
    }
    
    public int getAtaque()
    {
        return ataque;
    }
    
    public void setAtaque(int a) //Podria hacernos falta cambiar el ataque del pokemon.
    {
        ataque=a;
    }
    
    public int getDefensa()
    {
        return defensa;
    }
    
    public void setDefensa(int d)
    {
        defensa=d;
    }
    
    public int getAtaqueEspecial()
    {
        return ataqueEspecial;
    }
    
    public void setAtaqueEspecial(int atSpl)
    {
        ataqueEspecial=atSpl;
    }
    
    public int getDefensaEspecial()
    {
        return defensaEspecial;
    }
    
    public void setDefensaEspecial(int dfspl)
    {
        defensaEspecial=dfspl;
    }
    
    public int getVelocidad()
    {
        return velocidad;
    }
    
    public void setVelocidad(int ve)
    {
        velocidad=ve;
    }
    
    public void setId(String ide)
    {
        id=ide;
    }
    
    public void setNombrePokemon(String n)
    {
        nombrePokemon=n;
    }
    
    public void setTipo(int tip)
    {
        tipo=tip;
    }
    
    public double  bonificacionPrimerAtacante(int tipoDefensor)//defensor es el tipo que recibe el ataque y atacante es el que hace el ataque.
    //Funcion que me calcula la bonificacion del daño que hace un pokemon a otro.
    //Lo pongo privado ya que lo llamaré desde otra funcion
    {
        return matrizBonificacion[tipo][tipoDefensor];
    }
    
    public boolean getAtaqueEspecialRealizado()
    {
        return ataqueEspecialRealizado;
    }
     
    public void setAtaqueEspecialRealizado(boolean ataqueRealizado)
    {
        ataqueEspecialRealizado=ataqueRealizado;
    }
    
    public boolean getDescansado()
    {
        return descansado;
    }
    
    public void setDescansado(boolean d)
    {
        descansado=d;
    }
    
    public void descansarTurno()
    {
        descansado=true;
    }
    
    public void pokemonHaRealizadoAtaqueEspecial()
    {
       ataqueEspecialRealizado=true;
    }
    
    public void setVidaActual(int v)
    {
        vidaActual=v;
    }
    
    public int getVidaActual()
    {
        return vidaActual;
    }
    
    public int dañoNormalPokemon(Pokemon defensor) //Funcion que me calcula el daño normal normal que hace un pokemon
    {
       int v=(int) (Math.random()*15+1)+85;
       
       double bonificacion=bonificacionPrimerAtacante( defensor.getTipo());
       
       double valor1=0.1;
       
       double valor2=bonificacion;
       
       double valor3=1;
       
       double valor4=v;
       
       double valor5=0.2;
       
       double valor6=nivel;
       
       double valor7=ataque;
       
       double valor8=25;
       
       double valor9=defensor.getDefensa();
       
       double valor10=2;
        
      double  daño1=valor1*valor2*valor3*valor4;
      
      double daño2=((valor5 * valor6 + 1) * valor7) / (valor8 * valor9) + valor10;
     
       double dañoTotal=daño1*daño2;
       return(int) dañoTotal;
    }
    
    public int dañoEspecialPokemon(Pokemon defensor) //Funcion que me calcula el daño especial que hace el pokemon
    {
       int v=(int) (Math.random()*15+1)+85;
       
       double bonificacion=bonificacionPrimerAtacante( defensor.getTipo());
       
       double valor1=0.1;
       
       double valor2=bonificacion;
       
       double valor3=1;
       
       double valor4=v;
       
       double valor5=0.2;
       
       double valor6=nivel;
       
       double valor7=ataqueEspecial;
       
       double valor8=25;
       
       double valor9=defensor.getDefensaEspecial();
       
       double valor10=2;
        
      double  daño1=valor1*valor2*valor3*valor4;
      
      double daño2=((valor5 * valor6 + 1) * valor7) / (valor8 * valor9) + valor10;
     
       double dañoTotal=daño1*daño2;
       return(int) dañoTotal;
    }
    
    public void porcentajeMejora(int porcentaje) //Metodo que utilizo para subir las estadisticas del pokemon dependienzo del porcentaje pasado como argumento.
    {
        double mejora = ((double) porcentaje / 100) + 1;
        vidaActual=(int)(vidaActual*mejora);
        vida=(int)(vida*mejora);
        ataque=(int)(ataque*mejora);
        defensa=(int)(defensa*mejora);
        ataqueEspecial=(int)(ataqueEspecial*mejora);
        defensaEspecial=(int)(defensaEspecial*mejora);
        velocidad=(int)(velocidad*mejora); 
    }       
}
