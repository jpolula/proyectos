/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 */

package com.mycompany.tikets;

import com.mycompany.tikets.util.DatabaseConnection;
import com.mycompany.tikets.view.MainWindow;
import java.sql.Connection;
import java.sql.SQLException;
import java.awt.Font;
import javax.swing.JOptionPane;
import javax.swing.SwingUtilities;
import javax.swing.UIManager;
import javax.swing.UnsupportedLookAndFeelException;
import javax.swing.plaf.FontUIResource;

/**
 * Clase principal de la aplicación
 */
public class Tikets {

    /**
     * Aumenta el tamaño de fuente de todos los componentes de la interfaz
     */
    private static void setGlobalFontSize(float escala) {
        UIManager.put("OptionPane.messageFont", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("OptionPane.buttonFont", new FontUIResource(new Font("Dialog", Font.BOLD, (int)(12 * escala))));
        UIManager.put("TextField.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("Label.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("Button.font", new FontUIResource(new Font("Dialog", Font.BOLD, (int)(12 * escala))));
        UIManager.put("ComboBox.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("CheckBox.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("RadioButton.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("TabbedPane.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("List.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("Table.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("TableHeader.font", new FontUIResource(new Font("Dialog", Font.BOLD, (int)(12 * escala))));
        UIManager.put("MenuBar.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("Menu.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("MenuItem.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
        UIManager.put("PasswordField.font", new FontUIResource(new Font("Dialog", Font.PLAIN, (int)(12 * escala))));
    }
    
    public static void main(String[] args) {
        try {
            // Establecer el Look and Feel del sistema
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
            
            // Aumentar el tamaño de fuente global (1.5 = 50% más grande)
            setGlobalFontSize(1.5f);
            
            // Inicializar la base de datos
            try {
                // Intentar obtener una conexión para verificar que la base de datos está accesible
                Connection conn = DatabaseConnection.getConnection();
                if (conn != null) {
                    System.out.println("Conexión a la base de datos establecida correctamente.");
                    conn.close(); // Cerrar la conexión ya que solo estamos verificando
                }
            } catch (SQLException e) {
                System.err.println("Error al conectar a la base de datos: " + e.getMessage());
                JOptionPane.showMessageDialog(null, 
                        "No se pudo conectar a la base de datos. La aplicación funcionará en modo DEMO.\n" + e.getMessage(), 
                        "Error de conexión", 
                        JOptionPane.WARNING_MESSAGE);
            }
            
            // Iniciar la aplicación
            SwingUtilities.invokeLater(() -> {
                MainWindow mainWindow = new MainWindow();
                mainWindow.setVisible(true);
            });
            
        } catch (ClassNotFoundException | InstantiationException | IllegalAccessException | UnsupportedLookAndFeelException e) {
            e.printStackTrace();
        }
    }
}
