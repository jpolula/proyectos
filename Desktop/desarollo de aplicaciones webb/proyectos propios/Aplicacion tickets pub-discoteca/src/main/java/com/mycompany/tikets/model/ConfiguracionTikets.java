package com.mycompany.tikets.model;

import com.mycompany.tikets.util.DatabaseConnection;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Modelo para la tabla configuracion en la base de datos tikets_db
 */
public class ConfiguracionTikets {
    
    private static ConfiguracionTikets instancia;
    
    // Atributos que corresponden a las columnas de la tabla
    private int id;
    private String nombreClub;
    private String cif;
    private String direccion1;
    private String direccion2;
    private double precioCopa;
    private double precioCerveza;
    private double precioSinConsumicion;
    private double precioSoloTicket;
    private String rutaLogo;
    private String rutaIcono; // Nueva propiedad para la ruta del icono
    private String fraseDelDia;
    private String condicionesEntrada;
    private String condicionesConsumicion;
    private boolean imprimirTicket;
    private boolean mostrarPrecio;
    private boolean imprimirVale;
    private String impresora;
    
    /**
     * Constructor privado para el patrón Singleton
     */
    private ConfiguracionTikets() {
        cargarConfiguracion();
    }
    
    /**
     * Obtiene la instancia única de la configuración
     * @return Instancia de ConfiguracionTikets
     */
    public static ConfiguracionTikets getInstancia() {
        if (instancia == null) {
            instancia = new ConfiguracionTikets();
        } else {
            // Forzar la recarga de la configuración desde la base de datos
            try {
                instancia.cargarConfiguracion();
                System.out.println("Recarga de configuración forzada desde getInstancia()");
            } catch (Exception e) {
                System.err.println("Error al recargar la configuración: " + e.getMessage());
                e.printStackTrace();
            }
        }
        return instancia;
    }
    
