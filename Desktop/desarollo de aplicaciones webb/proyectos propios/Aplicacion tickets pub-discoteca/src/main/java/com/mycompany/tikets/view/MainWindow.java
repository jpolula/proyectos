package com.mycompany.tikets.view;

import com.mycompany.tikets.controller.TicketController;
import com.mycompany.tikets.model.Ticket;
import com.mycompany.tikets.model.ConfiguracionTikets;
import com.mycompany.tikets.util.*;
import java.sql.Connection;
import java.sql.SQLException;
import javax.swing.*;
import java.awt.*;
import java.awt.event.*;
import java.math.BigDecimal;

/**
 * Ventana principal de la aplicación de gestión de tickets para Baru Summer Club
 */
public class MainWindow extends JFrame implements ConfiguracionListener {
    
    // Componentes de la interfaz
    private final JRadioButton radioCopa;
    private final JRadioButton radioCervezas;
    private final JRadioButton radioSinConsumicion;
    private final JRadioButton radioSoloTicket;
    private final JComboBox<Integer> comboCantidad;
    private final JButton btnCrearTicket;
    private final JButton btnConfiguracion;
    private final JLabel lblPrecio;
    private final JLabel lblEstadoConexion;
    private TicketController ticketController; // Cambiado a no final para permitir inicialización en el constructor

