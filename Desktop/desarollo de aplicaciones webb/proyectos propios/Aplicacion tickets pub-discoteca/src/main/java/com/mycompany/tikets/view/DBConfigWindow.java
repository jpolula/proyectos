package com.mycompany.tikets.view;

import javax.swing.*;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.util.prefs.Preferences;
import com.mycompany.tikets.util.UIScaler;

/**
 * Ventana para configurar los datos de conexión a la base de datos
 */
public class DBConfigWindow extends JDialog {
    private final JTextField txtHost = new JTextField(20);
    private final JTextField txtPort = new JTextField(20);
    private final JTextField txtDatabase = new JTextField(20);
    private final JTextField txtUser = new JTextField(20);
    private final JPasswordField txtPassword = new JPasswordField(20);
    private final Preferences prefs = Preferences.userNodeForPackage(DBConfigWindow.class);

    public DBConfigWindow(JFrame parent) {
        super(parent, "Configuración de Base de Datos", true);
        
        // Inicialización básica
        UIScaler.scaleDialog(this, 450, 300);
        setLocationRelativeTo(parent);
        setDefaultCloseOperation(DISPOSE_ON_CLOSE);
        
        // Aplicar escala global de fuentes
        UIScaler.scaleGlobalFonts();
        
        // Panel principal
        JPanel panel = new JPanel(new GridLayout(6, 2, 10, 10));
        panel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        
        // Añadir componentes
        JLabel lblHost = new JLabel("Host:");
        UIScaler.scaleFont(lblHost);
        panel.add(lblHost);
        UIScaler.scaleFont(txtHost);
        panel.add(txtHost);
        
        JLabel lblPort = new JLabel("Puerto:");
        UIScaler.scaleFont(lblPort);
        panel.add(lblPort);
        UIScaler.scaleFont(txtPort);
        panel.add(txtPort);
        
        JLabel lblDatabase = new JLabel("Base de datos:");
        UIScaler.scaleFont(lblDatabase);
        panel.add(lblDatabase);
        UIScaler.scaleFont(txtDatabase);
        panel.add(txtDatabase);
        
        JLabel lblUser = new JLabel("Usuario:");
        UIScaler.scaleFont(lblUser);
        panel.add(lblUser);
        UIScaler.scaleFont(txtUser);
        panel.add(txtUser);
        
        JLabel lblPassword = new JLabel("Contraseña:");
        UIScaler.scaleFont(lblPassword);
        panel.add(lblPassword);
        UIScaler.scaleFont(txtPassword);
        panel.add(txtPassword);
        
        // Panel de botones
        JPanel buttonPanel = new JPanel();
        JButton testButton = new JButton("Probar conexión");
        JButton saveButton = new JButton("Guardar");
        
        // Aumentar tamaño de fuente de los botones
        UIScaler.scaleFont(testButton);
        UIScaler.scaleFont(saveButton);
        JButton cancelButton = new JButton("Cancelar");
        
        testButton.addActionListener(this::probarConexion);
        saveButton.addActionListener(this::guardarConfiguracion);
        cancelButton.addActionListener(e -> dispose());
        
        buttonPanel.add(testButton);
        buttonPanel.add(saveButton);
        buttonPanel.add(cancelButton);
        
        // Añadir paneles al diálogo
        setLayout(new BorderLayout());
        add(panel, BorderLayout.CENTER);
        add(buttonPanel, BorderLayout.SOUTH);
        
        // Cargar configuración
        cargarConfiguracion();
    }
    
    private void cargarConfiguracion() {
        // Siempre usar localhost para evitar problemas de permisos
        txtHost.setText("localhost");
        txtPort.setText(prefs.get("db_port", "3306"));
        txtDatabase.setText(prefs.get("db_name", "tikets"));
        txtUser.setText(prefs.get("db_user", "root"));
        txtPassword.setText(prefs.get("db_pass", ""));
    }
    
    private void guardarConfiguracion(ActionEvent e) {
        String host = txtHost.getText().trim();
        String port = txtPort.getText().trim();
        String database = txtDatabase.getText().trim();
        String user = txtUser.getText().trim();
        String password = new String(txtPassword.getPassword());
        
        // Guardar en preferencias locales (no en la base de datos)
        try {
            prefs.put("db_host", host);
            prefs.put("db_port", port);
            prefs.put("db_name", database);
            prefs.put("db_user", user);
            prefs.put("db_pass", password);
            prefs.flush(); // Asegurar que los cambios se guarden inmediatamente
            
            // Probar la conexión para verificar que los datos son correctos
            Class.forName("com.mysql.cj.jdbc.Driver");
            String url = "jdbc:mysql://" + host + ":" + port + "/" + database;
            java.sql.Connection conn = java.sql.DriverManager.getConnection(url, user, password);
            conn.close();
            
            // Mostrar mensaje de éxito
            JOptionPane.showMessageDialog(this, "Configuración de la base de datos guardada correctamente.", 
                    "Configuración guardada", JOptionPane.INFORMATION_MESSAGE);
            
            // Cerrar ventana
            dispose();
        } catch (Exception ex) {
            System.err.println("Error al guardar configuración: " + ex.getMessage());
            JOptionPane.showMessageDialog(this, "Error al guardar la configuración: " + ex.getMessage(), 
                    "Error", JOptionPane.ERROR_MESSAGE);
        }
    }
    
