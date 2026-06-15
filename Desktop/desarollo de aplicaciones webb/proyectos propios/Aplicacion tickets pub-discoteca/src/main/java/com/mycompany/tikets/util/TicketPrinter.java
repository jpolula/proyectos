package com.mycompany.tikets.util;

import com.mycompany.tikets.model.Ticket;
import java.awt.BorderLayout;
import javax.swing.JDialog;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JOptionPane;
import javax.swing.SwingUtilities;
import java.awt.Font;
import java.awt.Graphics;
import java.awt.Graphics2D;
import java.awt.print.PageFormat;
import java.awt.print.Paper;
import java.awt.print.Printable;
import java.awt.print.PrinterException;
import java.awt.print.PrinterJob;
import java.text.SimpleDateFormat;
import javax.print.PrintService;
import javax.print.PrintServiceLookup;
import com.mycompany.tikets.view.ConfiguracionImpresionWindow;
import javax.print.attribute.HashPrintRequestAttributeSet;
import javax.print.attribute.PrintRequestAttributeSet;

/**
 * Clase para manejar la impresión de tickets
 */
public class TicketPrinter implements Printable {
    
    private Ticket ticket;
    private boolean esVale;
    
    /**
     * Constructor para la impresión de tickets
     * @param ticket Ticket a imprimir
     * @param esVale Si es true, imprime un vale/bono simplificado
     */
    public TicketPrinter(Ticket ticket, boolean esVale) {
        this.ticket = ticket;
        this.esVale = esVale;
    }
    