    /**
     * Constructor de la ventana principal
     */
    public MainWindow() {
        try {
            // Inicializar controlador primero
            ticketController = new TicketController();
            
            // Configuración básica de la ventana
            setTitle("BARU SUMMER CLUB - Gestión de Tickets");
            setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
            setSize(800, 600);
            setLocationRelativeTo(null); // Centrar en pantalla
            
            // Configurar Look and Feel
            try {
                UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
                UIScaler.scaleGlobalFonts();
            } catch (Exception e) {
                System.err.println("Error al configurar el Look and Feel: " + e.getMessage());
                JOptionPane.showMessageDialog(null, 
                    "Error al configurar la apariencia: " + e.getMessage(), 
                    "Advertencia", 
                    JOptionPane.WARNING_MESSAGE);
            }
        } catch (Exception e) {
            JOptionPane.showMessageDialog(null, 
                "Error al inicializar la aplicación: " + e.getMessage(), 
                "Error crítico", 
                JOptionPane.ERROR_MESSAGE);
            System.exit(1);
        }
        
        // Inicializar componentes
        radioCopa = new JRadioButton("Copa");
        radioCervezas = new JRadioButton("Cervezas");
        radioSinConsumicion = new JRadioButton("Sin consumición");
        radioSoloTicket = new JRadioButton("Solo ticket");
        
        // Agrupar botones de radio
        ButtonGroup grupoConsumicion = new ButtonGroup();
        grupoConsumicion.add(radioCopa);
        grupoConsumicion.add(radioCervezas);
        grupoConsumicion.add(radioSinConsumicion);
        grupoConsumicion.add(radioSoloTicket);
        radioCopa.setSelected(true); // Selección por defecto
        
        // Configurar combo de cantidad
        comboCantidad = new JComboBox<>(new Integer[]{1, 2, 3, 4, 5, 10});
        
        // Botones
        btnCrearTicket = new JButton("Crear Ticket");
        btnCrearTicket.addActionListener(this::crearTicket);
        
        btnConfiguracion = new JButton("Configuración");
        btnConfiguracion.addActionListener(e -> {
            try {
                // Crear la ventana de configuración directamente
                ConfiguracionWindow configWindow = new ConfiguracionWindow((JFrame) SwingUtilities.getWindowAncestor(this));
                configWindow.setLocationRelativeTo(this);
                configWindow.setVisible(true);
            } catch (Exception ex) {
                JOptionPane.showMessageDialog(this,
                    "No se pudo abrir la ventana de configuración: " + ex.getMessage(),
                    "Error",
                    JOptionPane.ERROR_MESSAGE);
                ex.printStackTrace(); // Para depuración
            }
        });
        
        // Panel de estado
        JPanel panelEstado = new JPanel(new FlowLayout(FlowLayout.LEFT, 10, 5));
        panelEstado.setBorder(BorderFactory.createEtchedBorder());
        
        // Etiqueta de precio
        lblPrecio = new JLabel("Precio: 0.00 €");
        lblPrecio.setFont(lblPrecio.getFont().deriveFont(Font.BOLD, 16f));
        
        // Inicializar etiqueta de estado
        lblEstadoConexion = new JLabel("Verificando conexión...");
        lblEstadoConexion.setForeground(Color.GRAY);
        
        panelEstado.add(lblPrecio);
        panelEstado.add(Box.createHorizontalStrut(20));
        panelEstado.add(lblEstadoConexion);
        
        // Configurar el layout
        JPanel panelPrincipal = new JPanel(new BorderLayout(10, 10));
        panelPrincipal.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));
        
        // Panel de opciones
        JPanel panelOpciones = new JPanel(new GridLayout(0, 1, 5, 5));
        panelOpciones.setBorder(BorderFactory.createTitledBorder("Tipo de Consumición"));
        panelOpciones.add(radioCopa);
        panelOpciones.add(radioCervezas);
        panelOpciones.add(radioSinConsumicion);
        panelOpciones.add(radioSoloTicket);
        
        // Panel de controles
        JPanel panelControles = new JPanel(new FlowLayout(FlowLayout.CENTER, 10, 10));
        panelControles.add(new JLabel("Cantidad:"));
        panelControles.add(comboCantidad);
        panelControles.add(btnCrearTicket);
        panelControles.add(btnConfiguracion);
        panelControles.add(lblPrecio);
        
        // Añadir paneles a la ventana
        panelPrincipal.add(panelOpciones, BorderLayout.CENTER);
        panelPrincipal.add(panelControles, BorderLayout.SOUTH);
        panelPrincipal.add(panelEstado, BorderLayout.NORTH);
        
        add(panelPrincipal);
        
        // Actualizar el precio inicial y verificar conexión
        actualizarPrecio();
        verificarConexionBaseDatos();
        
        // Añadir listeners para actualizar el precio cuando cambia la selección
        java.awt.event.ActionListener actualizarPrecioListener = e -> actualizarPrecio();
        radioCopa.addActionListener(actualizarPrecioListener);
        radioCervezas.addActionListener(actualizarPrecioListener);
        radioSinConsumicion.addActionListener(actualizarPrecioListener);
        radioSoloTicket.addActionListener(actualizarPrecioListener);
        
        // Configurar atajos de teclado
        configurarAtajosTeclado();
    }
    
    /**
     * Configura los atajos de teclado para la aplicación
     */
    private void configurarAtajosTeclado() {
        // Atajo para crear ticket: Ctrl+Enter
        getRootPane().getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW)
            .put(KeyStroke.getKeyStroke(java.awt.event.KeyEvent.VK_ENTER, java.awt.event.InputEvent.CTRL_DOWN_MASK), "crearTicket");
        getRootPane().getActionMap().put("crearTicket", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                btnCrearTicket.doClick();
            }
        });
        
        // Atajo para abrir configuración: Ctrl+,
        getRootPane().getInputMap(JComponent.WHEN_IN_FOCUSED_WINDOW)
            .put(KeyStroke.getKeyStroke("control COMMA"), "abrirConfiguracion");
        getRootPane().getActionMap().put("abrirConfiguracion", new AbstractAction() {
            @Override
            public void actionPerformed(ActionEvent e) {
                btnConfiguracion.doClick();
            }
        });
    }
    
    /**
     * Actualiza el precio mostrado según la opción seleccionada
     */
    private void actualizarPrecio() {
        ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
        double precio = 0.0;
        
        if (radioCopa.isSelected()) {
            precio = config.getPrecioCopa();
        } else if (radioCervezas.isSelected()) {
            precio = config.getPrecioCerveza();
        } else if (radioSoloTicket.isSelected()) {
            precio = config.getPrecioSoloTicket();
        } else {
            precio = config.getPrecioSinConsumicion();
        }
        
        lblPrecio.setText(String.format("Precio: %.2f €", precio));
    }
    
    /**
     * Método para crear un nuevo ticket
     */
    /**
     * Verifica el estado de la conexión a la base de datos
     */
    private void verificarConexionBaseDatos() {
        new Thread(() -> {
            try (Connection conn = DatabaseConnection.getConnection()) {
                SwingUtilities.invokeLater(() -> {
                    lblEstadoConexion.setText("Estado: Conectado");
                    lblEstadoConexion.setForeground(new Color(0, 128, 0)); // Verde oscuro
                });
            } catch (SQLException ex) {
                SwingUtilities.invokeLater(() -> {
                    lblEstadoConexion.setText("Error de conexión a la base de datos");
                    lblEstadoConexion.setForeground(Color.RED);
                });
            }
        }).start();
    }
    
    /**
     * Método de la interfaz ConfiguracionListener
     * Se llama cuando se actualiza la configuración
     */
    @Override
    public void configuracionActualizada() {
        actualizarPrecio();
    }
    
    private void crearTicket(ActionEvent e) {
        int cantidad = (int) comboCantidad.getSelectedItem();
        btnCrearTicket.setEnabled(false); // Deshabilitar botón mientras se procesa
        
        try {
            // Obtener la configuración actual
            ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
            
            // Determinar el tipo de consumición y el precio
            Ticket.TipoConsumicion tipoConsumicion = null;
            double precio = 0.0;
            
            if (radioCopa.isSelected()) {
                tipoConsumicion = Ticket.TipoConsumicion.COPA;
                precio = config.getPrecioCopa();
            } else if (radioCervezas.isSelected()) {
                tipoConsumicion = Ticket.TipoConsumicion.CERVEZAS;
                precio = config.getPrecioCerveza();
            } else if (radioSoloTicket.isSelected()) {
                tipoConsumicion = Ticket.TipoConsumicion.SOLO_TICKET;
                precio = config.getPrecioSoloTicket();
            } else {
                tipoConsumicion = Ticket.TipoConsumicion.SIN_CONSUMICION;
                precio = config.getPrecioSinConsumicion();
            }
            
            // Crear los tickets
            for (int i = 0; i < cantidad; i++) {
                Ticket ticket = new Ticket();
                ticket.setTipoConsumicion(tipoConsumicion);
                ticket.setPrecio(BigDecimal.valueOf(precio));
                ticket.setCantidad(1); // Cada ticket es una unidad
                // Asignar datos de configuración al ticket
                ticket.setMostrarPrecio(config.isMostrarPrecio());
                ticket.setFraseDelDia(config.getFraseDelDia());
                ticket.setCondicionesEntrada(config.getCondicionesEntrada());
                ticket.setCondicionesConsumicion(config.getCondicionesConsumicion());
                
                // Asignar icono desde la configuración
                String rutaIcono = config.getRutaIcono();
                if (rutaIcono != null && !rutaIcono.isEmpty()) {
                    ticket.setIcono(rutaIcono);
                    System.out.println("Icono asignado al ticket: " + rutaIcono);
                } else {
                    // Si no hay icono configurado, intentar usar el logo como alternativa
                    String rutaLogo = config.getRutaLogo();
                    if (rutaLogo != null && !rutaLogo.isEmpty()) {
                        ticket.setIcono(rutaLogo);
                        System.out.println("No hay icono configurado, usando logo como alternativa: " + rutaLogo);
                    } else {
                        System.out.println("No hay icono ni logo configurado en la configuración");
                    }
                }
                
                // Guardar el ticket en la base de datos
                int ticketId = ticketController.guardarTicket(ticket);
                if (ticketId <= 0) {
                    throw new Exception("No se pudo guardar el ticket en la base de datos");
                }
                
                // Obtener el ticket completo con el ID asignado
                Ticket ticketGuardado = ticketController.obtenerTicketPorId(ticketId);
                if (ticketGuardado != null) {
                    ticket = ticketGuardado; // Usar el ticket guardado
                }
                
                try {
                    // Imprimir el ticket principal solo si está configurado
                    if (config.isImprimirTicket()) {
                        TicketPrinter2.imprimirTicket(ticket, config);
                    }
                    
                    // Imprimir vales según el tipo de consumición y si está habilitado
                    if (config.isImprimirVale()) {
                        if (tipoConsumicion == Ticket.TipoConsumicion.COPA) {
                            // 1 vale para COPA
                            TicketPrinter2.imprimirVale(ticket, config);
                        } else if (tipoConsumicion == Ticket.TipoConsumicion.CERVEZAS) {
                            // 2 vales para CERVEZAS
                            TicketPrinter2.imprimirVale(ticket, config);
                            TicketPrinter2.imprimirVale(ticket, config);
                        }
                    }
                    // No se imprime vale para SIN_CONSUMICION ni SOLO_TICKET

                } catch (UnsupportedOperationException ex) {
                    System.err.println("Advertencia: La función de impresión no está implementada: " + ex.getMessage());
                } catch (Exception ex) {
                    System.err.println("Error al imprimir ticket/vale: " + ex.getMessage());
                    ex.printStackTrace();
                }
            }
            
            
        } catch (Exception ex) {
            JOptionPane.showMessageDialog(
                this,
                "<html><b>Error al crear los tickets:</b><br>" + 
                ex.getMessage().replace("\n", "<br>") + "</html>",
                "Error",
                JOptionPane.ERROR_MESSAGE
            );
            System.err.println("Error en crearTicket: ");
            ex.printStackTrace();
        } finally {
            btnCrearTicket.setEnabled(true);
        }
    }
}
