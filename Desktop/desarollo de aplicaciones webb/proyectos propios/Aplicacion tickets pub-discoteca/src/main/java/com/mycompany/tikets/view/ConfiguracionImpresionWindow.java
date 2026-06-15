package com.mycompany.tikets.view;

import javax.swing.*;
import javax.print.PrintService;
import javax.print.PrintServiceLookup;
import java.awt.*;
import java.awt.print.PrinterJob;
import java.awt.print.Printable;
import java.awt.print.PrinterException;
import java.util.prefs.Preferences;
import com.mycompany.tikets.util.UIScaler;

/**
 * Ventana de configuración de impresión
 */
public class ConfiguracionImpresionWindow extends JDialog {
    private final JComboBox<String> comboImpresoras;
    private final JCheckBox chkImprimirTicket;
    private final JCheckBox chkMostrarPrecio;
    private PrintService[] printServices;
    private final Preferences prefs;

    /**
     * Constructor de la ventana de configuración de impresión
     * @param parent Ventana padre
     */
    public ConfiguracionImpresionWindow(JFrame parent) {
        super(parent, "Configuración de Impresión", true);
        this.prefs = Preferences.userNodeForPackage(ConfiguracionImpresionWindow.class);
        this.comboImpresoras = new JComboBox<>();
        this.chkImprimirTicket = new JCheckBox("Imprimir Ticket");
        this.chkMostrarPrecio = new JCheckBox("Mostrar Precio");
        
        initComponents();
        actualizarListaImpresoras();
        cargarConfiguracion();
    }

