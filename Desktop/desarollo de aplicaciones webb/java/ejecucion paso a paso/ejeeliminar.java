import java.util.*;

/**
 *
 * Haré una función que de una tabla de valor n y borraré el número que me diga el usuario.
 * Aparte de hacer eso crearé la funcion rellenarSinRepetidos(int [] tabla) que me rellenará 
 * la tabla de x elementos sin poner ningún repetido
 * 
 */
public class EliminarValor {
    
    static void rellenarTablaSinRepetidos(int tabla[])
    {
        int cont=0;
        int num;

        for(int i=0;i<tabla.length;i++)
        {
           while(cont<tabla.length)
           {
               num=(int) (Math.random()*20+1);
               if(encontrado(tabla, num)==false)
               {
                   tabla[cont]=num;
                   cont ++;
               }
           }
        }
    }
    
     static boolean encontrado(int tabla[], int num)
        {
            boolean encontrado=false;
            for(int i=0;i<tabla.length&&encontrado==false;i++)
            {
                if(tabla[i]==num)
                {
                    encontrado=true;
                }
            
            }
        
            return encontrado;
        }
    
    static int[] eliminarValor(int tabla[], int valor) //En esta función se copiará la tabla original menos el valor que haya puesto el usuario.
    {
        int tablaCopia[]=new int [0];
        for(int i=0;i<tabla.length;i++)
        {
            if(tabla[i]!=valor)  // En cada iteración del bucle se copiará el valor de [i] excepto si es el valor a borrar.
            {
                tablaCopia=Arrays.copyOf( tablaCopia, tablaCopia.length+1);
                tablaCopia[tablaCopia.length-1]=tabla[i];
            }
        
        }
        return tablaCopia;
    }
 
    public static void main(String[] args) 
    {
        
        Scanner sc=new Scanner(System.in);
        System.out.println("Dime el tamaño de tu tabla");
        
        int tamañoVector=sc.nextInt();
        int tabla[]=new int [tamañoVector];
        
        System.out.println("Dime el número que deseas borrar de tu tabla");
        int numeroAborrar=sc.nextInt();
        
        rellenarTablaSinRepetidos(tabla);
      
        Arrays.sort(tabla); //Ordeno la tabla tabla para que se quede mas clara la tabla.
        
        System.out.println(Arrays.toString(tabla)); // Enseño la tabla original para ver los cambios con respecto a la tabla devuelta.
        
        tabla=eliminarValor(tabla, numeroAborrar);
        System.out.println(Arrays.toString(tabla));
        
    }
}
