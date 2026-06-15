package com.mycompany.tikets.util;

import com.mycompany.tikets.model.ConfiguracionTikets;
import com.mycompany.tikets.model.Ticket;
import java.awt.Desktop;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import javax.swing.JOptionPane;

// Importaciones de iText para PDFs
import com.itextpdf.text.*;
import com.itextpdf.text.pdf.*;
import com.itextpdf.text.pdf.draw.LineSeparator;
import java.nio.file.Files;
import java.nio.file.Paths;

/**
 * Clase de utilidad para generar tickets en formato PDF
 */
public class TicketPDFGenerator {
    
    /**
     * Genera un archivo PDF con el ticket
     * @param ticket Ticket a generar
     * @param esVale Indica si es un vale o un ticket normal
     * @return Ruta del archivo generado o null si hay error
     */
    public static String generarPDF(Ticket ticket, boolean esVale) {
        // Crear directorio para tickets si no existe
        String outPath = "tickets";
        File dir = new File(outPath);
        if (!dir.exists()) {
            dir.mkdirs();
        }
        
        // Nombre del archivo PDF
        String fileName = outPath + File.separator + "ticket_" + ticket.getId() + 
                (esVale ? "_vale_" : "_entrada_") + System.currentTimeMillis() + ".pdf";
        
        // Crear documento PDF
        Document document = new Document(PageSize.A4);
        // Usar un ancho más estrecho para simular un ticket térmico
        document.setPageSize(new Rectangle(210, 600)); // ~ 7.4 x 21.1 cm
        document.setMargins(10, 10, 10, 10);
        
        try {
            PdfWriter writer = PdfWriter.getInstance(document, new FileOutputStream(fileName));
            document.open();
            
            // Cargar configuración
            ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
            
            // Crear fuentes
            BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.EMBEDDED);
            Font titleFont = new Font(bf, 16, Font.BOLD);
            Font boldFont = new Font(bf, 12, Font.BOLD);
            Font normalFont = new Font(bf, 10, Font.NORMAL);
            Font smallFont = new Font(bf, 8, Font.NORMAL);
            
            // Añadir logo si existe
            try {
                String logoPath = config.getRutaLogo();
                if (logoPath != null && !logoPath.isEmpty() && Files.exists(Paths.get(logoPath))) {
                    Image logo = Image.getInstance(logoPath);
                    // Ajustar tamaño del logo
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
            } catch (Exception e) {
                System.err.println("Error al cargar el logo: " + e.getMessage());
                // Añadir título
                Paragraph title = new Paragraph("BARU SUMMER CLUB", titleFont);
                title.setAlignment(Element.ALIGN_CENTER);
                document.add(title);
            }
            
            // Añadir información del establecimiento
            document.add(new Paragraph(" ")); // Espacio
            Paragraph infoClub = new Paragraph(config.getNombreClub(), normalFont);
            infoClub.setAlignment(Element.ALIGN_CENTER);
            document.add(infoClub);
            
            Paragraph infoCIF = new Paragraph("CIF " + config.getCif(), normalFont);
            infoCIF.setAlignment(Element.ALIGN_CENTER);
            document.add(infoCIF);
            
            Paragraph infoDireccion1 = new Paragraph(config.getDireccion1(), normalFont);
            infoDireccion1.setAlignment(Element.ALIGN_CENTER);
            document.add(infoDireccion1);
            
            Paragraph infoDireccion2 = new Paragraph(config.getDireccion2(), normalFont);
            infoDireccion2.setAlignment(Element.ALIGN_CENTER);
            document.add(infoDireccion2);
            
            // Línea separadora
            document.add(new Paragraph(" ")); // Espacio
            LineSeparator lineSeparator = new LineSeparator();
            lineSeparator.setLineWidth(1);
            lineSeparator.setPercentage(70);
            lineSeparator.setAlignment(Element.ALIGN_CENTER);
            document.add(lineSeparator);
            document.add(new Paragraph(" ")); // Espacio
            if (ticket.getCondicionesConsumicion() != null && !ticket.getCondicionesConsumicion().isEmpty()) {
                document.add(new Paragraph("Condiciones de consumición: " + ticket.getCondicionesConsumicion(), normalFont));
            }
            
            document.add(new Paragraph(" ")); // Espacio
            
            // Icono (solo mencionamos el nombre del archivo)
            if (ticket.getIcono() != null && !ticket.getIcono().isEmpty()) {
                document.add(new Paragraph("Icono: " + ticket.getIcono(), normalFont));
                document.add(new Paragraph(" ")); // Espacio
            }
            
            // Pie de página
            document.add(new Paragraph("===================================", normalFont));
            document.add(new Paragraph("Gracias por su visita.", smallFont));
            document.add(new Paragraph("Este ticket debe presentarse a la entrada.", smallFont));
            document.add(new Paragraph("===================================", normalFont));
            
            document.close();
            return fileName;
            
        } catch (Exception e) {
            System.err.println("Error al generar archivo de ticket: " + e.getMessage());
            e.printStackTrace();
            return null;
        }
    }
}
