package com.mycompany.tikets.util;

import com.mycompany.tikets.model.ConfiguracionTikets;
import com.mycompany.tikets.model.Ticket;
import java.awt.Color;
import java.awt.Font;
import java.awt.Graphics;
import java.awt.Graphics2D;
import java.awt.geom.AffineTransform;
import java.awt.image.BufferedImage;
import java.awt.print.PageFormat;
import java.awt.print.Paper;
import java.awt.print.Printable;
import java.awt.print.PrinterException;
import java.awt.print.PrinterJob;
import java.io.File;
import java.io.IOException;
import java.text.SimpleDateFormat;
import javax.imageio.ImageIO;
import javax.print.DocFlavor;
import javax.print.PrintService;
import javax.print.PrintServiceLookup;
import javax.print.attribute.HashPrintRequestAttributeSet;
import javax.print.attribute.PrintRequestAttributeSet;
import javax.print.attribute.standard.OrientationRequested;

/**
 * Clase para imprimir tickets en impresoras térmicas
 */
public class TicketPrinter2 implements Printable {

    /**
     * Imprime un ticket en la impresora configurada
     * @param ticketGuardado El ticket a imprimir
     * @param config Configuración de la aplicación
     */
    public static void imprimirTicket(Ticket ticketGuardado, ConfiguracionTikets config) {
        imprimirTicket(ticketGuardado, config, false);
    }
    
    /**
     * Imprime un vale (bono) en la impresora configurada
     * @param ticketGuardado El ticket para el que se genera el vale
     * @param config Configuración de la aplicación
     */
    public static void imprimirVale(Ticket ticketGuardado, ConfiguracionTikets config) {
        imprimirTicket(ticketGuardado, config, true);
    }
    
    /**
     * Método privado para imprimir un ticket o vale
     * @param ticketGuardado El ticket a imprimir
     * @param config Configuración de la aplicación
     * @param esVale Indica si es un vale (true) o un ticket normal (false)
     */
    private static void imprimirTicket(Ticket ticketGuardado, ConfiguracionTikets config, boolean esVale) {
        if (ticketGuardado == null) {
            System.err.println("Error: No se puede imprimir un ticket nulo");
            return;
        }
        
        System.out.println("Preparando para imprimir " + (esVale ? "vale" : "ticket") + " ID: " + ticketGuardado.getId());
        
        try {
            // Obtener la impresora configurada
            String nombreImpresora = config.getImpresoraPredeterminada();
            PrintService[] services = PrintServiceLookup.lookupPrintServices(null, null);
            PrintService impresora = null;
            
            System.out.println("Buscando impresora: " + nombreImpresora);
            System.out.println("Impresoras disponibles:");
            for (PrintService service : services) {
                System.out.println(" - " + service.getName());
                if (service.getName().equalsIgnoreCase(nombreImpresora)) {
                    impresora = service;
                }
            }
            
            if (impresora == null) {
                System.err.println("No se encontró la impresora configurada. Usando impresora predeterminada.");
                impresora = PrintServiceLookup.lookupDefaultPrintService();
                
                if (impresora == null) {
                    System.err.println("No se encontró ninguna impresora predeterminada.");
                    return;
                }
            }
            
            System.out.println("Imprimiendo en: " + impresora.getName());
            
            // Configurar el trabajo de impresión
            PrinterJob job = PrinterJob.getPrinterJob();
            job.setPrintService(impresora);
            
            // Configurar el formato de página para impresora térmica (80mm de ancho)
            PageFormat pf = job.defaultPage();
            Paper paper = new Paper();
            double width = 80 * 2.83; // 80mm a puntos (1mm = 2.83 puntos)
            double height = 297 * 2.83; // Altura A4 por defecto, se ajustará
            paper.setSize(width, height);
            paper.setImageableArea(0, 0, width, height);
            pf.setPaper(paper);
            
            // Crear el contenido del ticket o vale
            TicketPrinter2 ticketPrinter = new TicketPrinter2(ticketGuardado, esVale);
            job.setPrintable(ticketPrinter, pf);
            
            // Imprimir
            job.print();
            System.out.println((esVale ? "Vale" : "Ticket") + " enviado a la impresora correctamente");
            
        } catch (PrinterException e) {
            System.err.println("Error al imprimir el " + (esVale ? "vale" : "ticket") + ": " + e.getMessage());
            e.printStackTrace();
        } catch (Exception e) {
            System.err.println("Error inesperado al imprimir el " + (esVale ? "vale" : "ticket") + ": " + e.getMessage());
            e.printStackTrace();
        }
    }
    
    private Ticket ticket;
    private boolean esVale;
    
