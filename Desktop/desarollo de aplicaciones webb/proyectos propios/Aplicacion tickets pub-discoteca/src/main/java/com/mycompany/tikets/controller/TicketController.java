package com.mycompany.tikets.controller;

import com.mycompany.tikets.model.Ticket;
import com.mycompany.tikets.model.ConfiguracionTikets;
import com.mycompany.tikets.util.DatabaseConnection;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

/**
 * Controlador para gestionar las operaciones relacionadas con tickets
 */
public class TicketController {
    
    /**
     * Guarda un nuevo ticket en la base de datos
     * @param ticket El ticket a guardar
     * @return El ID del ticket guardado o -1 si hay error
     * @throws SQLException Si hay un error de base de datos
     * @throws IllegalArgumentException Si los datos del ticket no son válidos
     */
    public int guardarTicket(Ticket ticket) throws SQLException, IllegalArgumentException {
        // Validar datos del ticket
        if (ticket == null) {
            throw new IllegalArgumentException("El ticket no puede ser nulo");
        }
        
        if (ticket.getCantidad() <= 0) {
            throw new IllegalArgumentException("La cantidad debe ser mayor a cero");
        }
        
        if (ticket.getPrecio() == null || ticket.getPrecio().compareTo(BigDecimal.ZERO) <= 0) {
            throw new IllegalArgumentException("El precio debe ser mayor a cero");
        }
        
        // SQL ajustado a la estructura de la tabla en tikets_db
        // Incluimos el campo cantidad que es obligatorio en la tabla
        String sql = "INSERT INTO tickets (tipo_consumicion, precio, cantidad) VALUES (?, ?, ?)";
        
        try (Connection conn = DatabaseConnection.getConnection()) {
            System.out.println("Conectado a la base de datos para guardar ticket");
            
            // Verificar si la tabla existe
            if (!tablaExiste(conn, "tickets")) {
                System.out.println("La tabla tickets no existe, creándola...");
                crearTablaTickets(conn);
            } else {
                System.out.println("La tabla tickets ya existe");
            }
            
            try (PreparedStatement pstmt = conn.prepareStatement(sql, Statement.RETURN_GENERATED_KEYS)) {
                // Convertir el tipo de consumición al formato correcto (COPA, CERVEZA, etc.)
                String tipoConsumicion = convertirTipoConsumicion(ticket.getTipoConsumicion());
                pstmt.setString(1, tipoConsumicion);
                pstmt.setBigDecimal(2, ticket.getPrecio());
                pstmt.setInt(3, ticket.getCantidad());
                
                System.out.println("Ejecutando SQL: " + sql);
                System.out.println("Tipo consumición: " + ticket.getTipoConsumicion().name());
                System.out.println("Precio: " + ticket.getPrecio());
                
                int affectedRows = pstmt.executeUpdate();
                System.out.println("Filas afectadas: " + affectedRows);
                
                if (affectedRows > 0) {
                    try (ResultSet rs = pstmt.getGeneratedKeys()) {
                        if (rs.next()) {
                            int id = rs.getInt(1);
                            System.out.println("Ticket guardado con ID: " + id);
                            return id;
                        }
                    }
                }
            }
        } catch (SQLException e) {
            System.err.println("Error al guardar ticket: " + e.getMessage());
            e.printStackTrace();
            throw e; // Relanzar para manejar en la capa superior
        }
        
        return -1;
    }
    
    /**
     * Verifica si una tabla existe en la base de datos
     * @param conn Conexión a la base de datos
     * @param nombreTabla Nombre de la tabla a verificar
     * @return true si la tabla existe, false en caso contrario
     */
    private boolean tablaExiste(Connection conn, String nombreTabla) throws SQLException {
        try (ResultSet rs = conn.getMetaData().getTables(null, null, nombreTabla, null)) {
            return rs.next();
        }
    }
    