    /**
     * Inicializa los componentes de la interfaz
     */
    private void initComponents() {
        setDefaultCloseOperation(JDialog.DISPOSE_ON_CLOSE);
        setLayout(new BorderLayout(10, 10));
        
        // Usar UIScaler para establecer pantalla completa
        UIScaler.scaleDialog(this, 500, 300);
        
        // Aplicar escala global de fuentes
        UIScaler.scaleGlobalFonts();

        JPanel mainPanel = new JPanel(new BorderLayout(10, 10));
        mainPanel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));

        // Título de la ventana
        JLabel lblTitulo = new JLabel("CONFIGURACIÓN DE IMPRESIÓN", SwingConstants.CENTER);
        lblTitulo.setFont(new Font("Arial", Font.BOLD, 24));
        mainPanel.add(lblTitulo, BorderLayout.NORTH);

        // Panel central con selección de impresora
        JPanel centerPanel = new JPanel(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(10, 10, 10, 10);
        gbc.anchor = GridBagConstraints.WEST;
        gbc.gridx = 0;
        gbc.gridy = 0;

        // Etiqueta y combo de impresoras
        centerPanel.add(new JLabel("Impresora:"), gbc);
        gbc.gridx = 1;
        centerPanel.add(comboImpresoras, gbc);
        
        // Checkbox para mostrar precio
        gbc.gridx = 0;
        gbc.gridy++;
        gbc.gridwidth = 2;
        chkMostrarPrecio.setSelected(true);
        centerPanel.add(chkMostrarPrecio, gbc);
        
        // Checkbox para imprimir ticket
        gbc.gridy++;
        chkImprimirTicket.setSelected(true);
        centerPanel.add(chkImprimirTicket, gbc);
        chkImprimirTicket.setToolTipText("Si está desmarcado, solo se imprimirán los vales");
        
        // Botón de prueba de impresión
        gbc.gridy++;
        JButton btnProbar = new JButton("Probar Impresión");
        btnProbar.addActionListener(e -> probarImpresion());
        centerPanel.add(btnProbar, gbc);
        
        mainPanel.add(centerPanel, BorderLayout.CENTER);

        // Panel de botones inferior
        JPanel buttonPanel = new JPanel();
        JButton btnGuardar = new JButton("Guardar");
        btnGuardar.addActionListener(e -> guardarConfiguracion());
        JButton btnCancelar = new JButton("Cancelar");
        btnCancelar.addActionListener(e -> dispose());
        buttonPanel.add(btnGuardar);
        buttonPanel.add(btnCancelar);
        mainPanel.add(buttonPanel, BorderLayout.SOUTH);

        add(mainPanel);
        pack();
        setLocationRelativeTo(getParent());
    }

    /**
     * Actualiza la lista de impresoras disponibles en el combo
     */
    private void actualizarListaImpresoras() {
        comboImpresoras.removeAllItems();
        printServices = PrintServiceLookup.lookupPrintServices(null, null);
        if (printServices.length == 0) {
            comboImpresoras.addItem("No se encontraron impresoras");
        } else {
            for (PrintService ps : printServices) {
                comboImpresoras.addItem(ps.getName());
            }
        }
    }

    /**
     * Prueba la impresión con la impresora seleccionada
     */
    private void probarImpresion() {
        int idx = comboImpresoras.getSelectedIndex();
        if (printServices == null || idx < 0 || idx >= printServices.length) {
            JOptionPane.showMessageDialog(this, "Seleccione una impresora válida.", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
        try {
            PrinterJob job = PrinterJob.getPrinterJob();
            job.setPrintService(printServices[idx]);
            job.setPrintable((graphics, pageFormat, pageIndex) -> {
                if (pageIndex > 0) return Printable.NO_SUCH_PAGE;
                graphics.drawString("PRUEBA DE IMPRESIÓN", 100, 100);
                return Printable.PAGE_EXISTS;
            });
            job.print();
            JOptionPane.showMessageDialog(this, "Impresión enviada.", "OK", JOptionPane.INFORMATION_MESSAGE);
        } catch (PrinterException ex) {
            JOptionPane.showMessageDialog(this, "Error al imprimir: " + ex.getMessage(), "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    /**
     * Guarda la configuración actual
     */
    private void guardarConfiguracion() {
        boolean imprimirTicket = chkImprimirTicket.isSelected();
        boolean mostrarPrecio = chkMostrarPrecio.isSelected();
        
        int idx = comboImpresoras.getSelectedIndex();
        if (printServices == null || idx < 0 || idx >= printServices.length) {
            JOptionPane.showMessageDialog(this, 
                "Seleccione una impresora válida.", 
                "Error", 
                JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        prefs.put("impresora", printServices[idx].getName());
        prefs.putBoolean("imprimirTicket", imprimirTicket);
        prefs.putBoolean("mostrarPrecio", mostrarPrecio);
        
        JOptionPane.showMessageDialog(this, 
            "Configuración guardada correctamente.", 
            "Configuración", 
            JOptionPane.INFORMATION_MESSAGE);
            
        dispose();
    }
    
    /**
     * Carga la configuración guardada
     */
    private void cargarConfiguracion() {
        String impresora = prefs.get("impresora", "");
        boolean imprimirTicket = prefs.getBoolean("imprimirTicket", true);
        boolean mostrarPrecio = prefs.getBoolean("mostrarPrecio", true);
        
        // Configurar checkboxes
        chkImprimirTicket.setSelected(imprimirTicket);
        chkMostrarPrecio.setSelected(mostrarPrecio);
        
        // Seleccionar impresora guardada
        if (!impresora.isEmpty() && printServices != null) {
            for (int i = 0; i < printServices.length; i++) {
                if (printServices[i].getName().equals(impresora)) {
                    comboImpresoras.setSelectedIndex(i);
                    break;
                }
            }
        }
    }
    
    /**
     * Clase para almacenar la configuración de impresión
     */
    public static class ConfiguracionImpresion {
        private String impresora = "";
        private boolean imprimirTicket = true;
        private boolean mostrarPrecio = true;

        /**
         * @return Nombre de la impresora configurada
         */
        public String getImpresora() {
            return impresora;
        }
        
        /**
         * Establece la impresora a utilizar
         * @param impresora Nombre de la impresora
         */
        public void setImpresora(String impresora) {
            this.impresora = impresora != null ? impresora : "";
        }
        
        /**
         * @return true si se debe imprimir el ticket principal
         */
        public boolean isImprimirTicket() {
            return imprimirTicket;
        }
        
        /**
         * Establece si se debe imprimir el ticket principal
         * @param imprimirTicket true para imprimir el ticket
         */
        public void setImprimirTicket(boolean imprimirTicket) {
            this.imprimirTicket = imprimirTicket;
        }
        
        /**
         * @return true si se debe mostrar el precio en los tickets
         */
        public boolean isMostrarPrecio() {
            return mostrarPrecio;
        }
        
        /**
         * Establece si se debe mostrar el precio en los tickets
         * @param mostrarPrecio true para mostrar el precio
         */
        public void setMostrarPrecio(boolean mostrarPrecio) {
            this.mostrarPrecio = mostrarPrecio;
        }
    }
    /**
     * Carga la configuración de impresión guardada
     * @return Configuración de impresión cargada
     */
    public static ConfiguracionImpresion cargarConfiguracionImpresion() {
        try {
            Preferences prefs = Preferences.userNodeForPackage(ConfiguracionImpresionWindow.class);
            ConfiguracionImpresion config = new ConfiguracionImpresion();
            config.setImpresora(prefs.get("impresora", ""));
            config.setImprimirTicket(prefs.getBoolean("imprimirTicket", true));
            config.setMostrarPrecio(prefs.getBoolean("mostrarPrecio", true));
            return config;
        } catch (Exception e) {
            System.err.println("Error al cargar la configuración de impresión: " + e.getMessage());
            // Retornar configuración por defecto en caso de error
            return new ConfiguracionImpresion();
        }
    }
}
