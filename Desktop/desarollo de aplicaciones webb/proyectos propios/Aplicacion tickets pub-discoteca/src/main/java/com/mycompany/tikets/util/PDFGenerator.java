package com.mycompany.tikets.util;

import com.itextpdf.text.*;
import com.itextpdf.text.pdf.BaseFont;
import com.itextpdf.text.pdf.PdfWriter;
import com.mycompany.tikets.model.Ticket;
import com.mycompany.tikets.model.ConfiguracionTikets;
import java.io.File;
import java.io.FileOutputStream;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.text.SimpleDateFormat;
import java.util.Date;

/**
 * Clase utilitaria para generar archivos PDF de tickets
 */
public class PDFGenerator {
    
    /**
     * Genera un archivo PDF para el ticket proporcionado
     * @param ticket El ticket para el que se generará el PDF
     * @param parentComponent Componente padre para mostrar diálogos (puede ser null)
     * @return true si la generación fue exitosa, false en caso contrario
     */
    public static boolean generarPDF(Ticket ticket, ConfiguracionTikets config) {
        Document document = null;
        FileOutputStream fos = null;
        
        try {
            // Crear el documento PDF
            document = new Document();
            String fileName = "ticket_" + ticket.getId() + ".pdf";
            File file = new File(fileName);
            
            // Configurar el documento con tamaño de ticket y márgenes
            document.setPageSize(new Rectangle(210, 600));
            document.setMargins(10, 10, 10, 10);
            
            // Crear el escritor PDF
            fos = new FileOutputStream(file);
            PdfWriter.getInstance(document, fos);
            document.open();
            
            // Obtener configuración
            ConfiguracionTikets configTikets = ConfiguracionTikets.getInstancia();
            
            // Configurar fuentes
            BaseFont bf = BaseFont.createFont(
                BaseFont.HELVETICA, 
                BaseFont.CP1252, 
                BaseFont.EMBEDDED);
            Font titleFont = new Font(bf, 16, Font.BOLD);
            Font boldFont = new Font(bf, 12, Font.BOLD);
            Font normalFont = new Font(bf, 10);
            Font smallFont = new Font(bf, 8);
            
            // Añadir logo si existe
            try {
                String logoPath = configTikets.getRutaLogo();
                if (logoPath != null && !logoPath.isEmpty() && Files.exists(Paths.get(logoPath))) {
                    Image logo = Image.getInstance(logoPath);
                    float logoWidth = 80;
                    float ratio = logoWidth / logo.getWidth();
                    logo.scaleToFit(logoWidth, logo.getHeight() * ratio);
                    logo.setAlignment(Element.ALIGN_CENTER);
                    document.add(logo);
                } else {
                    // Sin logo, añadir título
                    Paragraph title = new Paragraph("BARU SUMMER CLUB", titleFont);
                    title.setAlignment(Element.ALIGN_CENTER);
                    document.add(title);
                }
            } catch (Exception ex) {
                System.err.println("Error al cargar el logo: " + ex.getMessage());
                Paragraph title = new Paragraph("BARU SUMMER CLUB", titleFont);
                title.setAlignment(Element.ALIGN_CENTER);
                document.add(title);
            }
            
            // Añadir información del establecimiento
            addEmptyLine(document);
            addCenteredText(document, configTikets.getNombreClub(), normalFont);
            
            // Añadir información de CIF si existe
            if (configTikets.getCif() != null && !configTikets.getCif().trim().isEmpty()) {
                addCenteredText(document, "CIF: " + configTikets.getCif(), normalFont);
            }
            
            // Añadir dirección si existe
            if (configTikets.getDireccion1() != null && !configTikets.getDireccion1().trim().isEmpty()) {
                addCenteredText(document, configTikets.getDireccion1(), normalFont);
            }
            
            if (configTikets.getDireccion2() != null && !configTikets.getDireccion2().trim().isEmpty()) {
                addCenteredText(document, configTikets.getDireccion2(), normalFont);
            }
            
            // Línea separadora
            addSeparator(document, normalFont);
            
            // Información del ticket
            addCenteredText(document, "TICKET #" + ticket.getId(), boldFont);
            
            // Fecha y hora
            SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy HH:mm:ss");
            String fechaHoraStr = ticket.getFechaCreacion() != null ? 
                sdf.format(ticket.getFechaCreacion()) : 
                sdf.format(new Date());
            addCenteredText(document, fechaHoraStr, normalFont);
            addEmptyLine(document);
            
            // Tipo de consumición
            String tipoConsumicion = getTipoConsumicionString(ticket.getTipoConsumicion());
            addCenteredText(document, "Tipo: " + tipoConsumicion, normalFont);
            
            // Precio si es visible
            if (ticket.isMostrarPrecio() && ticket.getPrecio() != null) {
                addCenteredText(document, 
                    "Precio: " + String.format("%.2f", ticket.getPrecio()) + " €", 
                    boldFont);
            }
            
            // Frase del día si existe
            if (ticket.getFraseDelDia() != null && !ticket.getFraseDelDia().trim().isEmpty()) {
                addEmptyLine(document);
                addCenteredText(document, "\"" + ticket.getFraseDelDia() + "\"", normalFont);
            }
            
            // Condiciones de entrada si existen
            if (ticket.getCondicionesEntrada() != null && !ticket.getCondicionesEntrada().isEmpty()) {
                addEmptyLine(document);
                addCenteredText(document, ticket.getCondicionesEntrada(), smallFont);
            }
            
            // Condiciones de consumición si existen
            if (ticket.getCondicionesConsumicion() != null && !ticket.getCondicionesConsumicion().isEmpty()) {
                addEmptyLine(document);
                addCenteredText(document, ticket.getCondicionesConsumicion(), smallFont);
            }
            
            // Cerrar el documento
            document.close();
            document = null;
            
            // Mostrar mensaje de éxito
            String message = "Se ha generado el PDF correctamente.\n";
            
            // Intentar abrir el PDF automáticamente
            if (java.awt.Desktop.isDesktopSupported()) {
                try {
                    java.awt.Desktop.getDesktop().open(file);
                    message += "El archivo se ha abierto automáticamente.";
                } catch (Exception ex) {
                    message += "No se pudo abrir automáticamente.\n";
                    message += "Ubicación: " + file.getAbsolutePath();
                }
            } else {
                message += "Ubicación: " + file.getAbsolutePath();
            }
            
            // Mostrar mensaje en consola ya que no tenemos un componente padre
            System.out.println("PDF Generado: " + message);
            
            return true;
            
        } catch (Exception ex) {
            String errorMsg = "Error al generar el PDF: " + ex.getMessage();
            System.err.println(errorMsg);
            ex.printStackTrace();
            
            // Mostrar error en consola ya que no tenemos un componente padre
            System.err.println(errorMsg);
            return false;
            
        } finally {
            // Asegurarse de cerrar el documento
            if (document != null && document.isOpen()) {
                try {
                    document.close();
                } catch (Exception e) {
                    System.err.println("Error al cerrar el documento: " + e.getMessage());
                }
            }
            // Cerrar el FileOutputStream
            if (fos != null) {
                try {
                    fos.close();
                } catch (java.io.IOException ex) {
                    System.err.println("Error al cerrar el archivo: " + ex.getMessage());
                }
            }
        }
    }
    