    /**
     * Carga la configuración desde la base de datos
     */
    public void cargarConfiguracion() {
        System.out.println("=== INICIO CARGAR CONFIGURACIÓN ===");
        
        // Valores por defecto en caso de error
        id = 1;
        nombreClub = "Club de Eventos";
        cif = "";
        direccion1 = "";
        direccion2 = "";
        precioCopa = 5.00;
        precioCerveza = 3.50;
        precioSinConsumicion = 10.00;
        precioSoloTicket = 3.00;
        rutaLogo = "";
        rutaIcono = ""; // Inicializar la ruta del icono
        fraseDelDia = "";
        condicionesEntrada = "Prohibida la entrada a menores de 18 años";
        condicionesConsumicion = "Válido para una consumición";
        imprimirTicket = true;
        mostrarPrecio = true;
        imprimirVale = true;
        impresora = "";
        
        System.out.println("Valores por defecto establecidos:");
        System.out.println("Nombre del club (default): " + nombreClub);
        System.out.println("CIF (default): " + cif);
        System.out.println("Precio copa (default): " + precioCopa);
        
        Connection conn = null;
        PreparedStatement pstmt = null;
        ResultSet rs = null;
        
        try {
            // Intentar conectar a la base de datos
            try {
                conn = DatabaseConnection.getConnection();
                System.out.println("Conexión establecida para cargar configuración");
            } catch (SQLException e) {
                System.err.println("Error al conectar a la base de datos: " + e.getMessage());
                e.printStackTrace();
                return; // Usar valores por defecto
            }
            
            // Verificar si existe la tabla y tiene la estructura correcta
            try {
                // Primero verificar si la tabla existe
                String sqlVerificar = "SHOW TABLES LIKE 'configuracion'";
                pstmt = conn.prepareStatement(sqlVerificar);
                rs = pstmt.executeQuery();
                
                boolean tablaExiste = rs.next();
                rs.close();
                pstmt.close();
                
                if (!tablaExiste) {
                    System.err.println("La tabla 'configuracion' no existe en la base de datos");
                    crearTablaConfiguracion(conn);
                } else {
                    System.out.println("La tabla 'configuracion' existe en la base de datos");
                    
                    // Verificar si la estructura es correcta
                    try {
                        // Intentar obtener la estructura de la tabla
                        String sqlDescribe = "DESCRIBE configuracion";
                        pstmt = conn.prepareStatement(sqlDescribe);
                        rs = pstmt.executeQuery();
                        
                        boolean nombreClubExiste = false;
                        while (rs.next()) {
                            String columnName = rs.getString("Field");
                            if ("nombre_club".equals(columnName)) {
                                nombreClubExiste = true;
                                break;
                            }
                        }
                        rs.close();
                        pstmt.close();
                        
                        if (!nombreClubExiste) {
                            System.err.println("La columna 'nombre_club' no existe en la tabla 'configuracion'");
                            // Recrear la tabla con la estructura correcta
                            String sqlDropTable = "DROP TABLE configuracion";
                            pstmt = conn.prepareStatement(sqlDropTable);
                            pstmt.executeUpdate();
                            pstmt.close();
                            System.out.println("Tabla 'configuracion' eliminada para recrearla con la estructura correcta");
                            
                            crearTablaConfiguracion(conn);
                        }
                    } catch (SQLException e) {
                        System.err.println("Error al verificar la estructura de la tabla: " + e.getMessage());
                        e.printStackTrace();
                    }
                }
            } catch (SQLException e) {
                System.err.println("Error al verificar la existencia de la tabla: " + e.getMessage());
                e.printStackTrace();
                return; // Usar valores por defecto
            }
            
            // Consultar la configuración
            String sql = "SELECT * FROM configuracion WHERE id = 1";
            try {
                pstmt = conn.prepareStatement(sql);
                rs = pstmt.executeQuery();
                
                if (rs.next()) {
                    try {
                        id = rs.getInt("id");
                        System.out.println("ID cargado: " + id);
                        
                        // Obtener valores de texto con manejo de nulos
                        String tempNombreClub = rs.getString("nombre_club");
                        nombreClub = (tempNombreClub != null) ? tempNombreClub : "Club de Eventos";
                        System.out.println("Nombre del club cargado: " + nombreClub);
                        
                        String tempCif = rs.getString("cif");
                        cif = (tempCif != null) ? tempCif : "";
                        System.out.println("CIF cargado: " + cif);
                        
                        String tempDir1 = rs.getString("direccion1");
                        direccion1 = (tempDir1 != null) ? tempDir1 : "";
                        System.out.println("Dirección 1 cargada: " + direccion1);
                        
                        String tempDir2 = rs.getString("direccion2");
                        direccion2 = (tempDir2 != null) ? tempDir2 : "";
                        System.out.println("Dirección 2 cargada: " + direccion2);
                        
                        // Obtener valores numéricos
                        precioCopa = rs.getDouble("precio_copa");
                        System.out.println("Precio copa cargado: " + precioCopa);
                        
                        precioCerveza = rs.getDouble("precio_cerveza");
                        System.out.println("Precio cerveza cargado: " + precioCerveza);
                        
                        precioSinConsumicion = rs.getDouble("precio_sin_consumicion");
                        System.out.println("Precio sin consumición cargado: " + precioSinConsumicion);
                        
                        precioSoloTicket = rs.getDouble("precio_solo_ticket");
                        System.out.println("Precio solo ticket cargado: " + precioSoloTicket);
                        
                        // Obtener ruta del logo
                        String tempRutaLogo = rs.getString("ruta_logo");
                        rutaLogo = (tempRutaLogo != null) ? tempRutaLogo : "";
                        System.out.println("Ruta logo cargada: " + rutaLogo);
                        
                        // Obtener ruta del icono
                        try {
                            String tempRutaIcono = rs.getString("ruta_icono");
                            rutaIcono = (tempRutaIcono != null) ? tempRutaIcono : "";
                            System.out.println("Ruta icono cargada: " + rutaIcono);
                        } catch (SQLException e) {
                            // Si la columna no existe, no hacer nada (se usará el valor por defecto)
                            System.out.println("La columna ruta_icono no existe en la tabla, se usará valor por defecto");
                        }
                        
                        // Obtener textos adicionales
                        String tempFraseDelDia = rs.getString("frase_del_dia");
                        fraseDelDia = (tempFraseDelDia != null) ? tempFraseDelDia : "";
                        System.out.println("Frase del día cargada: " + fraseDelDia);
                        
                        String tempCondEntrada = rs.getString("condiciones_entrada");
                        condicionesEntrada = (tempCondEntrada != null) ? tempCondEntrada : "Prohibida la entrada a menores de 18 años";
                        System.out.println("Condiciones entrada cargadas: " + condicionesEntrada);
                        
                        String tempCondConsumicion = rs.getString("condiciones_consumicion");
                        condicionesConsumicion = (tempCondConsumicion != null) ? tempCondConsumicion : "Válido para una consumición";
                        System.out.println("Condiciones consumición cargadas: " + condicionesConsumicion);
                        
                        // Obtener valores booleanos
                        imprimirTicket = rs.getBoolean("imprimir_ticket");
                        System.out.println("Imprimir ticket cargado: " + imprimirTicket);
                        
                        mostrarPrecio = rs.getBoolean("mostrar_precio");
                        System.out.println("Mostrar precio cargado: " + mostrarPrecio);
                        
                        imprimirVale = rs.getBoolean("imprimir_vale");
                        System.out.println("Imprimir vale cargado: " + imprimirVale);
                        
                        // Obtener impresora
                        String tempImpresora = rs.getString("impresora");
                        impresora = (tempImpresora != null) ? tempImpresora : "";
                        System.out.println("Impresora cargada: " + impresora);
                        
                        System.out.println("Configuración cargada correctamente de la base de datos");
                    } catch (SQLException e) {
                        System.err.println("Error al leer columna: " + e.getMessage());
                        e.printStackTrace();
                    }
                    System.out.println("Nombre del club: " + nombreClub);
                    System.out.println("CIF: " + cif);
                    System.out.println("Precio copa: " + precioCopa);
                    System.out.println("Precio cerveza: " + precioCerveza);
                    System.out.println("Precio sin consumición: " + precioSinConsumicion);
                } else {
                    System.out.println("No se encontró la configuración con id = 1");
                    // Intentar insertar un registro por defecto
                    try {
                        String sqlInsert = "INSERT INTO configuracion (id) VALUES (1)";
                        pstmt = conn.prepareStatement(sqlInsert);
                        pstmt.executeUpdate();
                        System.out.println("Se ha creado un registro de configuración por defecto");
                    } catch (SQLException ex) {
                        System.err.println("Error al crear configuración por defecto: " + ex.getMessage());
                    }
                }
            } catch (SQLException e) {
                System.err.println("Error al consultar la configuración: " + e.getMessage());
                e.printStackTrace();
            }
            
        } catch (Exception e) {
            System.err.println("Error general al cargar la configuración: " + e.getMessage());
            e.printStackTrace();
        } finally {
            // Cerrar recursos
            try {
                if (rs != null) rs.close();
                if (pstmt != null) pstmt.close();
                if (conn != null) conn.close();
            } catch (SQLException e) {
                System.err.println("Error al cerrar recursos: " + e.getMessage());
            }
        }
    }
    
