package com.mycompany.tikets.model;

import com.mycompany.tikets.controller.ConfiguracionController;

/**
 * Clase para almacenar la configuración del club
 */
public class ConfiguracionClub {
    
    private static ConfiguracionClub instancia;
    private ConfiguracionController configuracionController;
    
    private String nombreClub;
    private String cif;
    private String direccion1;
    private String direccion2;
    private double precioCopa;
    private double precioCerveza;
    private double precioSinConsumicion;
    private double precioSoloTicket;
    private String rutaLogo; // Ruta al archivo de imagen del logo
    private String fraseDelDia;
    private String condicionesEntrada;
    private String condicionesConsumicion;
    
    private ConfiguracionClub() {
        // Inicializar controlador
        configuracionController = new ConfiguracionController();
        
        // Cargar desde la base de datos
        cargarConfiguracionBaseDatos();
    }
    
    /**
     * Carga la configuración desde la base de datos
     */
    private void cargarConfiguracionBaseDatos() {
        // Valores por defecto
        nombreClub = "BARU SUMMER CLUB";
        cif = "B44958882";
        direccion1 = "Carretera Benamaurel";
        direccion2 = "18800, Baza, Granada";
        precioCopa = 5.00;
        precioCerveza = 3.50;
        precioSinConsumicion = 12.50;
        precioSoloTicket = 3.00;
        rutaLogo = ""; // Por defecto no hay logo
        fraseDelDia = "";
        condicionesEntrada = "Prohibida la entrada a menores de 18 años";
        condicionesConsumicion = "Válido para una consumición";
        
        try {
            // Intentar cargar desde la base de datos
            String valorNombreClub = configuracionController.obtenerConfiguracion("nombre_club");
            String valorCif = configuracionController.obtenerConfiguracion("cif");
            String valorDireccion1 = configuracionController.obtenerConfiguracion("direccion1");
            String valorDireccion2 = configuracionController.obtenerConfiguracion("direccion2");
            String valorPrecioCopa = configuracionController.obtenerConfiguracion("precio_copa");
            String valorPrecioCerveza = configuracionController.obtenerConfiguracion("precio_cerveza");
            String valorPrecioSinConsumicion = configuracionController.obtenerConfiguracion("precio_sin_consumicion");
            String valorPrecioSoloTicket = configuracionController.obtenerConfiguracion("precio_solo_ticket");
            String valorRutaLogo = configuracionController.obtenerConfiguracion("ruta_logo");
            String valorFraseDelDia = configuracionController.obtenerConfiguracion("frase_del_dia");
            String valorCondicionesEntrada = configuracionController.obtenerConfiguracion("condiciones_entrada");
            String valorCondicionesConsumicion = configuracionController.obtenerConfiguracion("condiciones_consumicion");
            
            // Asignar valores si existen en la base de datos
            if (valorNombreClub != null) {
                nombreClub = valorNombreClub;
            } else {
                nombreClub = "BARU SUMMER CLUB";
            }
            
            if (valorCif != null) {
                cif = valorCif;
            } else {
                cif = "B44958882";
            }
            
            if (valorDireccion1 != null) {
                direccion1 = valorDireccion1;
            } else {
                direccion1 = "Carretera Benamaurel";
            }
            
            if (valorDireccion2 != null) {
                direccion2 = valorDireccion2;
            } else {
                direccion2 = "18800, Baza, Granada";
            }
            
            if (valorPrecioCopa != null) {
                precioCopa = Double.parseDouble(valorPrecioCopa);
            } else {
                precioCopa = 5.00;
            }
            
            if (valorPrecioCerveza != null) {
                precioCerveza = Double.parseDouble(valorPrecioCerveza);
            } else {
                precioCerveza = 3.50;
            }
            
            if (valorPrecioSinConsumicion != null) {
                try {
                    precioSinConsumicion = Double.parseDouble(valorPrecioSinConsumicion);
                } catch (NumberFormatException e) {
                    System.err.println("Error al parsear precio sin consumición: " + e.getMessage());
                }
            }
            
            if (valorPrecioSoloTicket != null) {
                try {
                    precioSoloTicket = Double.parseDouble(valorPrecioSoloTicket);
                } catch (NumberFormatException e) {
                    System.err.println("Error al parsear precio solo ticket: " + e.getMessage());
                }
            }
            
            // Asignar ruta del logo si existe
            if (valorRutaLogo != null && !valorRutaLogo.isEmpty()) {
                rutaLogo = valorRutaLogo;
            }
            
            // Asignar frase del día si existe
            if (valorFraseDelDia != null) {
                fraseDelDia = valorFraseDelDia;
            }
            
            // Asignar condiciones de entrada si existen
            if (valorCondicionesEntrada != null) {
                condicionesEntrada = valorCondicionesEntrada;
            }
            
            // Asignar condiciones de consumición si existen
            if (valorCondicionesConsumicion != null) {
                condicionesConsumicion = valorCondicionesConsumicion;
            }
        } catch (Exception e) {
            // En caso de error, ya tenemos valores por defecto
            System.err.println("Error al cargar configuración desde base de datos: " + e.getMessage());
            System.out.println("Usando valores por defecto para la configuración del club");
        }
    }
    
