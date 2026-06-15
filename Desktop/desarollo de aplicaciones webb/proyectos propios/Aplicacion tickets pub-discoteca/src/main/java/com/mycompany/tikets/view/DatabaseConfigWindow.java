package com.mycompany.tikets.view;

import javax.swing.*;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.util.prefs.Preferences;
import com.mycompany.tikets.util.UIScaler;

/**
 * Ventana para configurar los datos de conexión a la base de datos
 */
public class DatabaseConfigWindow extends JDialog {
    private JTextField txtHost;
    private JTextField txtPort;
    private JTextField txtDatabase;
    private JTextField txtUser;
    private JPasswordField txtPassword;
    private Preferences prefs;

    public DatabaseConfigWindow(JFrame parent) {
        super(parent, "Configuración de Base de Datos", true);
        prefs = Preferences.userNodeForPackage(DatabaseConfigWindow.class);
        initComponents();
        cargarConfiguracion();
    }

    private void initComponents() {
        setDefaultCloseOperation(JDialog.DISPOSE_ON_CLOSE);
        setLayout(new BorderLayout(10, 10));
        
        // Usar UIScaler para establecer pantalla completa
        UIScaler.scaleDialog(this, 400, 300);
        
        // Aplicar escala global de fuentes
        UIScaler.scaleGlobalFonts();

        JPanel mainPanel = new JPanel(new GridBagLayout());
        mainPanel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(8, 8, 8, 8);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        JLabel lblHost = new JLabel("Host:");
        txtHost = new JTextField(20);
        txtHost.setEditable(true);
        // Aumentar tamaño de fuente
        UIScaler.scaleFont(lblHost);
        UIScaler.scaleFont(txtHost);
        gbc.gridx = 0; gbc.gridy = 0;
        mainPanel.add(lblHost, gbc);
        gbc.gridx = 1;
        mainPanel.add(txtHost, gbc);

        JLabel lblPort = new JLabel("Puerto:");
        txtPort = new JTextField(6);
        txtPort.setEditable(true);
        // Aumentar tamaño de fuente
        UIScaler.scaleFont(lblPort);
        UIScaler.scaleFont(txtPort);
        gbc.gridx = 0; gbc.gridy = 1;
        mainPanel.add(lblPort, gbc);
        gbc.gridx = 1;
        mainPanel.add(txtPort, gbc);

        JLabel lblDatabase = new JLabel("Base de datos:");
        txtDatabase = new JTextField(20);
        txtDatabase.setEditable(true);
        gbc.gridx = 0; gbc.gridy = 2;
        mainPanel.add(lblDatabase, gbc);
        gbc.gridx = 1;
        mainPanel.add(txtDatabase, gbc);

        JLabel lblUser = new JLabel("Usuario:");
        txtUser = new JTextField(20);
        txtUser.setEditable(true);
        gbc.gridx = 0; gbc.gridy = 3;
        mainPanel.add(lblUser, gbc);
        gbc.gridx = 1;
        mainPanel.add(txtUser, gbc);

        JLabel lblPassword = new JLabel("Contraseña:");
        txtPassword = new JPasswordField(20);
        txtPassword.setEditable(true);
        gbc.gridx = 0; gbc.gridy = 4;
        mainPanel.add(lblPassword, gbc);
        gbc.gridx = 1;
        mainPanel.add(txtPassword, gbc);

        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.RIGHT));
        JButton btnProbar = new JButton("Probar conexión");
        btnProbar.addActionListener(this::probarConexion);
        JButton btnGuardar = new JButton("Guardar");
        btnGuardar.addActionListener(this::guardarConfiguracion);
        JButton btnCancelar = new JButton("Cancelar");
        btnCancelar.addActionListener(e -> dispose());
        
        // Aumentar tamaño de fuente de los botones
        UIScaler.scaleFont(btnGuardar);
        UIScaler.scaleFont(btnCancelar);
        panelBotones.add(btnProbar);
        panelBotones.add(btnGuardar);
        panelBotones.add(btnCancelar);

        add(mainPanel, BorderLayout.CENTER);
        add(panelBotones, BorderLayout.SOUTH);
        pack();
        setLocationRelativeTo(getParent());
    }

    private void cargarConfiguracion() {
        boolean loadedFromDB = false;
        try {
            // Leer primero de la base de datos si existe registro
            String host = prefs.get("db_host", "localhost");
            String port = prefs.get("db_port", "3306");
            String db = prefs.get("db_name", "baru_summer_club");
            String user = prefs.get("db_user", "root");
            String pass = prefs.get("db_pass", "");
            String url = "jdbc:mysql://" + host + ":" + port + "/" + db;
            Class.forName("com.mysql.cj.jdbc.Driver");
            java.sql.Connection conn = java.sql.DriverManager.getConnection(url, user, pass);
            java.sql.Statement st = conn.createStatement();
            // Crear la tabla si no existe antes de consultar
            String sqlConfigTable = "CREATE TABLE IF NOT EXISTS config_db_connection (" +
                "id INT PRIMARY KEY AUTO_INCREMENT, " +
                "host VARCHAR(255), " +
                "port VARCHAR(255), " +
                "db_name VARCHAR(255), " +
                "db_user VARCHAR(255), " +
                "db_pass VARCHAR(255)" +
                ")";
            st.executeUpdate(sqlConfigTable);
            java.sql.ResultSet rs = st.executeQuery("SELECT * FROM config_db_connection LIMIT 1");
            if (rs.next()) {
                txtHost.setText(rs.getString("host"));
                txtPort.setText(rs.getString("port"));
                txtDatabase.setText(rs.getString("db_name"));
                txtUser.setText(rs.getString("db_user"));
                txtPassword.setText(rs.getString("db_pass"));
                loadedFromDB = true;
            }
            rs.close();
            st.close();
            conn.close();
        } catch (Exception ex) {
            // Si hay error o no hay registro, cargar de preferencias
        }
        if (!loadedFromDB) {
            txtHost.setText(prefs.get("db_host", "localhost"));
            txtPort.setText(prefs.get("db_port", "3306"));
            txtDatabase.setText(prefs.get("db_name", "baru_summer_club"));
            txtUser.setText(prefs.get("db_user", "root"));
            txtPassword.setText(prefs.get("db_pass", ""));
        }
    }

    private void guardarConfiguracion(ActionEvent e) {
        String host = txtHost.getText().trim();
        String port = txtPort.getText().trim();
        String db = txtDatabase.getText().trim();
        String user = txtUser.getText().trim();
        String pass = new String(txtPassword.getPassword());
        prefs.put("db_host", host);
        prefs.put("db_port", port);
        prefs.put("db_name", db);
        prefs.put("db_user", user);
        prefs.put("db_pass", pass);

        // Guardar también en la base de datos
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            String url = "jdbc:mysql://" + host + ":" + port + "/" + db;
            java.sql.Connection conn = java.sql.DriverManager.getConnection(url, user, pass);
            // Si ya existe un registro, actualiza el primero. Si no, inserta uno nuevo.
            java.sql.Statement st = conn.createStatement();
            java.sql.ResultSet rs = st.executeQuery("SELECT id FROM config_db_connection LIMIT 1");
            if (rs.next()) {
                int id = rs.getInt("id");
                String update = "UPDATE config_db_connection SET host='" + host + "', port='" + port + "', db_name='" + db + "', db_user='" + user + "', db_pass='" + pass + "' WHERE id=" + id;
                st.executeUpdate(update);
            } else {
                String insert = "INSERT INTO config_db_connection (host, port, db_name, db_user, db_pass) VALUES ('" + host + "', '" + port + "', '" + db + "', '" + user + "', '" + pass + "')";
                st.executeUpdate(insert);
            }
            rs.close();
            st.close();
            conn.close();
        } catch (Exception ex) {
            // Si hay error, solo muestra advertencia pero sigue guardando en preferencias
            JOptionPane.showMessageDialog(this, "No se pudo guardar la configuración en la tabla config_db_connection: " + ex.getMessage(), "Advertencia", JOptionPane.WARNING_MESSAGE);
        }
        JOptionPane.showMessageDialog(this, "Configuración de base de datos guardada correctamente.", "Base de Datos", JOptionPane.INFORMATION_MESSAGE);
        dispose();
    }

    private void probarConexion(ActionEvent e) {
        String host = txtHost.getText().trim();
        String port = txtPort.getText().trim();
        String db = txtDatabase.getText().trim();
        String user = txtUser.getText().trim();
        String pass = new String(txtPassword.getPassword());
        String url = "jdbc:mysql://" + host + ":" + port + "/" + db;
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
            java.sql.Connection conn = java.sql.DriverManager.getConnection(url, user, pass);
            conn.close();
            JOptionPane.showMessageDialog(this, "¡Conexión exitosa!", "Prueba de conexión", JOptionPane.INFORMATION_MESSAGE);
        } catch (Exception ex) {
            JOptionPane.showMessageDialog(this, "Error de conexión: " + ex.getMessage(), "Prueba de conexión", JOptionPane.ERROR_MESSAGE);
        }
    }
    

}