    /**
     * Guarda la configuración en la base de datos
     * @return true si se guardó correctamente, false en caso contrario
     */
    public boolean guardarConfiguracion() {
        System.out.println("=== INICIO GUARDAR CONFIGURACIÓN ====");
        Connection conn = null;
        PreparedStatement pstmt = null;
        
        try {
            conn = DatabaseConnection.getConnection();
            
            // Verificar si existe el registro con id = 1
            String sqlVerificar = "SELECT COUNT(*) FROM configuracion WHERE id = 1";
            pstmt = conn.prepareStatement(sqlVerificar);
            ResultSet rs = pstmt.executeQuery();
            rs.next();
            int count = rs.getInt(1);
            rs.close();
            pstmt.close();
            
            // Imprimir los valores que se van a guardar
            System.out.println("Guardando configuración con los siguientes valores:");
            System.out.println("Nombre del club: " + nombreClub);
            
            if (count > 0) {
                // Actualizar el registro existente
                String sqlUpdate = "UPDATE configuracion SET " +
                    "nombre_club = ?, " +
                    "cif = ?, " +
                    "direccion1 = ?, " +
                    "direccion2 = ?, " +
                    "precio_copa = ?, " +
                    "precio_cerveza = ?, " +
                    "precio_sin_consumicion = ?, " +
                    "precio_solo_ticket = ?, " +
                    "ruta_logo = ?, " +
                    "ruta_icono = ?, " +
                    "frase_del_dia = ?, " +
                    "condiciones_entrada = ?, " +
                    "condiciones_consumicion = ?, " +
                    "imprimir_ticket = ?, " +
                    "mostrar_precio = ?, " +
                    "imprimir_vale = ?, " +
                    "impresora = ? " +
                    "WHERE id = 1";
                
                pstmt = conn.prepareStatement(sqlUpdate);
                pstmt.setString(1, nombreClub);
                pstmt.setString(2, cif);
                pstmt.setString(3, direccion1);
                pstmt.setString(4, direccion2);
                pstmt.setDouble(5, precioCopa);
                pstmt.setDouble(6, precioCerveza);
                pstmt.setDouble(7, precioSinConsumicion);
                pstmt.setDouble(8, precioSoloTicket);
                pstmt.setString(9, rutaLogo);
                pstmt.setString(10, rutaIcono);
                pstmt.setString(11, fraseDelDia);
                pstmt.setString(12, condicionesEntrada);
                pstmt.setString(13, condicionesConsumicion);
                pstmt.setBoolean(14, imprimirTicket);
                pstmt.setBoolean(15, mostrarPrecio);
                pstmt.setBoolean(16, imprimirVale);
                pstmt.setString(17, impresora);
                
                int filasActualizadas = pstmt.executeUpdate();
                System.out.println("Filas actualizadas: " + filasActualizadas);
                
                // Recargar la configuración para verificar que se guardó correctamente
                if (filasActualizadas > 0) {
                    // Reiniciar la instancia para forzar una recarga
                    instancia = null;
                    // Forzar la recarga de la configuración
                    cargarConfiguracion();
                    return true;
                }
                
                return filasActualizadas > 0;
            } else {
                // Insertar un nuevo registro
                String sqlInsert = "INSERT INTO configuracion (id, nombre_club, cif, direccion1, direccion2, " +
                    "precio_copa, precio_cerveza, precio_sin_consumicion, precio_solo_ticket, " +
                    "ruta_logo, ruta_icono, frase_del_dia, condiciones_entrada, condiciones_consumicion, " +
                    "imprimir_ticket, mostrar_precio, imprimir_vale, impresora) " +
                    "VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                pstmt = conn.prepareStatement(sqlInsert);
                pstmt.setString(1, nombreClub);
                pstmt.setString(2, cif);
                pstmt.setString(3, direccion1);
                pstmt.setString(4, direccion2);
                pstmt.setDouble(5, precioCopa);
                pstmt.setDouble(6, precioCerveza);
                pstmt.setDouble(7, precioSinConsumicion);
                pstmt.setDouble(8, precioSoloTicket);
                pstmt.setString(9, rutaLogo);
                pstmt.setString(10, rutaIcono);
                pstmt.setString(11, fraseDelDia);
                pstmt.setString(12, condicionesEntrada);
                pstmt.setString(13, condicionesConsumicion);
                pstmt.setBoolean(14, imprimirTicket);
                pstmt.setBoolean(15, mostrarPrecio);
                pstmt.setBoolean(16, imprimirVale);
                pstmt.setString(17, impresora);
                
                int filasInsertadas = pstmt.executeUpdate();
                System.out.println("Filas insertadas: " + filasInsertadas);
                
                // Recargar la configuración para verificar que se guardó correctamente
                if (filasInsertadas > 0) {
                    // Reiniciar la instancia para forzar una recarga
                    instancia = null;
                    // Forzar la recarga de la configuración
                    cargarConfiguracion();
                    return true;
                }
                
                return filasInsertadas > 0;
            }
        } catch (Exception e) {
            System.err.println("Error general al guardar configuración: " + e.getMessage());
            e.printStackTrace();
            return false;
        } finally {
            // Cerrar recursos
            try {
                if (pstmt != null) pstmt.close();
                if (conn != null) conn.close();
            } catch (SQLException e) {
                System.err.println("Error al cerrar recursos: " + e.getMessage());
            }
        }
    }
    
