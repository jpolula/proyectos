package com.mycompany.tikets.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Clase de utilidad para gestionar la conexión con la base de datos MySQL
 */
public class DatabaseConnection {
    
    // Configuración dinámica para conexión multi-equipo
    private static Connection connection = null;
    private static String URL = null;
    private static String USER = null;
    private static String PASSWORD = null;
    private static boolean configCargada = false;
    
    /**
     * Carga la configuración de la base de datos desde las preferencias del usuario
     * Esta función se llama antes de cada intento de conexión
     */
    private static void cargarConfiguracionDesdePreferencias() {
        // Forzar la conexión a tikets_db con usuario root sin contraseña
        String host = "localhost";
        String port = "3306";
        String db = "tikets_db";
        String user = "root";
        String pass = "";
        
        // Usar la clase actual para las preferencias en lugar de DBConfigWindow
        java.util.prefs.Preferences prefs = java.util.prefs.Preferences.userNodeForPackage(DatabaseConnection.class);
        prefs.put("db_host", host);
        prefs.put("db_port", port);
        prefs.put("db_name", db);
        prefs.put("db_user", user);
        prefs.put("db_pass", pass);
        try {
            prefs.flush();
        } catch (Exception e) {
            System.err.println("Error al guardar preferencias: " + e.getMessage());
        }
        
        URL = "jdbc:mysql://" + host + ":" + port + "/" + db;
        USER = user;
        PASSWORD = pass;
        configCargada = true;
        System.out.println("URL de conexión FORZADA: " + URL);
        System.out.println("Usuario FORZADO: " + USER);
        System.out.println("Base de datos FORZADA: " + db);
    }
    private static boolean demoMode = false;
    private static boolean xamppChecked = false;
    
    /**
     * Obtiene una conexión a la base de datos
     * @return Objeto Connection
     * @throws SQLException si hay un error al conectar
     */
    // Variable para controlar si ya se mostró el mensaje de error
    private static boolean errorMostrado = false;
    
    /**
     * Cierra la conexión actual a la base de datos si está abierta.
     * Este método es útil para forzar una nueva conexión con parámetros actualizados.
     */
    public static void cerrarConexion() {
        if (connection != null) {
            try {
                if (!connection.isClosed()) {
                    connection.close();
                    System.out.println("Conexión cerrada manualmente");
                }
            } catch (SQLException e) {
                System.err.println("Error al cerrar la conexión: " + e.getMessage());
            } finally {
                connection = null;
            }
        }
    }
    
    /**
     * Obtiene una conexión a la base de datos
     * @return Objeto Connection
     * @throws SQLException si hay un error al conectar
     */
    public static Connection getConnection() throws SQLException {
        System.out.println("=== INTENTO DE CONEXIÓN A LA BASE DE DATOS ===");
        
        // Forzar cierre de la conexión existente para asegurar una nueva conexión
        if (connection != null) {
            try {
                if (!connection.isClosed()) {
                    connection.close();
                    System.out.println("Conexión anterior cerrada para forzar reconexión");
                }
                connection = null;
            } catch (SQLException e) {
                System.out.println("Error al cerrar la conexión existente: " + e.getMessage());
                connection = null;
            }
        }
        
        if (demoMode && !xamppChecked) {
            // Si estamos en modo demo pero no hemos verificado XAMPP, intentamos verificar
            System.out.println("En modo demo, verificando conexión XAMPP...");
            checkXamppConnection();
        }
        
        if (demoMode) {
            System.out.println("Aplicación en modo demo, no se intentará conectar a la base de datos");
            throw new SQLException("Aplicación en modo demo");
        }
        
        // Leer configuración dinámica antes de cada conexión
        cargarConfiguracionDesdePreferencias();
        
        // Asegurarse de que la conexión está cerrada
        if (connection != null && !connection.isClosed()) {
            try {
                System.out.println("Cerrando conexión existente para aplicar nueva configuración...");
                connection.close();
                connection = null;
            } catch (SQLException e) {
                System.out.println("Error al cerrar la conexión existente: " + e.getMessage());
                connection = null;
            }
        }
        
        if (connection == null || connection.isClosed()) {
            try {
                System.out.println("Cargando driver MySQL...");
                // Intentar con diferentes nombres de driver para mayor compatibilidad
                try {
                    Class.forName("com.mysql.cj.jdbc.Driver");
                    System.out.println("Driver com.mysql.cj.jdbc.Driver cargado correctamente");
                } catch (ClassNotFoundException e1) {
                    System.out.println("Error al cargar com.mysql.cj.jdbc.Driver: " + e1.getMessage());
                    try {
                        Class.forName("com.mysql.jdbc.Driver");
                        System.out.println("Driver com.mysql.jdbc.Driver cargado correctamente");
                    } catch (ClassNotFoundException e2) {
                        System.out.println("Error al cargar com.mysql.jdbc.Driver: " + e2.getMessage());
                        try {
                            Class.forName("org.gjt.mm.mysql.Driver");
                            System.out.println("Driver org.gjt.mm.mysql.Driver cargado correctamente");
                        } catch (ClassNotFoundException e3) {
                            System.out.println("Error al cargar org.gjt.mm.mysql.Driver: " + e3.getMessage());
                            demoMode = true;
                            if (!errorMostrado) {
                                System.out.println("No se encontró el driver MySQL. La aplicación usará el modo demo.");
                                System.out.println("Asegúrate de que XAMPP está en ejecución y MySQL está activo.");
                                errorMostrado = true;
                            }
                            throw new SQLException("Driver MySQL no encontrado", e3);
                        }
                    }
                }
                
                // Intentar conexión explícita a XAMPP
                System.out.println("Intentando conectar a MySQL con URL: " + URL);
                System.out.println("Usuario: " + USER);
                connection = DriverManager.getConnection(URL, USER, PASSWORD);
                System.out.println("Conexión a la base de datos XAMPP establecida correctamente.");
                demoMode = false;
                errorMostrado = false; // Resetear el flag si la conexión tiene éxito
                
                // Verificar que la base de datos existe
                System.out.println("Verificando existencia de tablas...");
                java.sql.Statement stmt = connection.createStatement();
                java.sql.ResultSet rs = stmt.executeQuery("SHOW TABLES LIKE 'configuracion'");
                if (rs.next()) {
                    System.out.println("Tabla 'configuracion' encontrada en la base de datos");
                } else {
                    System.out.println("ADVERTENCIA: La tabla 'configuracion' no existe en la base de datos");
                    // Intentar crear la tabla
                    System.out.println("Intentando crear la tabla 'configuracion'...");
                    try {
                        stmt.execute("CREATE TABLE IF NOT EXISTS configuracion (" +
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
                                "fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP" +
                                ")");
                        System.out.println("Tabla 'configuracion' creada correctamente");
                        
                        // Insertar registro inicial
                        stmt.execute("INSERT INTO configuracion (id) VALUES (1)");
                        System.out.println("Registro inicial creado en la tabla 'configuracion'");
                    } catch (SQLException e) {
                        System.err.println("Error al crear la tabla 'configuracion': " + e.getMessage());
                    }
                }
                rs.close();
                stmt.close();
            } catch (SQLException e) {
                demoMode = true;
                if (!errorMostrado) {
                    System.out.println("Error al conectar a la base de datos XAMPP: " + e.getMessage());
                    System.out.println("Verifica que XAMPP está en ejecución y MySQL ha sido iniciado.");
                    System.out.println("La aplicación usará el modo demo por ahora.");
                    errorMostrado = true;
                }
                throw e;
            }
        } else {
            System.out.println("Usando conexión existente a la base de datos");
        }
        return connection;
    }
    