    /**
     * Imprime el ticket seleccionando una impresora
     * @return true si se imprimió correctamente, false en caso contrario
     */
    /**
     * Intenta imprimir el ticket con la impresora configurada
     * @return true si la impresión fue exitosa, false en caso contrario
     */
    public boolean imprimir() {
        System.out.println("=== INICIO DEL PROCESO DE IMPRESIÓN ===");
        
        // Mostrar diálogo de progreso
        JDialog loadingDialog = new JDialog((JFrame)null, "Imprimiendo...", true);
        loadingDialog.setSize(300, 100);
        loadingDialog.setLayout(new BorderLayout());
        loadingDialog.add(new JLabel("Preparando impresión...", JLabel.CENTER), BorderLayout.CENTER);
        loadingDialog.setLocationRelativeTo(null);
        
        // Usar un hilo separado para no bloquear la interfaz
        new Thread(() -> {
            try {
                loadingDialog.setVisible(true);
            } catch (Exception e) {
                e.printStackTrace();
            }
        }).start();
        
        try {
            // Cargar configuración de impresión
            ConfiguracionImpresionWindow.ConfiguracionImpresion config = 
                ConfiguracionImpresionWindow.cargarConfiguracionImpresion();
            
            if (config == null || config.getImpresora() == null || config.getImpresora().isEmpty()) {
                loadingDialog.dispose();
                JOptionPane.showMessageDialog(null, 
                    "<html><b>Error:</b> No se ha configurado ninguna impresora.<br><br>" +
                    "Por favor, configure una impresora en el menú de Configuración > Impresión.</html>", 
                    "Error de impresión", 
                    JOptionPane.ERROR_MESSAGE);
                return false;
            }
            
            // Buscar la impresora configurada
            loadingDialog.getContentPane().removeAll();
            loadingDialog.add(new JLabel("Buscando impresora: " + config.getImpresora(), JLabel.CENTER), BorderLayout.CENTER);
            loadingDialog.revalidate();
            
            PrintService[] printServices = PrintServiceLookup.lookupPrintServices(null, null);
            PrintService selectedPrinter = null;
            
            System.out.println("Buscando impresora configurada: " + config.getImpresora());
            for (PrintService service : printServices) {
                System.out.println("- Impresora disponible: " + service.getName());
                if (service.getName().equals(config.getImpresora())) {
                    selectedPrinter = service;
                    break;
                }
            }
            
            // Si no se encuentra la impresora configurada, intentar con la predeterminada
            if (selectedPrinter == null) {
                System.out.println("La impresora configurada no se encontró, buscando impresora predeterminada...");
                loadingDialog.getContentPane().removeAll();
                loadingDialog.add(new JLabel("Buscando impresora predeterminada...", JLabel.CENTER), BorderLayout.CENTER);
                loadingDialog.revalidate();
                
                selectedPrinter = PrintServiceLookup.lookupDefaultPrintService();
                
                if (selectedPrinter == null && printServices.length > 0) {
                    selectedPrinter = printServices[0];
                    System.out.println("Usando la primera impresora disponible: " + selectedPrinter.getName());
                }
                
                if (selectedPrinter == null) {
                    loadingDialog.dispose();
                    JOptionPane.showMessageDialog(null, 
                        "<html><b>Error:</b> No se encontró ninguna impresora disponible.<br><br>" +
                        "Por favor, asegúrese de que:<br>" +
                        "1. La impresora está encendida y conectada<br>" +
                        "2. Los controladores están instalados correctamente<br>" +
                        "3. La impresora está configurada como predeterminada</html>", 
                        "Error de impresión", 
                        JOptionPane.ERROR_MESSAGE);
                    return false;
                }
                
                System.out.println("Usando impresora alternativa: " + selectedPrinter.getName());
            }
            
            // Configurar el trabajo de impresión
            loadingDialog.getContentPane().removeAll();
            loadingDialog.add(new JLabel("Preparando documento para impresión...", JLabel.CENTER), BorderLayout.CENTER);
            loadingDialog.revalidate();
            
            System.out.println("Configurando trabajo de impresión para: " + selectedPrinter.getName());
            PrinterJob job = PrinterJob.getPrinterJob();
            job.setPrintService(selectedPrinter);
            job.setPrintable(this);
            
            // Configurar el formato de página para un ticket pequeño
            PageFormat pf = job.defaultPage();
            Paper paper = pf.getPaper();
            
            // Tamaño de papel para ticket térmico (80mm x 297mm para rollo estándar)
            double width = 226.8; // 80mm en puntos (72 puntos = 1 pulgada)
            double height = 850.4; // 300mm para asegurar que quepa el contenido
            
            paper.setSize(width, height);
            paper.setImageableArea(5, 5, width - 10, height - 10);
            pf.setPaper(paper);
            
            // Configurar atributos de impresión
            PrintRequestAttributeSet attributes = new HashPrintRequestAttributeSet();
            
            // Mostrar diálogo de impresión (opcional, se puede comentar si no se desea)
            /*
            if (!job.printDialog(attributes)) {
                loadingDialog.dispose();
                System.out.println("Impresión cancelada por el usuario");
                return false;
            }
            */
            
            loadingDialog.getContentPane().removeAll();
            loadingDialog.add(new JLabel("Enviando a la impresora: " + selectedPrinter.getName(), JLabel.CENTER), BorderLayout.CENTER);
            loadingDialog.revalidate();
            
            System.out.println("Enviando trabajo de impresión a " + selectedPrinter.getName() + "...");
            
            // Usar un hilo separado para la impresión real
            final boolean[] resultado = {false};
            Thread printThread = new Thread(() -> {
                try {
                    job.print(attributes);
                    resultado[0] = true;
                    System.out.println("=== TRABAJO DE IMPRESIÓN ENVIADO CON ÉXITO ===");
                } catch (Exception e) {
                    System.err.println("Error al imprimir: " + e.getMessage());
                    e.printStackTrace();
                    
                    // Mostrar error en el hoto de eventos de la interfaz de usuario
                    SwingUtilities.invokeLater(() -> {
                        JOptionPane.showMessageDialog(null, 
                            "<html><b>Error al imprimir el ticket:</b><br>" + 
                            e.getMessage() + "<br><br>" +
                            "<b>Detalles técnicos:</b><br>" +
                            e.getClass().getSimpleName() + " - " + e.getMessage() + "</html>", 
                            "Error de impresión", 
                            JOptionPane.ERROR_MESSAGE);
                    });
                } finally {
                    // Cerrar el diálogo de carga
                    loadingDialog.dispose();
                }
            });
            
            printThread.start();
            
            // Esperar un momento para que el diálogo se actualice
            try { Thread.sleep(500); } catch (InterruptedException e) {}
            
            // Esperar a que termine la impresión (con un tiempo máximo de espera)
            try {
                printThread.join(10000); // Esperar máximo 10 segundos
            } catch (InterruptedException e) {
                System.err.println("La impresión tardó demasiado tiempo");
                printThread.interrupt();
                return false;
            }
            
            return resultado[0];
        } catch (Exception e) {
            loadingDialog.dispose();
            System.err.println("Error en el proceso de impresión: " + e.getMessage());
            e.printStackTrace();
            JOptionPane.showMessageDialog(null, 
                "<html><b>Error en el proceso de impresión:</b><br>" + 
                e.getMessage() + "<br><br>" +
                "<b>Detalles técnicos:</b><br>" +
                e.getClass().getSimpleName() + " - " + e.getMessage() + "</html>", 
                "Error de impresión", 
                JOptionPane.ERROR_MESSAGE);
            return false;
        }
    }
    