    // Getters y setters
    
    public int getId() {
        return id;
    }

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

    public double getPrecioSoloTicket() {
        return precioSoloTicket;
    }

    public void setPrecioSoloTicket(double precioSoloTicket) {
        this.precioSoloTicket = precioSoloTicket;
    }

    public String getRutaLogo() {
        return rutaLogo;
    }

    public void setRutaLogo(String rutaLogo) {
        this.rutaLogo = rutaLogo;
    }
    
    public String getRutaIcono() {
        return rutaIcono;
    }

    public void setRutaIcono(String rutaIcono) {
        this.rutaIcono = rutaIcono;
    }

    public String getFraseDelDia() {
        return fraseDelDia;
    }

    public void setFraseDelDia(String fraseDelDia) {
        this.fraseDelDia = fraseDelDia;
    }

    public String getCondicionesEntrada() {
        return condicionesEntrada;
    }

    public void setCondicionesEntrada(String condicionesEntrada) {
        this.condicionesEntrada = condicionesEntrada;
    }

    public String getCondicionesConsumicion() {
        return condicionesConsumicion;
    }

    public void setCondicionesConsumicion(String condicionesConsumicion) {
        this.condicionesConsumicion = condicionesConsumicion;
    }

    public boolean isImprimirTicket() {
        return imprimirTicket;
    }

