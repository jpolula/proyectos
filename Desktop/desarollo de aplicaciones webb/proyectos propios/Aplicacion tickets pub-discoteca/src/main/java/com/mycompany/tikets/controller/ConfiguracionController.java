package com.mycompany.tikets.controller;

import com.mycompany.tikets.util.DatabaseConnection;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

/**
 * Controlador para gestionar la configuración de la aplicación
 */
public class ConfiguracionController {
    
    /**
     * Obtiene un valor de configuración
     * @param nombre Nombre de la configuración
     * @return Valor de la configuración o null si no existe
     */
    public String obtenerConfiguracion(String nombre) {
        // Convertir a mayúsculas para mantener consistencia
        String nombreConfig = nombre.toUpperCase();
        String sql = "SELECT valor FROM configuracion WHERE UPPER(nombre) = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            
            pstmt.setString(1, nombreConfig);
            
            try (ResultSet rs = pstmt.executeQuery()) {
                if (rs.next()) {
                    return rs.getString("valor");
                }
            }
            
        } catch (SQLException e) {
            System.err.println("Error al obtener configuración '" + nombre + "': " + e.getMessage());
        }
        
        return null;
    }
    
    /**
     * Guarda o actualiza un valor de configuración
     * @param nombre Nombre de la configuración
     * @param valor Valor de la configuración
     * @return true si se guardó correctamente, false en caso contrario
     */
    public boolean guardarConfiguracion(String nombre, String valor) {
        // Convertir a mayúsculas para mantener consistencia
        String nombreConfig = nombre.toUpperCase();
        String sql = "INSERT INTO configuracion (nombre, valor) VALUES (?, ?) " +
                     "ON DUPLICATE KEY UPDATE valor = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            
            pstmt.setString(1, nombreConfig);
            pstmt.setString(2, valor);
            pstmt.setString(3, valor);
            
            int affectedRows = pstmt.executeUpdate();
            return affectedRows > 0;
            
        } catch (SQLException e) {
            System.err.println("Error al guardar configuración: " + e.getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todas las configuraciones
     * @return Mapa con todas las configuraciones
     */
    public Map<String, String> obtenerTodasLasConfiguraciones() {
        Map<String, String> configuraciones = new HashMap<>();
        String sql = "SELECT nombre, valor FROM configuracion";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql);
             ResultSet rs = pstmt.executeQuery()) {
            
            while (rs.next()) {
                configuraciones.put(rs.getString("nombre"), rs.getString("valor"));
            }
            
        } catch (SQLException e) {
            System.err.println("Error al obtener configuraciones: " + e.getMessage());
        }
        
        return configuraciones;
    }
}