    /**
     * Crea la tabla de tickets si no existe
     * @param conn Conexión a la base de datos
     */
    private void crearTablaTickets(Connection conn) throws SQLException {
        String sql = "CREATE TABLE IF NOT EXISTS tickets (" +
                   "id INT AUTO_INCREMENT PRIMARY KEY," +
                   "numero_ticket VARCHAR(20) NOT NULL," +
                   "tipo_consumicion ENUM('COPA', 'CERVEZA', 'SIN_CONSUMICION', 'SOLO_TICKET') NOT NULL," +
                   "precio DECIMAL(10,2) NOT NULL," +
                   "cantidad INT NOT NULL," +
                   "fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP," +
                   "impreso BOOLEAN DEFAULT FALSE," +
                   "anulado BOOLEAN DEFAULT FALSE" +
                   ")";
        
        System.out.println("Creando tabla tickets con SQL: " + sql);
        
        try (Statement stmt = conn.createStatement()) {
            stmt.execute(sql);
            System.out.println("Tabla tickets creada correctamente");
        } catch (SQLException e) {
            System.err.println("Error al crear la tabla tickets: " + e.getMessage());
            e.printStackTrace();
            throw e;
        }
    }
    
    /**
     * Obtiene un ticket por su ID
     * @param id ID del ticket a obtener
     * @return El ticket encontrado o null si no existe
     */
    public Ticket obtenerTicketPorId(int id) {
        String sql = "SELECT * FROM tickets WHERE id = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            
            pstmt.setInt(1, id);
            
            try (ResultSet rs = pstmt.executeQuery()) {
                if (rs.next()) {
                    return mapearTicket(rs);
                }
            }
            
        } catch (SQLException e) {
            System.err.println("Error al obtener ticket: " + e.getMessage());
        }
        