    /**
     * Obtiene el nombre del tipo de consumición
     */
    private static String getTipoConsumicionString(Ticket.TipoConsumicion tipo) {
        if (tipo == null) return "Entrada";
        
        switch (tipo) {
            case COPA: return "Copa";
            case CERVEZAS: return "Cerveza";
            case SOLO_TICKET: return "Solo Ticket";
            case SIN_CONSUMICION: 
            default: return "Entrada";
        }
    }
    
    /**
     * Añade una línea de texto centrada al documento
     */
    private static void addCenteredText(Document doc, String text, Font font) 
            throws DocumentException {
        if (text == null || text.trim().isEmpty()) return;
        
        Paragraph p = new Paragraph(text, font);
        p.setAlignment(Element.ALIGN_CENTER);
        doc.add(p);
    }
    
    /**
     * Añade una línea en blanco al documento
     */
    private static void addEmptyLine(Document doc) throws DocumentException {
        doc.add(new Paragraph(" "));
    }
    
    /**
     * Añade una línea separadora al documento
     */
    private static void addSeparator(Document doc, Font font) 
            throws DocumentException {
        addEmptyLine(doc);
        Paragraph separator = new Paragraph("--------------------------------", font);
        separator.setAlignment(Element.ALIGN_CENTER);
        doc.add(separator);
        addEmptyLine(doc);
    }
}