    private void probarConexion(ActionEvent e) {
        String host = txtHost.getText().trim();
        String port = txtPort.getText().trim();
        String database = txtDatabase.getText().trim();
        String user = txtUser.getText().trim();
        String password = new String(txtPassword.getPassword());
        
        // Mostrar diálogo de carga
        JDialog loadingDialog = new JDialog(this, "Probando conexión...", true);
        loadingDialog.setSize(300, 100);
        loadingDialog.setLayout(new BorderLayout());
        loadingDialog.add(new JLabel("Probando conexión con la base de datos...", JLabel.CENTER), BorderLayout.CENTER);
        loadingDialog.setLocationRelativeTo(this);
        
        // Usar un hilo separado para no bloquear la interfaz
        new Thread(() -> {
            try {
                // Mostrar diálogo de carga
                loadingDialog.setVisible(true);
                
                // Intentar conectar
                Class.forName("com.mysql.cj.jdbc.Driver");
                String url = "jdbc:mysql://" + host + ":" + port + "/" + database + "?useSSL=false";
                
                // Intentar conectar con un timeout
                java.util.Properties props = new java.util.Properties();
                props.setProperty("user", user);
                props.setProperty("password", password);
                props.setProperty("connectTimeout", "3000"); // 3 segundos de timeout
                
                try (java.sql.Connection conn = java.sql.DriverManager.getConnection(url, props)) {
                    // Obtener información de la base de datos
                    String dbProduct = conn.getMetaData().getDatabaseProductName();
                    String dbVersion = conn.getMetaData().getDatabaseProductVersion();
                    
                    // Obtener el número de tablas
                    int tableCount = 0;
                    try (java.sql.ResultSet rs = conn.getMetaData().getTables(database, null, "%", new String[]{"TABLE"})) {
                        while (rs.next()) tableCount++;
                    }
                    
                    // Mostrar información detallada
                    String message = "<html><b>¡Conexión exitosa!</b><br><br>" +
                                   "<b>Servidor:</b> " + host + ":" + port + "<br>" +
                                   "<b>Base de datos:</b> " + database + "<br>" +
                                   "<b>Motor:</b> " + dbProduct + " " + dbVersion + "<br>" +
                                   "<b>Tablas encontradas:</b> " + tableCount + "<br><br>" +
                                   "La configuración es correcta.";
                    
                    // Cerrar diálogo de carga
                    loadingDialog.dispose();
                    
                    // Mostrar mensaje de éxito
                    JOptionPane.showMessageDialog(DBConfigWindow.this, 
                            message, 
                            "Conexión exitosa", 
                            JOptionPane.INFORMATION_MESSAGE);
                }
            } catch (java.sql.SQLException ex) {
                // Cerrar diálogo de carga
                loadingDialog.dispose();
                
                // Mostrar error detallado
                String errorMsg = "<html><b>Error de conexión:</b><br>" +
                                ex.getMessage() + "<br><br>" +
                                "<b>Detalles técnicos:</b><br>" +
                                "- Código de error: " + ex.getErrorCode() + "<br>" +
                                "- Estado SQL: " + ex.getSQLState() + "<br><br>" +
                                "Verifica que:<br>" +
                                "1. El servidor MySQL está en ejecución<br>" +
                                "2. El usuario y contraseña son correctos<br>" +
                                "3. La base de datos existe<br>" +
                                "4. El puerto es el correcto (por defecto: 3306)";
                
                JOptionPane.showMessageDialog(DBConfigWindow.this, 
                        errorMsg, 
                        "Error de conexión", 
                        JOptionPane.ERROR_MESSAGE);
            } catch (ClassNotFoundException ex) {
                loadingDialog.dispose();
                JOptionPane.showMessageDialog(DBConfigWindow.this, 
                        "No se encontró el controlador JDBC de MySQL.\nAsegúrate de tener mysql-connector-j en el classpath.", 
                        "Error", 
                        JOptionPane.ERROR_MESSAGE);
            } catch (Exception ex) {
                loadingDialog.dispose();
                JOptionPane.showMessageDialog(DBConfigWindow.this, 
                        "Error inesperado: " + ex.getMessage(), 
                        "Error", 
                        JOptionPane.ERROR_MESSAGE);
            }
        }).start();
        
        // Mostrar diálogo de carga
        loadingDialog.setVisible(true);
    }
}
