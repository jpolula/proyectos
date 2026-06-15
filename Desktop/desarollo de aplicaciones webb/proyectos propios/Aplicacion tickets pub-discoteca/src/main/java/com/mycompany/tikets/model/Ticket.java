package com.mycompany.tikets.model;

import java.math.BigDecimal;
import java.util.Date;

/**
 * Clase modelo para representar un ticket
 */
public class Ticket {
    
    public enum TipoConsumicion {
        COPA,
        CERVEZAS,
        SIN_CONSUMICION,
        SOLO_TICKET  // Nuevo tipo que solo imprime ticket sin bono
    }
    
    private int id;
    private TipoConsumicion tipoConsumicion;
    private int cantidad;
    private BigDecimal precio;
    private String fraseDelDia;
    private String condicionesEntrada;
    private String condicionesConsumicion;
    private String icono;
    private Date fechaCreacion;
    private boolean mostrarPrecio = true; // Por defecto se muestra el precio
    
    // Constructor
    public Ticket() {
        this.cantidad = 1;
        this.fechaCreacion = new Date();
    }
    
    // Constructor completo
    public Ticket(int id, TipoConsumicion tipoConsumicion, int cantidad, BigDecimal precio, 
            String fraseDelDia, String condicionesEntrada, String condicionesConsumicion, 
            String icono, Date fechaCreacion) {
        this.id = id;
        this.tipoConsumicion = tipoConsumicion;
        this.cantidad = cantidad;
        this.precio = precio;
        this.fraseDelDia = fraseDelDia;
        this.condicionesEntrada = condicionesEntrada;
        this.condicionesConsumicion = condicionesConsumicion;
        this.icono = icono;
        this.fechaCreacion = fechaCreacion;
    }
    
    // Getters y Setters
    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public TipoConsumicion getTipoConsumicion() {
        return tipoConsumicion;
    }

    public void setTipoConsumicion(TipoConsumicion tipoConsumicion) {
        this.tipoConsumicion = tipoConsumicion;
    }

    public int getCantidad() {
        return cantidad;
    }

    public void setCantidad(int cantidad) {
        this.cantidad = cantidad;
    }

    public BigDecimal getPrecio() {
        // Nunca devolver un precio nulo
        if (precio == null) {
            return new BigDecimal("0.00");
        }
        return precio;
    }

    public void setPrecio(BigDecimal precio) {
        this.precio = precio;
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

    public String getIcono() {
        return icono;
    }

    public void setIcono(String icono) {
        this.icono = icono;
    }

    public Date getFechaCreacion() {
        return fechaCreacion;
    }

    public void setFechaCreacion(Date fechaCreacion) {
        this.fechaCreacion = fechaCreacion;
    }
    
    public boolean isMostrarPrecio() {
        return mostrarPrecio;
    }

    public void setMostrarPrecio(boolean mostrarPrecio) {
        this.mostrarPrecio = mostrarPrecio;
    }
    
    @Override
    public String toString() {
        return "Ticket #" + id + " - " + tipoConsumicion + " - Cantidad: " + cantidad;
    }
}