    /**
     * Constructor
     * @param ticket Ticket a imprimir
     * @param esVale Indica si es un vale o un ticket normal
     */
    public TicketPrinter2(Ticket ticket, boolean esVale) {
        this.ticket = ticket;
        this.esVale = esVale;
    }
    
    /**
     * Imprime el ticket seleccionando una impresora
     * @return true si se imprimió correctamente, false en caso contrario
     */
    public boolean imprimir() {
        System.out.println("=== INICIANDO IMPRESIÓN DIRECTA ===");
        
        try {
            // Verificar que el ticket no sea nulo
            if (ticket == null) {
                System.err.println("Error: El ticket es nulo");
                return false;
            }
            
            // Configurar el trabajo de impresión
            PrinterJob job = PrinterJob.getPrinterJob();
            
            // Obtener la impresora predeterminada
            PrintService defaultService = PrintServiceLookup.lookupDefaultPrintService();
            if (defaultService == null) {
                System.err.println("Error: No se encontró ninguna impresora predeterminada");
                return false;
            }
            
            System.out.println("=== INFORMACIÓN DE IMPRESIÓN ===");
            System.out.println("Impresora predeterminada: " + defaultService.getName());
            System.out.println("Tipo de impresora: " + defaultService.getSupportedDocFlavors()[0].getMimeType());
            
            // Configurar el formato de página para impresora térmica
            PageFormat pf = job.defaultPage();
            Paper paper = new Paper();
            
            // Tamaño de papel para ticket (80mm x 297mm)
            // 1 mm = 2.83465 puntos (72 dpi / 25.4 mm por pulgada)
            double width = 80 * 2.83465; // ~227 puntos para 80mm
            double height = 297 * 2.83465; // ~842 puntos para 297mm (A4)
            paper.setSize(width, height);
            
            // Márgenes muy pequeños para impresora térmica
            // Las impresoras térmicas suelen tener márgenes mínimos físicos
            double margin = 5; // 5 puntos de margen (~1.76mm)
            paper.setImageableArea(
                margin, 
                margin, 
                Math.max(1, width - (2 * margin)),
                Math.max(1, height - (2 * margin))
            );
            
            pf.setPaper(paper);
            pf.setOrientation(PageFormat.PORTRAIT);
            
            System.out.println("=== CONFIGURACIÓN DE PÁGINA ===");
            System.out.println("Tamaño del papel: " + width + "x" + height + " puntos (" + 
                             (width/2.83465) + "x" + (height/2.83465) + "mm)");
            System.out.println("Área imprimible: " + pf.getImageableX() + "," + pf.getImageableY() + 
                             " " + pf.getImageableWidth() + "x" + pf.getImageableHeight());
            System.out.println("Orientación: " + (pf.getOrientation() == PageFormat.PORTRAIT ? "Vertical" : "Horizontal"));
            
            // Configurar el trabajo de impresión
            job.setPrintable(this, pf);
            job.setJobName("Ticket #" + ticket.getId());
            
            // Configurar atributos de impresión
            PrintRequestAttributeSet attributes = new HashPrintRequestAttributeSet();
            attributes.add(OrientationRequested.PORTRAIT);
            
            // Especificar el tamaño de papel personalizado
            attributes.add(new javax.print.attribute.standard.MediaPrintableArea(
                0, 0, 
                (int)width, (int)height, 
                javax.print.attribute.standard.MediaPrintableArea.MM
            ));
            
            // Establecer la impresora
            job.setPrintService(defaultService);
            
            // Mostrar diálogo de impresión para depuración
            // boolean doPrint = job.printDialog(attributes);
            boolean doPrint = true; // Forzar impresión sin diálogo
            
            if (doPrint) {
                try {
                    System.out.println("Iniciando trabajo de impresión...");
                    job.print(attributes);
                    System.out.println("Trabajo de impresión enviado correctamente a " + defaultService.getName());
                    return true;
                } catch (PrinterException e) {
                    System.err.println("=== ERROR DE IMPRESIÓN ===");
                    System.err.println("Tipo: " + e.getClass().getName());
                    System.err.println("Mensaje: " + e.getMessage());
                    System.err.println("Causa: " + (e.getCause() != null ? e.getCause().getMessage() : "N/A"));
                    e.printStackTrace();
                    
                    // Intentar con la impresora genérica si falla con la predeterminada
                    try {
                        System.out.println("Intentando con impresión genérica...");
                        PrintService[] services = PrintServiceLookup.lookupPrintServices(null, null);
                        if (services.length > 0) {
                            System.out.println("Usando impresora genérica: " + services[0].getName());
                            job.setPrintService(services[0]);
                            job.print(attributes);
                            return true;
                        }
                    } catch (Exception ex) {
                        System.err.println("Error en impresión genérica: " + ex.getMessage());
                    }
                    
                    return false;
                }
            } else {
                System.out.println("Impresión cancelada por el usuario");
                return false;
            }
        } catch (Exception e) {
            System.err.println("=== ERROR INESPERADO ===");
            System.err.println("Tipo: " + e.getClass().getName());
            System.err.println("Mensaje: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }
    
    /**
     * Imprime el ticket en una impresora específica
     * @param printerName Nombre de la impresora
     * @return true si se imprimió correctamente, false en caso contrario
     */
    public boolean imprimirEnImpresora(String printerName) {
        System.out.println("\n=== INICIANDO IMPRESIÓN EN IMPRESORA ESPECÍFICA ===");
        System.out.println("Buscando impresora: " + printerName);
        
        try {
            // Verificar que el ticket no sea nulo
            if (ticket == null) {
                System.err.println("ERROR: El ticket es nulo");
                return false;
            }
            
            // Configurar el trabajo de impresión
            PrinterJob job = PrinterJob.getPrinterJob();
            job.setJobName("Ticket #" + ticket.getId());
            
            // Buscar la impresora por nombre
            System.out.println("\n=== BUSCANDO IMPRESORAS DISPONIBLES ===");
            PrintService[] allServices = PrintServiceLookup.lookupPrintServices(null, null);
            System.out.println("Impresoras encontradas (" + allServices.length + "):");
            for (int i = 0; i < allServices.length; i++) {
                System.out.println((i+1) + ". " + allServices[i].getName() + 
                                 " (" + allServices[i].getDefaultAttributeValue(javax.print.attribute.standard.PrinterName.class) + ")");
                
                // Mostrar más detalles de la impresora
                System.out.println("  - Clase: " + allServices[i].getClass().getName());
                try {
                    javax.print.attribute.AttributeSet attributes = allServices[i].getAttributes();
                    System.out.println("  - Atributos: " + attributes);
                    System.out.println("  - Nombre: " + allServices[i].getName());
                    System.out.println("  - Nombre de la impresora: " + 
                        allServices[i].getDefaultAttributeValue(javax.print.attribute.standard.PrinterName.class));
                } catch (Exception e) {
                    System.out.println("  - No se pudieron obtener todos los atributos: " + e.getMessage());
                }
            }
            
            // Buscar la impresora por nombre exacto o parcial
            PrintService selectedService = null;
            if (printerName != null && !printerName.trim().isEmpty()) {
                System.out.println("\n=== BUSCANDO IMPRESORA ESPECÍFICA ===");
                System.out.println("Buscando impresora que coincida con: " + printerName);
                
                // Primero intentar coincidencia exacta
                for (PrintService service : allServices) {
                    if (service.getName().equalsIgnoreCase(printerName.trim())) {
                        selectedService = service;
                        System.out.println("Coincidencia exacta encontrada: " + service.getName());
                        break;
                    }
                }
                
                // Si no se encontró coincidencia exacta, buscar coincidencia parcial
                if (selectedService == null) {
                    System.out.println("No se encontró coincidencia exacta, buscando coincidencia parcial...");
                    for (PrintService service : allServices) {
                        System.out.println("Comparando con: " + service.getName());
                        if (service.getName().toLowerCase().contains(printerName.trim().toLowerCase())) {
                            selectedService = service;
                            System.out.println("Coincidencia parcial encontrada: " + service.getName());
                            break;
                        }
                    }
                }
                
                if (selectedService == null) {
                    System.err.println("No se encontró ninguna impresora que coincida con: " + printerName);
                    System.out.println("Impresoras disponibles:");
                    for (PrintService service : allServices) {
                        System.out.println("- " + service.getName());
                    }
                    return false;
                } else {
                    System.out.println("\nImpresora seleccionada: " + selectedService.getName());
                }
            }
            
            // Si no se encontró la impresora especificada, usar la predeterminada
            if (selectedService == null) {
                selectedService = PrintServiceLookup.lookupDefaultPrintService();
                if (selectedService != null) {
                    System.out.println("\nUsando impresora predeterminada: " + selectedService.getName());
                } else if (allServices.length > 0) {
                    selectedService = allServices[0];
                    System.out.println("\nUsando primera impresora disponible: " + selectedService.getName());
                }
            }
            
            // Si aún no hay impresora, mostrar error
            if (selectedService == null) {
                System.err.println("\nERROR: No se encontró ninguna impresora disponible");
                return false;
            }
            
            // Mostrar información detallada de la impresora seleccionada
            System.out.println("\n=== INFORMACIÓN DE IMPRESORA ===");
            System.out.println("Nombre: " + selectedService.getName());
            System.out.println("Clase: " + selectedService.getClass().getName());
            
            // Mostrar los formatos soportados
            System.out.println("\nFormatos soportados:");
            for (javax.print.DocFlavor flavor : selectedService.getSupportedDocFlavors()) {
                System.out.println(" - " + flavor.getMimeType());
            }
            
            // Configurar el formato de página para impresora térmica
            PageFormat pf = job.defaultPage();
            Paper paper = new Paper();
            
            // Tamaño de papel para ticket (80mm x 297mm)
            double width = 80 * 2.83465; // ~227 puntos para 80mm
            double height = 297 * 2.83465; // ~842 puntos para 297mm
            paper.setSize(width, height);
            
            // Márgenes muy pequeños para impresora térmica
            double margin = 5; // 5 puntos de margen (~1.76mm)
            paper.setImageableArea(
                margin, 
                margin, 
                Math.max(1, width - (2 * margin)),
                Math.max(1, height - (2 * margin))
            );
            
            pf.setPaper(paper);
            pf.setOrientation(PageFormat.PORTRAIT);
            
            System.out.println("\n=== CONFIGURACIÓN DE PÁGINA ===");
            System.out.println("Tamaño: " + (width/2.83465) + "x" + (height/2.83465) + " mm (" + 
                             width + "x" + height + " puntos)");
            System.out.println("Área imprimible: " + pf.getImageableX() + "," + 
                             pf.getImageableY() + " " + 
                             pf.getImageableWidth() + "x" + 
                             pf.getImageableHeight());
            
            // Configurar el trabajo de impresión
            job.setPrintable(this, pf);
            job.setPrintService(selectedService);
            
            // Configurar atributos de impresión
            PrintRequestAttributeSet attributes = new HashPrintRequestAttributeSet();
            attributes.add(OrientationRequested.PORTRAIT);
            
            // Especificar el tamaño de papel personalizado
            try {
                attributes.add(new javax.print.attribute.standard.MediaPrintableArea(
                    0, 0, 
                    (int)(width/2.83465), (int)(height/2.83465), 
                    javax.print.attribute.standard.MediaPrintableArea.MM
                ));
            } catch (Exception e) {
                System.err.println("No se pudo configurar el tamaño de papel personalizado: " + e.getMessage());
            }
            
            // Intentar imprimir
            System.out.println("\n=== INICIANDO IMPRESIÓN ===");
            try {
                // Mostrar diálogo de impresión para depuración
                // boolean doPrint = job.printDialog(attributes);
                boolean doPrint = true; // Forzar impresión sin diálogo
                
                if (doPrint) {
                    System.out.println("Configurando impresora: " + selectedService.getName());
                    
                    // Verificar si la impresora está lista
                    javax.print.attribute.AttributeSet attrs = selectedService.getAttributes();
                    System.out.println("Atributos de la impresora: " + attrs);
                    
                    try {
                        // Verificar estado de la impresora
                        System.out.println("Estado de la impresora:");
                        for (javax.print.attribute.Attribute attr : attrs.toArray()) {
                            System.out.println("  " + attr.getName() + ": " + attr);
                        }
                        
                        // Verificar si la impresora acepta trabajos
                        if (!selectedService.isDocFlavorSupported(DocFlavor.SERVICE_FORMATTED.PAGEABLE)) {
                            System.err.println("La impresora no soporta el tipo de documento solicitado");
                        }
                    } catch (Exception e) {
                        System.err.println("No se pudieron obtener los atributos de la impresora: " + e.getMessage());
                    }
                    
                    System.out.println("Enviando trabajo de impresión...");
                    job.print(attributes);
                    System.out.println("Trabajo de impresión enviado correctamente a la cola");
                    
                    // Esperar un momento para ver si hay errores
                    try {
                        Thread.sleep(1000); // Esperar 1 segundo
                    } catch (InterruptedException ie) {
                        Thread.currentThread().interrupt();
                    }
                    
                    // Verificar si el trabajo se envió correctamente
                    System.out.println("Trabajo de impresión enviado a la cola");
                    
                    // Verificar el estado de la impresora después de enviar el trabajo
                    System.out.println("Verificando estado de la impresora después del envío...");
                    System.out.println("Atributos actuales: " + selectedService.getAttributes());
                    
                    return true;
                } else {
                    System.out.println("Impresión cancelada por el usuario");
                    return false;
                }
            } catch (PrinterException e) {
                System.err.println("\n=== ERROR DE IMPRESIÓN ===");
                System.err.println("Tipo: " + e.getClass().getName());
                System.err.println("Mensaje: " + e.getMessage());
                if (e.getCause() != null) {
                    System.err.println("Causa: " + e.getCause().getMessage());
                }
                System.err.println("Estado de la impresora: " + 
                    (selectedService != null ? selectedService.getAttributes() : "[No disponible]"));
                e.printStackTrace();
                
                // Intentar obtener más detalles del error
                if (e.getMessage() != null && e.getMessage().contains("Printer is null")) {
                    System.err.println("ERROR: No se pudo encontrar la impresora especificada");
                } else if (e.getMessage() != null && e.getMessage().contains("Printer is not accepting jobs")) {
                    System.err.println("ERROR: La impresora no está aceptando trabajos");
                }
                
                // Intentar con la impresora genérica si falla con la seleccionada
                try {
                    System.out.println("\nIntentando con impresión genérica...");
                    PrintService[] services = PrintServiceLookup.lookupPrintServices(null, null);
                    if (services.length > 0) {
                        System.out.println("Usando impresora genérica: " + services[0].getName());
                        job.setPrintService(services[0]);
                        job.print(attributes);
                        return true;
                    }
                } catch (Exception ex) {
                    System.err.println("Error en impresión genérica: " + ex.getMessage());
                }
                
                return false;
            }
        } catch (Exception e) {
            System.err.println("\n=== ERROR INESPERADO ===");
            System.err.println("Tipo: " + e.getClass().getName());
            System.err.println("Mensaje: " + e.getMessage());
            e.printStackTrace();
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
        
        System.out.println("Iniciando proceso de impresión...");
        System.out.println("Ticket ID: " + (ticket != null ? ticket.getId() : "null"));
        System.out.println("Tipo de consumición: " + (ticket != null ? ticket.getTipoConsumicion() : "null"));
        System.out.println("Precio: " + (ticket != null && ticket.getPrecio() != null ? ticket.getPrecio() : "null"));
        
        if (ticket == null) {
            System.err.println("ERROR: El ticket es null");
            throw new PrinterException("El ticket es null y no se puede imprimir");
        }
        
        Graphics2D g2d = (Graphics2D) graphics;
        
        // Guardar la transformación original
        AffineTransform originalTransform = g2d.getTransform();
        
        // Obtener dimensiones del área imprimible
        double width = pageFormat.getImageableWidth();
        double x = pageFormat.getImageableX();
        double y = pageFormat.getImageableY();
        
        System.out.println("Dimensiones: " + width + "x" + pageFormat.getImageableHeight());
        System.out.println("Posición: x=" + x + ", y=" + y);
    
        // Ajustar la transformación para usar el área imprimible completa
        g2d.translate(x, y);
    
        // Usar tinta negra para el texto
        g2d.setColor(Color.BLACK);
        
        // Calcular posición central para alineación
        int centerX = (int)(width / 2);
        int currentY = 30; // Ajustar el margen superior
        
        // Fuentes
        Font normalFont = new Font("Arial", Font.PLAIN, 10);
        Font boldFont = new Font("Arial", Font.BOLD, 12);
        Font titleFont = new Font("Arial", Font.BOLD, 16);
        
        // Cargar logo del club para ambos tickets
        BufferedImage logoImage = null;
        try {
            // Obtener la ruta del logo desde la configuración
            ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
            String rutaLogo = config.getRutaLogo();
            
            System.out.println("Intentando cargar logo desde: " + rutaLogo);
            
            // Intenta cargar el logo del club si existe una ruta configurada
            if (rutaLogo != null && !rutaLogo.isEmpty()) {
                try {
                    File logoFile = new File(rutaLogo);
                    if (logoFile.exists()) {
                        System.out.println("Logo encontrado en la ruta configurada");
                        try {
                            logoImage = ImageIO.read(logoFile);
                            if (logoImage != null) {
                                System.out.println("Logo cargado correctamente desde la ruta configurada");
                            } else {
                                System.err.println("Error al cargar el logo: formato de imagen no válido");
                            }
                        } catch (Exception e) {
                            System.err.println("Error al leer el archivo de logo: " + e.getMessage());
                        }
                    } else {
                        System.out.println("El archivo de logo no existe en la ruta configurada: " + rutaLogo);
                    }
                } catch (Exception e) {
                    System.err.println("Error al verificar la existencia del logo: " + e.getMessage());
                }
            } else {
                System.out.println("No hay ruta de logo configurada, buscando logo por defecto");
            }
            
            // Si no se pudo cargar el logo configurado, intentar con el logo por defecto
            if (logoImage == null) {
                System.out.println("Buscando logo por defecto en múltiples ubicaciones...");
                // Intentar varias rutas posibles para encontrar el logo por defecto
                String[] posiblesRutas = {
                    "src/main/resources/images/logo_baru.png",
                    "target/classes/images/logo_baru.png",
                    "classes/images/logo_baru.png",
                    "images/logo_baru.png",
                    System.getProperty("user.dir") + "/images/logo_baru.png",
                    System.getProperty("user.dir") + "\\images\\logo_baru.png",
                    "src\\main\\resources\\images\\logo_baru.png",
                    "target\\classes\\images\\logo_baru.png",
                    "classes\\images\\logo_baru.png",
                    "images\\logo_baru.png"
                };
                
                for (String ruta : posiblesRutas) {
                    try {
                        System.out.println("Intentando cargar logo desde: " + ruta);
                        File logoFile = new File(ruta);
                        if (logoFile.exists()) {
                            System.out.println("Logo encontrado en: " + ruta);
                            try {
                                logoImage = ImageIO.read(logoFile);
                                if (logoImage != null) {
                                    System.out.println("Logo cargado correctamente desde: " + ruta);
                                    break;
                                } else {
                                    System.err.println("Error al cargar el logo desde " + ruta + ": formato de imagen no válido");
                                }
                            } catch (Exception e) {
                                System.err.println("Error al leer el archivo de logo desde " + ruta + ": " + e.getMessage());
                            }
                        }
                    } catch (Exception e) {
                        System.err.println("Error al verificar la ruta " + ruta + ": " + e.getMessage());
                    }
                }
                
                if (logoImage == null) {
                    System.err.println("No se pudo cargar el logo desde ninguna ubicación");
                }
            }
        } catch (Exception e) {
            System.err.println("Error general al cargar el logo: " + e.getMessage());
            e.printStackTrace();
        }
        
        if (!esVale) {
            // TICKET DE INFORMACIÓN (PRIMER TICKET)
            
            // Mostrar logo si existe
            if (logoImage != null) {
                int logoWidth = Math.min(80, logoImage.getWidth());
                int logoHeight = (int)((double)logoWidth / logoImage.getWidth() * logoImage.getHeight());
                
                // Dibujar logo centrado
                g2d.drawImage(logoImage, centerX - (logoWidth / 2), currentY, logoWidth, logoHeight, null);
                currentY += logoHeight + 10;
            } else {
                // Si no hay logo, mostrar título centrado
                g2d.setFont(titleFont);
                centrarTexto(g2d, "BARU SUMMER CLUB", centerX, currentY);
                currentY += 25;
            }
            
            // Encabezado - Información del sitio
            g2d.setFont(normalFont);
            // Usar la configuración global para la información del establecimiento
            ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
            
            centrarTexto(g2d, config.getNombreClub(), centerX, currentY);
            currentY += 15;
            centrarTexto(g2d, "CIF " + config.getCif(), centerX, currentY);
            currentY += 15;
            centrarTexto(g2d, config.getDireccion1(), centerX, currentY);
            currentY += 15;
            centrarTexto(g2d, config.getDireccion2(), centerX, currentY);
            currentY += 20;
            
            // Línea separadora
            dibujarLineaCentrada(g2d, centerX, currentY, 100);
            currentY += 20;
            
            // Número de ticket, fecha y referencia
            g2d.setFont(normalFont);
            SimpleDateFormat sdf = new SimpleDateFormat("dd/MM/yyyy");
            String fecha = sdf.format(ticket.getFechaCreacion());
            String ticketId = String.format("%05d", ticket.getId());
            
            // Línea con número de ticket y fecha
            String lineaTicket = "TICKET #" + ticketId + "   " + fecha;
            centrarTexto(g2d, lineaTicket, centerX, currentY);
            currentY += 15;
            
            // Línea con referencia
            centrarTexto(g2d, "REF: " + ticketId, centerX, currentY);
            currentY += 20;
            
            // Información de entrada
            g2d.setFont(boldFont);
            centrarTexto(g2d, "ENTRADA", centerX, currentY);
            currentY += 20;
            
            // Precio - Solo mostrar si la propiedad mostrarPrecio es true
            if (ticket.isMostrarPrecio()) {
                g2d.setFont(boldFont);
                // Comprobar que el precio no sea null antes de mostrarlo
                if (ticket.getPrecio() != null) {
                    String precioFormateado = ticket.getPrecio().toString();
                    centrarTexto(g2d, "Precio: " + precioFormateado + " €", centerX, currentY);
                    System.out.println("Imprimiendo precio: " + precioFormateado);
                } else {
                    centrarTexto(g2d, "Precio: 0.00 €", centerX, currentY);
                    System.out.println("El precio es null, se imprime 0.00");
                }
                currentY += 25;
            }
            
            // Frase del día en el primer ticket también
            if (ticket.getFraseDelDia() != null && !ticket.getFraseDelDia().isEmpty()) {
                g2d.setFont(normalFont);
                centrarTexto(g2d, ticket.getFraseDelDia(), centerX, currentY);
                currentY += 20;
            }
            
            // Mostrar el icono personalizado en lugar del triángulo
            if (ticket.getIcono() != null && !ticket.getIcono().isEmpty()) {
                try {
                    // Intentar cargar la imagen del icono
                    BufferedImage iconoImage = cargarIcono(ticket.getIcono());
                    if (iconoImage != null) {
                        // Dibujar la imagen centrada
                        int iconoAncho = 40; // Ancho máximo del icono
                        int iconoAlto = 40;  // Alto máximo del icono
                        
                        // Calcular las proporciones para mantener el aspecto
                        double ratio = Math.min(
                            (double)iconoAncho / iconoImage.getWidth(),
                            (double)iconoAlto / iconoImage.getHeight());
                        
                        int scaledWidth = (int)(iconoImage.getWidth() * ratio);
                        int scaledHeight = (int)(iconoImage.getHeight() * ratio);
                        
                        // Dibujar la imagen centrada
                        g2d.drawImage(iconoImage, 
                                      centerX - (scaledWidth / 2), 
                                      currentY, 
                                      scaledWidth, 
                                      scaledHeight, 
                                      null);
                        currentY += scaledHeight + 10;
                    } else {
                        // Si no se puede cargar la imagen, dibujar un triángulo como fallback
                        dibujarTrianguloCentrado(g2d, centerX, currentY, 15);
                        currentY += 20;
                    }
                } catch (Exception e) {
                    // Si hay un error, dibujar un triángulo como fallback
                    dibujarTrianguloCentrado(g2d, centerX, currentY, 15);
                    currentY += 20;
                }
            } else {
                // Si no hay icono seleccionado, dibujar un triángulo
                dibujarTrianguloCentrado(g2d, centerX, currentY, 15);
                currentY += 20;
            }
            
            // Línea separadora
            dibujarLineaCentrada(g2d, centerX, currentY, 100);
            currentY += 15;
            
            // Condiciones
            g2d.setFont(new Font("Arial", Font.PLAIN, 8)); // Usar fuente pequeña directamente
            centrarTexto(g2d, ticket.getCondicionesEntrada(), centerX, currentY);
            currentY += 15;
            
            // Línea de corte
            centrarTexto(g2d, "--------------------------------------------", centerX, currentY);
        } else {
            // BONO/VALE (SEGUNDO TICKET)
            
            // Mostrar logo si existe
            if (logoImage != null) {
                int logoWidth = Math.min(80, logoImage.getWidth());
                int logoHeight = (int)((double)logoWidth / logoImage.getWidth() * logoImage.getHeight());
                
                // Dibujar logo centrado
                g2d.drawImage(logoImage, centerX - (logoWidth / 2), currentY, logoWidth, logoHeight, null);
                currentY += logoHeight + 10;
            } else {
                // Si no hay logo, mostrar título centrado
                g2d.setFont(titleFont);
                centrarTexto(g2d, "BARU SUMMER CLUB", centerX, currentY);
                currentY += 25;
            }
            
            // Línea separadora
            dibujarLineaCentrada(g2d, centerX, currentY, 100);
            currentY += 20;
            
            // Frase del día (solo si existe)
            g2d.setFont(normalFont);
            if (ticket.getFraseDelDia() != null && !ticket.getFraseDelDia().isEmpty()) {
                centrarTexto(g2d, ticket.getFraseDelDia(), centerX, currentY);
                System.out.println("Imprimiendo frase en bono: " + ticket.getFraseDelDia());
                currentY += 20;
            } else {
                System.out.println("No hay frase para el bono");
            }
            
            // Tipo de consumición - GRANDE Y EN NEGRITA
            String tipoTexto = "";
            switch (ticket.getTipoConsumicion()) {
                case COPA:
                    tipoTexto = "BONO COPA";
                    break;
                case CERVEZAS:
                    tipoTexto = "BONO CERVEZA";
                    break;
                case SIN_CONSUMICION:
                    tipoTexto = "BONO ENTRADA";
                    break;
                case SOLO_TICKET:
                    tipoTexto = "BONO NO APLICABLE"; // Aunque este caso no debería ejecutarse nunca
                    break;
            }
            
            // Imprimir tipo de consumición en negrita
            g2d.setFont(new Font("Arial", Font.BOLD, 18));
            centrarTexto(g2d, tipoTexto, centerX, currentY);
            currentY += 25;
            
            // Número de ticket
            g2d.setFont(normalFont);
            centrarTexto(g2d, "Ref: " + ticket.getId(), centerX, currentY);
            currentY += 15;
            
            // Dibujar el icono seleccionado en lugar del triángulo
            if (ticket.getIcono() != null && !ticket.getIcono().isEmpty()) {
                try {
                    // Intentar cargar la imagen del icono
                    BufferedImage iconoImage = cargarIcono(ticket.getIcono());
                    if (iconoImage != null) {
                        // Dibujar la imagen centrada
                        int iconoAncho = 40; // Ancho máximo del icono
                        int iconoAlto = 40;  // Alto máximo del icono
                        
                        // Calcular las proporciones para mantener el aspecto
                        double ratio = Math.min(
                            (double)iconoAncho / iconoImage.getWidth(),
                            (double)iconoAlto / iconoImage.getHeight());
                        
                        int scaledWidth = (int)(iconoImage.getWidth() * ratio);
                        int scaledHeight = (int)(iconoImage.getHeight() * ratio);
                        
                        // Dibujar la imagen centrada
                        g2d.drawImage(iconoImage, 
                                      centerX - (scaledWidth / 2), 
                                      currentY, 
                                      scaledWidth, 
                                      scaledHeight, 
                                      null);
                        currentY += scaledHeight + 10;
                    } else {
                        // Si no se puede cargar la imagen, dibujar un triángulo como fallback
                        dibujarTrianguloCentrado(g2d, centerX, currentY, 15);
                        currentY += 20;
                    }
                } catch (Exception e) {
                    // Si hay un error, dibujar un triángulo como fallback
                    dibujarTrianguloCentrado(g2d, centerX, currentY, 15);
                    currentY += 20;
                }
            } else {
                // Si no hay icono seleccionado, dibujar un triángulo
                dibujarTrianguloCentrado(g2d, centerX, currentY, 15);
                currentY += 20;
            }
            
            // Línea separadora
            dibujarLineaCentrada(g2d, centerX, currentY, 100);
            currentY += 15;
            
            // Condiciones
            g2d.setFont(normalFont);
            String condiciones = ticket.getCondicionesConsumicion();
            if (condiciones != null) {
                centrarTexto(g2d, condiciones, centerX, currentY);
            } else {
                centrarTexto(g2d, "Sin condiciones", centerX, currentY);
            }
            currentY += 15;
            
            // Línea de corte
            centrarTexto(g2d, "--------------------------------------------", centerX, currentY);
        }
        
        // Restaurar la transformación original
        g2d.setTransform(originalTransform);
        
        return PAGE_EXISTS;
    }
    
    /**
     * Centra un texto en la posición X especificada
     */
    private void centrarTexto(Graphics2D g2d, String texto, int centerX, int y) {
        // Verificar que el texto no sea nulo para evitar NullPointerException
        if (texto == null) {
            texto = "";
        }
        int textWidth = g2d.getFontMetrics().stringWidth(texto);
        g2d.drawString(texto, centerX - (textWidth / 2), y);
    }
    
    /**
     * Dibuja una línea centrada con el ancho especificado
     */
    private void dibujarLineaCentrada(Graphics2D g2d, int centerX, int y, int halfWidth) {
        g2d.drawLine(centerX - halfWidth, y, centerX + halfWidth, y);
    }
    
    /**
     * Dibuja un triángulo centrado
     */
    private void dibujarTrianguloCentrado(Graphics2D g2d, int centerX, int y, int size) {
        int[] xPoints = {centerX, centerX - size, centerX + size};
        int[] yPoints = {y, y + size, y + size};
        g2d.fillPolygon(xPoints, yPoints, 3);
    }
    
    /**
     * Carga una imagen de icono desde el archivo
     * @param nombreIcono Nombre del archivo de icono
     * @return La imagen cargada o null si no se pudo cargar
     */
    private BufferedImage cargarIcono(String nombreIcono) {
        System.out.println("Intentando cargar icono: " + nombreIcono);
        try {
            // Buscar el icono en varias ubicaciones posibles
            String[] posiblesRutas = {
                "src/main/resources/images/" + nombreIcono,
                "target/classes/images/" + nombreIcono,
                "images/" + nombreIcono,
                System.getProperty("user.dir") + "/images/" + nombreIcono,
                nombreIcono // Por si se proporciona ruta absoluta
            };
            
            for (String ruta : posiblesRutas) {
                System.out.println("Probando ruta: " + ruta);
                File iconoFile = new File(ruta);
                if (iconoFile.exists()) {
                    System.out.println("Icono encontrado en: " + ruta);
                    return ImageIO.read(iconoFile);
                }
            }
            
            // Si llegamos aquí, intentar cargar iconos por defecto en la carpeta images
            File imagesDir = new File(System.getProperty("user.dir") + "/images");
            if (imagesDir.exists() && imagesDir.isDirectory()) {
                File[] iconFiles = imagesDir.listFiles((dir, name) -> name.startsWith("icono-"));
                if (iconFiles != null && iconFiles.length > 0) {
                    System.out.println("Usando icono por defecto: " + iconFiles[0].getName());
                    return ImageIO.read(iconFiles[0]);
                }
            }
            
            System.out.println("No se encontró ningún icono válido");
            return null;
        } catch (IOException e) {
            System.err.println("Error al cargar el icono: " + e.getMessage());
            return null;
        }
    }
}