        return null; // Return null if no ticket is found or an error occurs
    }
    
    /**
     * Obtiene todos los tickets
     * @return Lista de tickets
     */
    public List<Ticket> obtenerTodosLosTickets() {
        List<Ticket> tickets = new ArrayList<>();
        String sql = "SELECT * FROM tickets ORDER BY fecha_creacion DESC";
        
        try (Connection conn = DatabaseConnection.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {
            
            while (rs.next()) {
                tickets.add(mapearTicket(rs));
            }
            
        } catch (SQLException e) {
            System.err.println("Error al obtener tickets: " + e.getMessage());
        }
        
        return tickets;
    }
    
    /**
     * Obtiene el precio configurado para un tipo de consumición
     * @param tipoConsumicion El tipo de consumición
     * @return El precio configurado
     */
    public BigDecimal obtenerPrecioConsumicion(Ticket.TipoConsumicion tipoConsumicion) {
        String nombreConfig;
        
        switch (tipoConsumicion) {
            case COPA:
                nombreConfig = "PRECIO_COPA";
                break;
            case CERVEZAS:
                nombreConfig = "PRECIO_CERVEZA";
                break;
            case SIN_CONSUMICION:
                nombreConfig = "PRECIO_SIN_CONSUMICION";
                break;
            case SOLO_TICKET:
                nombreConfig = "PRECIO_SOLO_TICKET";
                break;
            default:
                return BigDecimal.ZERO;
        }
        
        String sql = "SELECT valor FROM configuracion WHERE nombre = ?";
        
        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {
            
            pstmt.setString(1, nombreConfig);
            
            try (ResultSet rs = pstmt.executeQuery()) {
                if (rs.next()) {
                    return new BigDecimal(rs.getString("valor"));
                }
            }
            
        } catch (SQLException e) {
            System.err.println("Error al obtener precio: " + e.getMessage());
        }
        
        // Valores por defecto si no hay configuración
        switch (tipoConsumicion) {
            case COPA:
                return new BigDecimal("5.00");
            case CERVEZAS:
                return new BigDecimal("3.50");
            case SIN_CONSUMICION:
                return new BigDecimal("12.50");
            case SOLO_TICKET:
                return new BigDecimal("3.00");
            default:
                return BigDecimal.ZERO;
        }
    }
    
    /**
     * Mapea un ResultSet a un objeto Ticket
     * @param rs ResultSet con datos del ticket
     * @return Objeto Ticket
     * @throws SQLException si hay error al acceder a los datos
     */
    /**
     * Convierte el tipo de consumición del enum de la aplicación al formato de la base de datos
     * @param tipoConsumicion Tipo de consumición en formato enum
     * @return Tipo de consumición en formato string para la base de datos
     */
    private String convertirTipoConsumicion(Ticket.TipoConsumicion tipoConsumicion) {
        switch (tipoConsumicion) {
            case COPA:
                return "COPA";
            case CERVEZAS:
                return "CERVEZA";
            case SIN_CONSUMICION:
                return "SIN_CONSUMICION";
            case SOLO_TICKET:
                return "SOLO_TICKET";
            default:
                return "COPA";
        }
    }
    
    /**
     * Convierte el tipo de consumición de la base de datos al enum de la aplicación
     * @param tipoConsumicionStr Tipo de consumición en formato string de la base de datos
     * @return Tipo de consumición en formato enum
     */
    private Ticket.TipoConsumicion convertirTipoConsumicionAEnum(String tipoConsumicionStr) {
        switch (tipoConsumicionStr.toUpperCase()) {
            case "COPA":
                return Ticket.TipoConsumicion.COPA;
            case "CERVEZA":
                return Ticket.TipoConsumicion.CERVEZAS;
            case "SIN_CONSUMICION":
                return Ticket.TipoConsumicion.SIN_CONSUMICION;
            case "SOLO_TICKET":
                return Ticket.TipoConsumicion.SOLO_TICKET;
            default:
                return Ticket.TipoConsumicion.COPA;
        }
    }
    
    private Ticket mapearTicket(ResultSet rs) throws SQLException {
        Ticket ticket = new Ticket();
        
        // Obtener el ID del ticket de la base de datos
        int id = rs.getInt("id");
        System.out.println("Obteniendo ticket con ID: " + id);
        ticket.setId(id);
        
        // Obtener el tipo de consumición y convertirlo al enum
        String tipoConsumicionStr = rs.getString("tipo_consumicion");
        ticket.setTipoConsumicion(convertirTipoConsumicionAEnum(tipoConsumicionStr));
        
        // Recuperar la cantidad de la base de datos
        try {
            int cantidad = rs.getInt("cantidad");
            // Si la cantidad es 0 o negativa, establecer a 1 por defecto
            ticket.setCantidad(cantidad > 0 ? cantidad : 1);
        } catch (SQLException e) {
            // Si el campo no existe, establecer a 1 por defecto
            ticket.setCantidad(1);
        }
        
        // Cargar la configuración actualizada para precios y otros datos
        ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
        
        // Actualizar el precio según el tipo de consumición y la configuración actual
        BigDecimal precioBD;
        switch (ticket.getTipoConsumicion()) {
            case COPA:
                precioBD = new BigDecimal(config.getPrecioCopa());
                break;
            case CERVEZAS:
                precioBD = new BigDecimal(config.getPrecioCerveza());
                break;
            case SIN_CONSUMICION:
                precioBD = new BigDecimal(config.getPrecioSinConsumicion());
                break;
            case SOLO_TICKET:
                precioBD = new BigDecimal(config.getPrecioSoloTicket());
                break;
            default:
                precioBD = rs.getBigDecimal("precio");
        }
        ticket.setPrecio(precioBD);
        
        // Cargar datos de configuración para el ticket
        ticket.setFraseDelDia(config.getFraseDelDia());
        ticket.setCondicionesEntrada(config.getCondicionesEntrada());
        ticket.setCondicionesConsumicion(config.getCondicionesConsumicion());
        
        // Establecer un icono aleatorio si no hay uno asignado
        ticket.setIcono(""); // Por ahora lo dejamos vacío, se puede implementar la selección de iconos más adelante
        
        // Fecha de creación
        ticket.setFechaCreacion(rs.getTimestamp("fecha_creacion"));
        
        // Cargar configuración de impresión
        ticket.setMostrarPrecio(config.isMostrarPrecio());
        
        // Imprimir información de depuración
        System.out.println("Ticket mapeado - ID: " + ticket.getId() + 
                           ", Tipo: " + ticket.getTipoConsumicion() + 
                           ", Precio actualizado: " + ticket.getPrecio() +
                           ", Frase: " + ticket.getFraseDelDia());
        
        return ticket;
    }
}