    /**
     * Imprime el ticket en una impresora específica
     * @param printerName Nombre de la impresora
     * @return true si se imprimió correctamente, false en caso contrario
     */
    public boolean imprimirEnImpresora(String printerName) {
        try {
            // Configurar el trabajo de impresión
            PrinterJob job = PrinterJob.getPrinterJob();
            job.setPrintable(this);
            
            // Configurar el formato de página para un ticket pequeño
            PageFormat pf = job.defaultPage();
            Paper paper = pf.getPaper();
            
            // Tamaño de papel para ticket térmico (80mm x 150mm)
            double width = 226.8; // 80mm en puntos (72 puntos = 1 pulgada)
            double height = 425.2; // 150mm en puntos
            
            paper.setSize(width, height);
            paper.setImageableArea(10, 10, width - 20, height - 20);
            pf.setPaper(paper);
            
            // Buscar la impresora por nombre
            PrintService[] services = PrinterJob.lookupPrintServices();
            boolean found = false;
            
            for (PrintService service : services) {
                if (service.getName().equalsIgnoreCase(printerName)) {
                    job.setPrintService(service);
                    found = true;
                    break;
                }
            }
            
            if (!found) {
                JOptionPane.showMessageDialog(null, 
                        "Impresora '" + printerName + "' no encontrada.", 
                        "Error de impresión", 
                        JOptionPane.WARNING_MESSAGE);
                return false;
            }
            
            // Imprimir sin mostrar diálogo
            job.print();
            return true;
            
        } catch (PrinterException e) {
            JOptionPane.showMessageDialog(null, 
                    "Error al imprimir: " + e.getMessage(), 
                    "Error de impresión", 
                    JOptionPane.ERROR_MESSAGE);
            return false;
        }
    }
    
    /**
     * Obtiene la lista de impresoras disponibles
     * @return Array con los nombres de las impresoras
     */
    public static String[] getImpresoras() {
        PrintService[] services = PrinterJob.lookupPrintServices();
        String[] impresoras = new String[services.length];
        
        for (int i = 0; i < services.length; i++) {
            impresoras[i] = services[i].getName();
        }
        
        return impresoras;
    }
    
