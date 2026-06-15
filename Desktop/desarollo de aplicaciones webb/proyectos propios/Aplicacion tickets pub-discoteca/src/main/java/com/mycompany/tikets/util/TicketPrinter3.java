package com.mycompany.tikets.util;

import com.mycompany.tikets.model.Ticket;
import java.awt.Font;
import java.awt.Graphics;
import java.awt.Graphics2D;
import java.awt.print.PageFormat;
import java.awt.print.Paper;
import java.awt.print.Printable;
import java.awt.print.PrinterException;
import java.awt.print.PrinterJob;
import java.text.SimpleDateFormat;
import java.util.Date;
import javax.print.PrintService;
import javax.print.PrintServiceLookup;
import javax.print.attribute.HashPrintRequestAttributeSet;
import javax.print.attribute.PrintRequestAttributeSet;
import javax.print.attribute.standard.MediaPrintableArea;
import javax.print.attribute.standard.OrientationRequested;

/**
 * Clase simplificada para imprimir tickets en impresoras térmicas
 */
public class TicketPrinter3 implements Printable {
    
    private final Ticket ticket;
    private final boolean esVale;
    
    public TicketPrinter3(Ticket ticket, boolean esVale) {
        this.ticket = ticket;
        this.esVale = esVale;
    }
    
    /**
     * Intenta imprimir el ticket en la impresora predeterminada
     */
    public boolean imprimir() {
        System.out.println("\n=== INICIANDO IMPRESIÓN DE TICKET ===");
        
        try {
            // Verificar que el ticket no sea nulo
            if (ticket == null) {
                System.err.println("ERROR: El ticket es nulo");
                return false;
            }
            
            // Obtener el servicio de impresión
            PrinterJob job = PrinterJob.getPrinterJob();
            job.setJobName("Ticket #" + ticket.getId() + (esVale ? " (Vale)" : ""));
            
            // Configurar el trabajo de impresión
            job.setPrintable(this);
            
            // Obtener la impresora predeterminada
            PrintService defaultPrinter = PrintServiceLookup.lookupDefaultPrintService();
            if (defaultPrinter == null) {
                System.err.println("ERROR: No se encontró ninguna impresora predeterminada");
                return false;
            }
            
            System.out.println("Impresora seleccionada: " + defaultPrinter.getName());
            job.setPrintService(defaultPrinter);
            
            // Configurar el formato de página para ticket (80mm de ancho)
            PageFormat pf = job.defaultPage();
            Paper paper = new Paper();
            
            // Tamaño de papel para ticket (80mm x 297mm)
            double width = 80 * 2.83465; // Convertir mm a puntos (1mm = 2.83465 puntos)
            double height = 297 * 2.83465;
            paper.setSize(width, height);
            
            // Márgenes muy pequeños para impresora térmica
            double margin = 5; // 5 puntos de margen
            paper.setImageableArea(
                margin, 
                margin, 
                Math.max(1, width - (2 * margin)),
                Math.max(1, height - (2 * margin))
            );
            
            pf.setPaper(paper);
            pf.setOrientation(PageFormat.PORTRAIT);
            
            // Configurar atributos de impresión
            PrintRequestAttributeSet attributes = new HashPrintRequestAttributeSet();
            attributes.add(OrientationRequested.PORTRAIT);
            
            // Intentar imprimir
            System.out.println("Enviando trabajo de impresión...");
            job.print(attributes);
            System.out.println("Trabajo de impresión enviado correctamente");
            return true;
            
        } catch (Exception e) {
            System.err.println("ERROR al imprimir: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }
    
    @Override
    public int print(Graphics graphics, PageFormat pageFormat, int pageIndex) throws PrinterException {
        if (pageIndex > 0) {
            return NO_SUCH_PAGE;
        }
        
        Graphics2D g2d = (Graphics2D) graphics;
        
        // Configuración de fuentes
        Font fontTitulo = new Font("Arial", Font.BOLD, 14);
        Font fontNormal = new Font("Arial", Font.PLAIN, 10);
        Font fontNegrita = new Font("Arial", Font.BOLD, 10);
        
        // Posición inicial
        int x = (int) pageFormat.getImageableX() + 10;
        int y = (int) pageFormat.getImageableY() + 20;
        int ancho = (int) pageFormat.getImageableWidth() - 20;
        
        // Dibujar contenido del ticket
        g2d.setFont(fontTitulo);
        centrarTexto(g2d, "BARU SUMMER CLUB", ancho, x, y);
        y += 20;
        
        g2d.setFont(fontNormal);
        centrarTexto(g2d, "C/ Ejemplo, 123", ancho, x, y);
        y += 15;
        centrarTexto(g2d, "Tel: 123 456 789", ancho, x, y);
        y += 20;
        
        // Línea divisoria
        g2d.drawLine(x, y, x + ancho, y);
        y += 15;
        
        // Información del ticket
        g2d.setFont(fontNegrita);
        centrarTexto(g2d, "TICKET #" + ticket.getId(), ancho, x, y);
        y += 20;
        
        g2d.setFont(fontNormal);
        SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy HH:mm:ss");
        centrarTexto(g2d, "Fecha: " + sdf.format(new Date()), ancho, x, y);
        y += 20;
        
        // Detalles del ticket
        g2d.setFont(fontNegrita);
        centrarTexto(g2d, "Tipo: " + ticket.getTipoConsumicion(), ancho, x, y);
        y += 20;
        
        if (ticket.isMostrarPrecio() && ticket.getPrecio() != null) {
            g2d.setFont(fontNegrita);
            centrarTexto(g2d, "Precio: " + ticket.getPrecio() + " €", ancho, x, y);
            y += 20;
        }
        
        // Línea divisoria final
        y += 10;
        g2d.drawLine(x, y, x + ancho, y);
        y += 15;
        
        // Mensaje de agradecimiento
        g2d.setFont(fontNormal);
        centrarTexto(g2d, "¡Gracias por su visita!", ancho, x, y);
        y += 15;
        centrarTexto(g2d, "www.barusummerclub.com", ancho, x, y);
        
        return PAGE_EXISTS;
    }
    
    /**
     * Método auxiliar para centrar texto
     */
    private void centrarTexto(Graphics2D g2d, String texto, int ancho, int x, int y) {
        int stringLen = (int) g2d.getFontMetrics().getStringBounds(texto, g2d).getWidth();
        int startX = x + (ancho - stringLen) / 2;
        g2d.drawString(texto, startX, y);
    }
    
    /**
     * Método estático para imprimir un ticket directamente
     */
    public static boolean imprimirTicket(Ticket ticket, boolean esVale) {
        return new TicketPrinter3(ticket, esVale).imprimir();
    }
}