    public void setImprimirTicket(boolean imprimirTicket) {
        this.imprimirTicket = imprimirTicket;
    }

    public boolean isMostrarPrecio() {
        return mostrarPrecio;
    }

    public void setMostrarPrecio(boolean mostrarPrecio) {
        this.mostrarPrecio = mostrarPrecio;
    }

    public boolean isImprimirVale() {
        return imprimirVale;
    }

    public void setImprimirVale(boolean imprimirVale) {
        this.imprimirVale = imprimirVale;
    }

    /**
     * Obtiene el nombre de la impresora configurada
     * @return Nombre de la impresora o null si no está configurada
     */
    public String getImpresora() {
        return impresora;
    }
    
    /**
     * Método de compatibilidad con versiones anteriores
     * @return Nombre de la impresora o null si no está configurada
     */
    public String getImpresoraPredeterminada() {
        return getImpresora();
    }

    public void setImpresora(String impresora) {
        this.impresora = impresora;
    }
    
    /**
     * Método auxiliar para crear la tabla configuracion con la estructura correcta
     * @param conn Conexión a la base de datos
     * @throws SQLException Si hay un error al ejecutar las consultas SQL
     */
    private void crearTablaConfiguracion(Connection conn) throws SQLException {
        PreparedStatement pstmt = null;
        try {
            String sqlCrearTabla = "CREATE TABLE IF NOT EXISTS configuracion (" +
                "id INT AUTO_INCREMENT PRIMARY KEY, " +
                "nombre_club VARCHAR(100) DEFAULT 'Club de Eventos', " +
                "cif VARCHAR(20) DEFAULT '', " +
                "direccion1 VARCHAR(100) DEFAULT '', " +
                "direccion2 VARCHAR(100) DEFAULT '', " +
                "precio_copa DECIMAL(10,2) DEFAULT 5.00, " +
                "precio_cerveza DECIMAL(10,2) DEFAULT 3.50, " +
                "precio_sin_consumicion DECIMAL(10,2) DEFAULT 10.00, " +
                "precio_solo_ticket DECIMAL(10,2) DEFAULT 3.00, " +
                "ruta_logo VARCHAR(255) DEFAULT '', " +
                "frase_del_dia TEXT, " +
                "condiciones_entrada TEXT DEFAULT 'Prohibida la entrada a menores de 18 años', " +
                "condiciones_consumicion TEXT DEFAULT 'Válido para una consumición', " +
                "imprimir_ticket BOOLEAN DEFAULT TRUE, " +
                "mostrar_precio BOOLEAN DEFAULT TRUE, " +
                "imprimir_vale BOOLEAN DEFAULT TRUE, " +
                "impresora VARCHAR(100) DEFAULT '', " +
                "fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
            
            pstmt = conn.prepareStatement(sqlCrearTabla);
            pstmt.executeUpdate();
            System.out.println("Tabla 'configuracion' creada correctamente");
            
            // Insertar registro inicial
            String sqlInsertInicial = "INSERT INTO configuracion (id) VALUES (1)";
            pstmt.close();
            pstmt = conn.prepareStatement(sqlInsertInicial);
            pstmt.executeUpdate();
            System.out.println("Registro inicial creado en la tabla 'configuracion'");
        } finally {
            if (pstmt != null) {
                pstmt.close();
            }
        }
    }
}