    @Override
    public int print(Graphics graphics, PageFormat pageFormat, int pageIndex) throws PrinterException {
        if (pageIndex > 0) {
            return NO_SUCH_PAGE;
        }
        
        Graphics2D g2d = (Graphics2D) graphics;
        g2d.translate(pageFormat.getImageableX(), pageFormat.getImageableY());
        
        // Escalar para que quepa en el ticket
        double width = pageFormat.getImageableWidth();
        
        // Dibujar el contenido del ticket
        int y = 0; // Reducido de 10 a 0 para eliminar el margen superior
        
        // Logo y encabezado
        g2d.setFont(new Font("Arial", Font.BOLD, 14));
        g2d.drawString("BARU", (int)(width/2) - 20, y + 15);
        y += 15; // Reducido el espaciado después del título
        
        // Dibujar un sol y olas (logo simplificado)
        int centerX = (int)(width/2);
        g2d.fillOval(centerX - 10, y - 15, 20, 20); // Sol
        g2d.drawArc(centerX - 30, y + 10, 60, 10, 0, 180); // Ola 1
        g2d.drawArc(centerX - 30, y + 20, 60, 10, 0, 180); // Ola 2
        y += 40;
        
        // Datos de la empresa (solo para ticket completo)
        if (!esVale) {
            g2d.setFont(new Font("Arial", Font.PLAIN, 8));
            g2d.drawString("Grupo Baru Baza S.L.", 10, y);
            y += 12;
            g2d.drawString("CIF B44958882", 10, y);
            y += 12;
            g2d.drawString("Carretera Benamaurel", 10, y);
            y += 12;
            g2d.drawString("18800, Baza", 10, y);
            y += 12;
            g2d.drawString("Granada", 10, y);
            y += 20;
        }
        
        // Frase del día
        g2d.setFont(new Font("Arial", Font.ITALIC, 10));
        g2d.drawString("Todo ha salido a pedir de Milhouse!!!!", 10, y);
        y += 20;
        
        // Número de venta y fecha
        SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy");
        String fecha = sdf.format(ticket.getFechaCreacion());
        String ticketId = String.format("%05d", ticket.getId()); // Formato de 5 dígitos con ceros a la izquierda
        
        g2d.setFont(new Font("Arial", Font.PLAIN, 10));
        g2d.drawString("TICKET #" + ticketId + "   " + fecha, 10, y);
        y += 12;
        g2d.drawString("REF: " + ticketId, 10, y);
        y += 15; // Reducido el espaciado después de la referencia
        
        // Tipo de ticket (entrada o vale)
        g2d.setFont(new Font("Arial", Font.BOLD, 14));
        if (esVale) {
            g2d.drawString("VALE POR UNA", (int)(width/2) - 50, y);
            y += 20;
            
            // Tipo de consumición
            String tipoConsumicion;
            switch (ticket.getTipoConsumicion()) {
                case COPA:
                    tipoConsumicion = "COPA";
                    break;
                case CERVEZAS:
                    tipoConsumicion = "CERVEZA";
                    break;
                case SIN_CONSUMICION:
                    tipoConsumicion = "ENTRADA";
                    break;
                default:
                    tipoConsumicion = "";
            }
            g2d.drawString(tipoConsumicion, (int)(width/2) - 30, y);
            y += 30;
            
            // Dibujar copas (icono)
            g2d.drawLine((int)(width/2) - 15, y, (int)(width/2) - 5, y - 20); // Copa izquierda
            g2d.drawLine((int)(width/2) - 15, y, (int)(width/2) - 25, y - 20); // Copa izquierda
            g2d.drawArc((int)(width/2) - 25, y - 30, 20, 10, 0, 180); // Copa izquierda
            
            g2d.drawLine((int)(width/2) + 15, y, (int)(width/2) + 5, y - 20); // Copa derecha
            g2d.drawLine((int)(width/2) + 15, y, (int)(width/2) + 25, y - 20); // Copa derecha
            g2d.drawArc((int)(width/2) + 5, y - 30, 20, 10, 0, 180); // Copa derecha
            y += 40;
        } else {
            // Ticket de entrada
            g2d.drawString("ENTRADA GENERAL", (int)(width/2) - 70, y);
            y += 20;
            
            // Precio
            g2d.setFont(new Font("Arial", Font.BOLD, 12));
            g2d.drawString("Precio: " + ticket.getPrecio() + " € iva incl.", 10, y);
            y += 30;
            
            // Dibujar copas (icono)
            g2d.drawLine((int)(width/2) - 5, y, (int)(width/2) - 15, y - 20); // Copa
            g2d.drawLine((int)(width/2) - 5, y, (int)(width/2) + 5, y - 20); // Copa
            g2d.drawArc((int)(width/2) - 15, y - 30, 20, 10, 0, 180); // Copa
            y += 40;
        }
        
        // Condiciones
        g2d.setFont(new Font("Arial", Font.PLAIN, 8));
        g2d.drawString("Condiciones legales:", 10, y);
        y += 12;
        // Comprobar si las condiciones son null antes de dibujarlas
        String condiciones = ticket.getCondicionesEntrada();
        if (condiciones != null) {
            g2d.drawString(condiciones, 10, y);
        } else {
            g2d.drawString("Sin condiciones", 10, y);
        }
        
        return PAGE_EXISTS;
    }
}