    /**
     * Guarda la configuración en la base de datos
     */
    public boolean guardarConfiguracionBaseDatos() {
        try {
            boolean resultado = true;
            resultado = resultado && configuracionController.guardarConfiguracion("nombre_club", nombreClub);
            resultado = resultado && configuracionController.guardarConfiguracion("cif", cif);
            resultado = resultado && configuracionController.guardarConfiguracion("direccion1", direccion1);
            resultado = resultado && configuracionController.guardarConfiguracion("direccion2", direccion2);
            resultado = resultado && configuracionController.guardarConfiguracion("precio_copa", String.valueOf(precioCopa));
            resultado = resultado && configuracionController.guardarConfiguracion("precio_cerveza", String.valueOf(precioCerveza));
            resultado = resultado && configuracionController.guardarConfiguracion("precio_sin_consumicion", String.valueOf(precioSinConsumicion));
            resultado = resultado && configuracionController.guardarConfiguracion("ruta_logo", rutaLogo);
            return resultado;
        } catch (Exception e) {
            System.err.println("Error al guardar configuración en base de datos: " + e.getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene la instancia única de la configuración
     * @return Instancia de ConfiguracionClub
     */
    public static ConfiguracionClub getInstancia() {
        if (instancia == null) {
            instancia = new ConfiguracionClub();
        }
        return instancia;
    }
    
    // Getters y setters
    
    public String getNombreClub() {
        return nombreClub;
    }
    
    public void setNombreClub(String nombreClub) {
        this.nombreClub = nombreClub;
    }
    
    public String getCif() {
        return cif;
    }
    
    public void setCif(String cif) {
        this.cif = cif;
    }
    
    public String getDireccion1() {
        return direccion1;
    }
    
    public void setDireccion1(String direccion1) {
        this.direccion1 = direccion1;
    }
    
    public String getDireccion2() {
        return direccion2;
    }
    
    public void setDireccion2(String direccion2) {
        this.direccion2 = direccion2;
    }
    
    public double getPrecioCopa() {
        return precioCopa;
    }
    
    public void setPrecioCopa(double precioCopa) {
        this.precioCopa = precioCopa;
    }
    
    public double getPrecioCerveza() {
        return precioCerveza;
    }
    
    public void setPrecioCerveza(double precioCerveza) {
        this.precioCerveza = precioCerveza;
    }
    
    public double getPrecioSinConsumicion() {
        return precioSinConsumicion;
    }
    
    public void setPrecioSinConsumicion(double precioSinConsumicion) {
        this.precioSinConsumicion = precioSinConsumicion;
    }
    
    /**
     * Obtiene la ruta del archivo del logo
     * @return Ruta del logo
     */
    public String getRutaLogo() {
        return rutaLogo;
    }
    
    /**
     * Establece la ruta del archivo del logo
     * @param rutaLogo Ruta del logo
     */
    public void setRutaLogo(String rutaLogo) {
        this.rutaLogo = rutaLogo;
    }
    
    /**
     * Obtiene el precio del ticket sin consumición
     * @return Precio del ticket sin consumición
     */
    public double getPrecioSoloTicket() {
        return precioSoloTicket;
    }
    
    /**
     * Establece el precio del ticket sin consumición
     * @param precioSoloTicket Precio del ticket sin consumición
     */
    public void setPrecioSoloTicket(double precioSoloTicket) {
        this.precioSoloTicket = precioSoloTicket;
    }
    
    /**
     * Obtiene la frase del día
     * @return Frase del día
     */
    public String getFraseDelDia() {
        return fraseDelDia != null ? fraseDelDia : "";
    }
    
    /**
     * Establece la frase del día
     * @param fraseDelDia Frase del día
     */
    public void setFraseDelDia(String fraseDelDia) {
        this.fraseDelDia = fraseDelDia;
    }
    
    /**
     * Obtiene las condiciones de entrada
     * @return Condiciones de entrada
     */
    public String getCondicionesEntrada() {
        return condicionesEntrada != null ? condicionesEntrada : "";
    }
    
    /**
     * Establece las condiciones de entrada
     * @param condicionesEntrada Condiciones de entrada
     */
    public void setCondicionesEntrada(String condicionesEntrada) {
        this.condicionesEntrada = condicionesEntrada;
    }
    
    /**
     * Obtiene las condiciones de consumición
     * @return Condiciones de consumición
     */
    public String getCondicionesConsumicion() {
        return condicionesConsumicion != null ? condicionesConsumicion : "";
    }
    
    /**
     * Establece las condiciones de consumición
     * @param condicionesConsumicion Condiciones de consumición
     */
    public void setCondicionesConsumicion(String condicionesConsumicion) {
        this.condicionesConsumicion = condicionesConsumicion;
    }
}