    /**
     * Cierra la conexión a la base de datos si está abierta
     * @param reiniciarConfiguracion Si es true, se reinicia la configuración para forzar una recarga
     */
    public static void closeConnection(boolean reiniciarConfiguracion) {
        if (connection != null) {
            try {
                if (!connection.isClosed()) {
                    connection.close();
                    System.out.println("Conexión a la base de datos cerrada");
                }
            } catch (SQLException e) {
                System.err.println("Error al cerrar la conexión: " + e.getMessage());
            } finally {
                connection = null;
                if (reiniciarConfiguracion) {
                    // Reiniciar la configuración para forzar una recarga en la próxima conexión
                    configCargada = false;
                    URL = null;
                    USER = null;
                    PASSWORD = null;
                    System.out.println("Configuración de conexión reiniciada");
                }
            }
        }
    }
    
    /**
     * Cierra la conexión a la base de datos si está abierta
     * Método sobrecargado para mantener compatibilidad
     */
    public static void closeConnection() {
        closeConnection(false);
    }
    
    /**
     * Verifica si la aplicación está en modo demo
     * @return true si está en modo demo, false si hay conexión a la base de datos
     */
    public static boolean isInDemoMode() {
        return demoMode;
    }
    
    /**
     * Verifica si XAMPP está disponible y ejecutándose
     * Intenta establecer una conexión a MySQL en XAMPP
     */
    private static void checkXamppConnection() {
        xamppChecked = true;
        if (demoMode) {
            try {
                // Intentar primero cargar el driver
                try {
                    Class.forName("com.mysql.cj.jdbc.Driver");
                } catch (ClassNotFoundException e1) {
                    try {
                        Class.forName("com.mysql.jdbc.Driver");
                    } catch (ClassNotFoundException e2) {
                        try {
                            Class.forName("org.gjt.mm.mysql.Driver");
                        } catch (ClassNotFoundException e3) {
                            System.out.println("Verificación XAMPP: No se encontró el driver MySQL.");
                            return; // Mantenemos el modo demo
                        }
                    }
                }
                
                // Probar conexión directamente a MySQL (no a la base de datos específica)
                Connection tempConnection = DriverManager.getConnection(
                    "jdbc:mysql://localhost:3306/", USER, PASSWORD);
                
                if (tempConnection != null) {
                    System.out.println("Conexión a XAMPP MySQL establecida correctamente. Creando base de datos...");
                    
                    // Si llegamos aquí, XAMPP está disponible
                    tempConnection.close();
                    
                    // Reiniciamos el modo demo para intentar conectar a la base de datos real
                    demoMode = false;
                    errorMostrado = false;
                    
                    // Crear la base de datos si no existe
                    initializeDatabase();
                }
            } catch (Exception e) {
                System.out.println("Verificación XAMPP: MySQL no está disponible. " + e.getMessage());
                // Mantener el modo demo
            }
        }
    }
    
    /**
     * Verifica la conexión a la base de datos sin crear ni eliminar nada
     */
    public static void initializeDatabase() {
        try {
            // Cargar la configuración antes de intentar conectar
            cargarConfiguracionDesdePreferencias();
            
            // Simplemente verificamos que podemos conectarnos a la base de datos
            Connection conn = getConnection();
            if (conn != null) {
                System.out.println("Conexión a la base de datos establecida correctamente.");
                // No hacemos nada más, solo verificamos la conexión
                conn.close();
            }
        } catch (SQLException e) {
            System.err.println("Error al conectar a la base de datos: " + e.getMessage());
        }
    }
}
