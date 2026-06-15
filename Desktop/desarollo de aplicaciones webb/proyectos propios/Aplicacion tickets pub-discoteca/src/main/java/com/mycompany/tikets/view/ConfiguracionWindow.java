package com.mycompany.tikets.view;

import java.awt.BorderLayout;
import java.awt.Color;
import java.awt.Dimension;
import java.awt.FlowLayout;
import java.awt.Font;
import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
import java.awt.GridLayout;
import java.awt.Image;
import java.awt.Insets;
import java.awt.Window;
import java.awt.event.ActionEvent;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import com.mycompany.tikets.util.UIScaler;
import com.mycompany.tikets.model.ConfiguracionTikets;
import com.mycompany.tikets.util.DatabaseConnection;

import javax.swing.Box;
import javax.swing.JScrollPane;
import javax.swing.SwingUtilities;

import javax.swing.BorderFactory;
import javax.swing.ImageIcon;
import javax.swing.JButton;
import javax.swing.JCheckBox;
import javax.swing.JComboBox;
import javax.swing.JDialog;
import javax.swing.JFileChooser;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JOptionPane;
import javax.swing.JPanel;
import javax.swing.JSeparator;
import javax.swing.JTabbedPane;
import javax.swing.JTextField;
import javax.swing.JPasswordField;
import javax.swing.border.EmptyBorder;
import javax.swing.filechooser.FileNameExtensionFilter;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.util.ArrayList;
import java.util.List;
import java.util.prefs.Preferences;
import javax.print.PrintService;
import javax.print.PrintServiceLookup;
import javax.swing.filechooser.FileView;

/**
 * Ventana de configuración de la aplicación
 */
public class ConfiguracionWindow extends JDialog {
    
    // Campos para datos del club
    private JTextField txtNombreClub;
    private JTextField txtCIF;
    private JTextField txtDireccion1;
    private JTextField txtDireccion2;
    private JTextField txtPrecioCopa;
    private JTextField txtPrecioCerveza;
    private JTextField txtPrecioSinConsumicion;
    private JTextField txtPrecioSoloTicket;
    private JTextField txtRutaLogo;
    private JButton btnSeleccionarLogo;
    private JLabel lblVistaPrevia;
    
    // Campos para el icono
    private JTextField txtRutaIcono;
    private JButton btnSeleccionarIcono;
    private JLabel lblVistaPreviaIcono;
    
    // Campos para opciones de impresión
    private JCheckBox checkImprimirTicket;
    private JCheckBox checkMostrarPrecio;
    private JCheckBox checkImprimirVale;
    private JComboBox<String> comboImpresoras;
    private JTextField txtFraseDelDia;
    private JTextField txtCondicionesEntrada;
    private JTextField txtCondicionesConsumicion;
    
    // Campos para configuración de base de datos
    private JTextField txtDbHost;
    private JTextField txtDbPort;
    private JTextField txtDbName;
    private JTextField txtDbUser;
    private JPasswordField txtDbPassword;
    
    // Lista de listeners para notificar cambios
    private List<ConfiguracionListener> listeners = new ArrayList<>();
    
    /**
     * Constructor de la ventana de configuración
     * @param parent Ventana padre
     */
    public ConfiguracionWindow(JFrame parent) {
        super(parent, "Configuración", true);
        
        initComponents();
        cargarConfiguracion();
    }
    
    /**
     * Inicializa los componentes de la interfaz
     */
    private void initComponents() {
        // Configuración básica de la ventana
        setTitle("Configuración - Baru Summer Club");
        setDefaultCloseOperation(JDialog.DISPOSE_ON_CLOSE);
        
        // Tamaño y posición
        setSize(750, 650);
        setLocationRelativeTo(null);
        setResizable(true);
        
        // Layout principal
        setLayout(new BorderLayout());
        
        // Panel principal con borde y espaciado
        JPanel mainPanel = new JPanel(new BorderLayout());
        mainPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));
        
        // Panel de pestañas
        JTabbedPane tabbedPane = new JTabbedPane();
        
        // Pestaña de datos del club
        JPanel panelDatosClub = crearPanelDatosClub();
        JScrollPane scrollDatosClub = new JScrollPane(panelDatosClub);
        scrollDatosClub.setBorder(BorderFactory.createEmptyBorder());
        tabbedPane.addTab("Datos del Club", new JPanel(new BorderLayout()) {{
            add(scrollDatosClub, BorderLayout.CENTER);
        }});
        
        // Pestaña de opciones de impresión
        JPanel panelImpresion = crearPanelImpresion();
        JScrollPane scrollImpresion = new JScrollPane(panelImpresion);
        scrollImpresion.setBorder(BorderFactory.createEmptyBorder());
        tabbedPane.addTab("Impresión", new JPanel(new BorderLayout()) {{
            add(scrollImpresion, BorderLayout.CENTER);
        }});
        
        // Pestaña de configuración de base de datos
        JPanel panelBaseDatos = crearPanelBaseDatos();
        JScrollPane scrollBaseDatos = new JScrollPane(panelBaseDatos);
        scrollBaseDatos.setBorder(BorderFactory.createEmptyBorder());
        tabbedPane.addTab("Base de Datos", new JPanel(new BorderLayout()) {{
            add(scrollBaseDatos, BorderLayout.CENTER);
        }});
        
        // Añadir pestañas al panel principal
        mainPanel.add(tabbedPane, BorderLayout.CENTER);
        
        // Panel de botones
        JPanel panelBotones = new JPanel(new FlowLayout(FlowLayout.RIGHT, 10, 10));
        panelBotones.setBorder(BorderFactory.createEmptyBorder(10, 0, 0, 0));
        
        // Botón Guardar
        JButton btnGuardar = new JButton("Guardar");
        btnGuardar.setFont(new Font("Segoe UI", Font.BOLD, 12));
        btnGuardar.setPreferredSize(new Dimension(100, 32));
        btnGuardar.setBackground(new Color(76, 175, 80));
        btnGuardar.setForeground(Color.WHITE);
        btnGuardar.setFocusPainted(false);
        btnGuardar.addActionListener((ActionEvent e) -> {
            if (guardarConfiguracion()) {
                notificarCambios();
                dispose();
            }
        });
        
        // Botón Cancelar
        JButton btnCancelar = new JButton("Cancelar");
        btnCancelar.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        btnCancelar.setPreferredSize(new Dimension(100, 32));
        btnCancelar.setBackground(new Color(239, 239, 239));
        btnCancelar.setFocusPainted(false);
        btnCancelar.addActionListener((ActionEvent e) -> {
            dispose();
        });
        
        // Añadir botones al panel
        panelBotones.add(btnCancelar);
        panelBotones.add(btnGuardar);
        
        // Añadir todo al contenido de la ventana
        add(mainPanel, BorderLayout.CENTER);
        add(panelBotones, BorderLayout.SOUTH);
        
        // Forzar actualización de la interfaz
        revalidate();
        repaint();
        btnCancelar.addActionListener((ActionEvent e) -> {
            dispose();
        });
        
        // Añadir espacio entre botones
        panelBotones.add(btnGuardar);
        panelBotones.add(Box.createRigidArea(new Dimension(20, 0))); // Espacio entre botones
        panelBotones.add(btnCancelar);
        
        // Añadir componentes a la ventana
        add(tabbedPane, BorderLayout.CENTER);
        add(panelBotones, BorderLayout.SOUTH);
    }
    
    /**
     * Crea el panel de datos del club
     * @return Panel con los campos de datos del club
     */
    private JPanel crearPanelDatosClub() {
        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBorder(new EmptyBorder(15, 15, 15, 15));
        panel.setBackground(Color.WHITE);
        
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.insets = new Insets(5, 10, 5, 10);
        gbc.anchor = GridBagConstraints.WEST;
        
        // Título de la sección
        JLabel lblTitulo = new JLabel("Datos del Club");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 16));
        lblTitulo.setForeground(new Color(51, 51, 51));
        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.gridwidth = 3;
        gbc.weightx = 1.0;
        gbc.insets = new Insets(0, 0, 15, 0);
        panel.add(lblTitulo, gbc);
        
        // Panel de información básica
        JPanel panelInfo = new JPanel(new GridBagLayout());
        panelInfo.setBorder(BorderFactory.createTitledBorder(
            BorderFactory.createLineBorder(new Color(200, 200, 200)), 
            "Información Básica"
        ));
        panelInfo.setBackground(Color.WHITE);
        
        GridBagConstraints gbcInfo = new GridBagConstraints();
        gbcInfo.fill = GridBagConstraints.HORIZONTAL;
        gbcInfo.insets = new Insets(5, 10, 5, 10);
        gbcInfo.anchor = GridBagConstraints.WEST;
        
        // Nombre del club
        JLabel lblNombre = new JLabel("Nombre del Club:");
        lblNombre.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 0;
        gbcInfo.gridy = 0;
        gbcInfo.weightx = 0.3;
        panelInfo.add(lblNombre, gbcInfo);
        
        txtNombreClub = new JTextField(20);
        txtNombreClub.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 1;
        gbcInfo.gridy = 0;
        gbcInfo.weightx = 0.7;
        panelInfo.add(txtNombreClub, gbcInfo);
        
        // CIF
        JLabel lblCIF = new JLabel("CIF:");
        lblCIF.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 0;
        gbcInfo.gridy = 1;
        panelInfo.add(lblCIF, gbcInfo);
        
        txtCIF = new JTextField(20);
        txtCIF.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 1;
        gbcInfo.gridy = 1;
        panelInfo.add(txtCIF, gbcInfo);
        
        // Dirección 1
        JLabel lblDireccion1 = new JLabel("Dirección (línea 1):");
        lblDireccion1.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 0;
        gbcInfo.gridy = 2;
        panelInfo.add(lblDireccion1, gbcInfo);
        
        txtDireccion1 = new JTextField(20);
        txtDireccion1.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 1;
        gbcInfo.gridy = 2;
        panelInfo.add(txtDireccion1, gbcInfo);
        
        // Dirección 2
        JLabel lblDireccion2 = new JLabel("Dirección (línea 2):");
        lblDireccion2.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 0;
        gbcInfo.gridy = 3;
        panelInfo.add(lblDireccion2, gbcInfo);
        
        txtDireccion2 = new JTextField(20);
        txtDireccion2.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcInfo.gridx = 1;
        gbcInfo.gridy = 3;
        panelInfo.add(txtDireccion2, gbcInfo);
        
        // Panel de precios
        JPanel panelPrecios = new JPanel(new GridBagLayout());
        panelPrecios.setBorder(BorderFactory.createTitledBorder(
            BorderFactory.createLineBorder(new Color(200, 200, 200)), 
            "Precios"
        ));
        panelPrecios.setBackground(Color.WHITE);
        
        GridBagConstraints gbcPrecios = new GridBagConstraints();
        gbcPrecios.fill = GridBagConstraints.HORIZONTAL;
        gbcPrecios.insets = new Insets(5, 10, 5, 10);
        gbcPrecios.anchor = GridBagConstraints.WEST;
        
        // Precio Copa
        JLabel lblPrecioCopa = new JLabel("Precio Copa:");
        lblPrecioCopa.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 0;
        gbcPrecios.gridy = 0;
        panelPrecios.add(lblPrecioCopa, gbcPrecios);
        
        txtPrecioCopa = new JTextField(10);
        txtPrecioCopa.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 1;
        gbcPrecios.gridy = 0;
        panelPrecios.add(txtPrecioCopa, gbcPrecios);
        
        // Precio Cerveza
        JLabel lblPrecioCerveza = new JLabel("Precio Cerveza:");
        lblPrecioCerveza.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 2;
        gbcPrecios.gridy = 0;
        gbcPrecios.insets = new Insets(5, 20, 5, 10);
        panelPrecios.add(lblPrecioCerveza, gbcPrecios);
        
        txtPrecioCerveza = new JTextField(10);
        txtPrecioCerveza.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 3;
        gbcPrecios.gridy = 0;
        gbcPrecios.insets = new Insets(5, 0, 5, 10);
        panelPrecios.add(txtPrecioCerveza, gbcPrecios);
        
        // Precio Sin Consumición
        JLabel lblPrecioSinConsumicion = new JLabel("Sin Consumición:");
        lblPrecioSinConsumicion.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 0;
        gbcPrecios.gridy = 1;
        gbcPrecios.insets = new Insets(5, 10, 5, 10);
        panelPrecios.add(lblPrecioSinConsumicion, gbcPrecios);
        
        txtPrecioSinConsumicion = new JTextField(10);
        txtPrecioSinConsumicion.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 1;
        gbcPrecios.gridy = 1;
        panelPrecios.add(txtPrecioSinConsumicion, gbcPrecios);
        
        // Precio Solo Ticket
        JLabel lblPrecioSoloTicket = new JLabel("Solo Ticket:");
        lblPrecioSoloTicket.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 2;
        gbcPrecios.gridy = 1;
        gbcPrecios.insets = new Insets(5, 20, 5, 10);
        panelPrecios.add(lblPrecioSoloTicket, gbcPrecios);
        
        txtPrecioSoloTicket = new JTextField(10);
        txtPrecioSoloTicket.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcPrecios.gridx = 3;
        gbcPrecios.gridy = 1;
        gbcPrecios.insets = new Insets(5, 0, 5, 10);
        panelPrecios.add(txtPrecioSoloTicket, gbcPrecios);
        
        // Panel de imágenes
        JPanel panelImagenes = new JPanel(new GridBagLayout());
        panelImagenes.setBorder(BorderFactory.createTitledBorder(
            BorderFactory.createLineBorder(new Color(200, 200, 200)), 
            "Imágenes"
        ));
        panelImagenes.setBackground(Color.WHITE);
        
        GridBagConstraints gbcImagenes = new GridBagConstraints();
        gbcImagenes.fill = GridBagConstraints.HORIZONTAL;
        gbcImagenes.insets = new Insets(5, 10, 5, 10);
        gbcImagenes.anchor = GridBagConstraints.WEST;
        
        // Logo del club
        JPanel panelLogo = new JPanel(new BorderLayout(10, 5));
        panelLogo.setBackground(Color.WHITE);
        
        JPanel panelLogoControles = new JPanel(new GridBagLayout());
        panelLogoControles.setBackground(Color.WHITE);
        
        JLabel lblRutaLogo = new JLabel("Logo del Club:");
        lblRutaLogo.setFont(new Font("Segoe UI", Font.BOLD, 12));
        gbcImagenes.gridx = 0;
        gbcImagenes.gridy = 0;
        gbcImagenes.gridwidth = 2;
        panelLogoControles.add(lblRutaLogo, gbcImagenes);
        
        txtRutaLogo = new JTextField(20);
        txtRutaLogo.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcImagenes.gridx = 0;
        gbcImagenes.gridy = 1;
        gbcImagenes.gridwidth = 1;
        gbcImagenes.weightx = 1.0;
        gbcImagenes.fill = GridBagConstraints.HORIZONTAL;
        panelLogoControles.add(txtRutaLogo, gbcImagenes);
        
        btnSeleccionarLogo = new JButton("Examinar...");
        btnSeleccionarLogo.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        btnSeleccionarLogo.setPreferredSize(new Dimension(100, 25));
        gbcImagenes.gridx = 1;
        gbcImagenes.gridy = 1;
        gbcImagenes.weightx = 0.0;
        gbcImagenes.insets = new Insets(5, 10, 5, 0);
        panelLogoControles.add(btnSeleccionarLogo, gbcImagenes);
        
        btnSeleccionarLogo.addActionListener((ActionEvent e) -> {
            seleccionarLogo();
        });
        
        // Vista previa del logo
        lblVistaPrevia = new JLabel("<html><div style='text-align: center;'>No hay imagen<br>seleccionada</div></html>");
        lblVistaPrevia.setHorizontalAlignment(JLabel.CENTER);
        lblVistaPrevia.setVerticalAlignment(JLabel.CENTER);
        lblVistaPrevia.setFont(new Font("Segoe UI", Font.PLAIN, 11));
        lblVistaPrevia.setForeground(Color.GRAY);
        lblVistaPrevia.setBorder(BorderFactory.createCompoundBorder(
            BorderFactory.createLineBorder(new Color(200, 200, 200)),
            BorderFactory.createEmptyBorder(10, 10, 10, 10)
        ));
        lblVistaPrevia.setBackground(new Color(250, 250, 250));
        lblVistaPrevia.setOpaque(true);
        lblVistaPrevia.setPreferredSize(new Dimension(150, 120));
        
        panelLogo.add(panelLogoControles, BorderLayout.NORTH);
        panelLogo.add(lblVistaPrevia, BorderLayout.CENTER);
        
        // Icono para tickets
        JPanel panelIcono = new JPanel(new BorderLayout(10, 5));
        panelIcono.setBackground(Color.WHITE);
        
        JPanel panelIconoControles = new JPanel(new GridBagLayout());
        panelIconoControles.setBackground(Color.WHITE);
        
        JLabel lblRutaIcono = new JLabel("Icono para Tickets:");
        lblRutaIcono.setFont(new Font("Segoe UI", Font.BOLD, 12));
        gbcImagenes.gridx = 0;
        gbcImagenes.gridy = 2;
        gbcImagenes.gridwidth = 2;
        gbcImagenes.insets = new Insets(15, 10, 5, 10);
        panelIconoControles.add(lblRutaIcono, gbcImagenes);
        
        txtRutaIcono = new JTextField(20);
        txtRutaIcono.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        gbcImagenes.gridx = 0;
        gbcImagenes.gridy = 3;
        gbcImagenes.gridwidth = 1;
        gbcImagenes.weightx = 1.0;
        gbcImagenes.insets = new Insets(0, 10, 5, 10);
        panelIconoControles.add(txtRutaIcono, gbcImagenes);
        
        btnSeleccionarIcono = new JButton("Examinar...");
        btnSeleccionarIcono.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        btnSeleccionarIcono.setPreferredSize(new Dimension(100, 25));
        gbcImagenes.gridx = 1;
        gbcImagenes.gridy = 3;
        gbcImagenes.weightx = 0.0;
        gbcImagenes.insets = new Insets(0, 10, 5, 0);
        panelIconoControles.add(btnSeleccionarIcono, gbcImagenes);
        
        btnSeleccionarIcono.addActionListener((ActionEvent e) -> {
            seleccionarIcono();
        });
        
        // Vista previa del icono
        lblVistaPreviaIcono = new JLabel("<html><div style='text-align: center;'>No hay icono<br>seleccionado</div></html>");
        lblVistaPreviaIcono.setHorizontalAlignment(JLabel.CENTER);
        lblVistaPreviaIcono.setVerticalAlignment(JLabel.CENTER);
        lblVistaPreviaIcono.setFont(new Font("Segoe UI", Font.PLAIN, 11));
        lblVistaPreviaIcono.setForeground(Color.GRAY);
        lblVistaPreviaIcono.setBorder(BorderFactory.createCompoundBorder(
            BorderFactory.createLineBorder(new Color(200, 200, 200)),
            BorderFactory.createEmptyBorder(10, 10, 10, 10)
        ));
        lblVistaPreviaIcono.setBackground(new Color(250, 250, 250));
        lblVistaPreviaIcono.setOpaque(true);
        lblVistaPreviaIcono.setPreferredSize(new Dimension(150, 120));
        
        panelIcono.add(panelIconoControles, BorderLayout.NORTH);
        panelIcono.add(lblVistaPreviaIcono, BorderLayout.CENTER);
        
        // Añadir paneles de imágenes al panel principal de imágenes usando GridBagConstraints
        GridBagConstraints gbcImagenesMain = new GridBagConstraints();
        
        // Panel logo
        gbcImagenesMain.gridx = 0;
        gbcImagenesMain.gridy = 0;
        gbcImagenesMain.weightx = 0.5;
        gbcImagenesMain.fill = GridBagConstraints.BOTH;
        gbcImagenesMain.insets = new Insets(0, 0, 0, 10);
        panelImagenes.add(panelLogo, gbcImagenesMain);
        
        // Espaciador
        gbcImagenesMain.gridx = 1;
        gbcImagenesMain.weightx = 0;
        gbcImagenesMain.fill = GridBagConstraints.VERTICAL;
        gbcImagenesMain.insets = new Insets(0, 0, 0, 0);
        panelImagenes.add(Box.createHorizontalStrut(20), gbcImagenesMain);
        
        // Panel icono
        gbcImagenesMain.gridx = 2;
        gbcImagenesMain.weightx = 0.5;
        gbcImagenesMain.fill = GridBagConstraints.BOTH;
        gbcImagenesMain.insets = new Insets(0, 10, 0, 0);
        panelImagenes.add(panelIcono, gbcImagenesMain);
        
        // Reset constraints for main panel
        gbc = new GridBagConstraints();
        gbc.gridx = 0;
        gbc.gridy = 1;
        gbc.gridwidth = 1;
        gbc.weightx = 1.0;
        gbc.weighty = 0.0;
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.insets = new Insets(0, 0, 15, 0);
        gbc.anchor = GridBagConstraints.NORTHWEST;
        panel.add(panelInfo, gbc);
        
        // Add precios panel
        gbc.gridy = 2;
        gbc.weighty = 0.0;
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.insets = new Insets(0, 0, 15, 0);
        panel.add(panelPrecios, gbc);
        
        // Add images panel
        gbc.gridy = 3;
        gbc.weighty = 1.0;
        gbc.fill = GridBagConstraints.BOTH;
        gbc.insets = new Insets(0, 0, 0, 0);
        panel.add(panelImagenes, gbc);
        
        // Add spacer to push everything up
        JPanel spacerPanel = new JPanel();
        spacerPanel.setOpaque(false);
        gbc.gridy = 4;
        gbc.weighty = 1.0;
        gbc.fill = GridBagConstraints.BOTH;
        panel.add(spacerPanel, gbc);
        
        return panel;
    }
    
    /**
     * Crea el panel de opciones de impresión
     * @return Panel con las opciones de impresión
     */
    private JPanel crearPanelImpresion() {
        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBorder(new EmptyBorder(10, 10, 10, 10)); // Borde reducido
        
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.insets = new Insets(4, 5, 4, 5); // Espaciado reducido
        
        // Sección de opciones de impresión
        JLabel lblSeccionImpresion = new JLabel("Opciones de Impresión");
        lblSeccionImpresion.setFont(new Font("Arial", Font.BOLD, 16));
        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.gridwidth = 2;
        panel.add(lblSeccionImpresion, gbc);
        
        // Separador
        JSeparator separador1 = new JSeparator();
        gbc.gridx = 0;
        gbc.gridy = 1;
        gbc.gridwidth = 2;
        panel.add(separador1, gbc);
        
        // Selección de impresora predeterminada
        JLabel lblImpresora = new JLabel("Impresora Predeterminada:");
        gbc.gridx = 0;
        gbc.gridy = 2;
        gbc.gridwidth = 1;
        panel.add(lblImpresora, gbc);
        
        comboImpresoras = new JComboBox<>();
        gbc.gridx = 1;
        gbc.gridy = 2;
        panel.add(comboImpresoras, gbc);
        
        // Cargar la lista de impresoras disponibles
        actualizarListaImpresoras();
        
        // Checkbox para imprimir ticket
        checkImprimirTicket = new JCheckBox("Imprimir Ticket");
        gbc.gridx = 0;
        gbc.gridy = 3;
        gbc.gridwidth = 2;
        panel.add(checkImprimirTicket, gbc);
        
        // Checkbox para mostrar precio
        checkMostrarPrecio = new JCheckBox("Mostrar Precio");
        gbc.gridx = 0;
        gbc.gridy = 3;
        gbc.gridwidth = 2;
        panel.add(checkMostrarPrecio, gbc);
        
        // Checkbox para imprimir vale
        checkImprimirVale = new JCheckBox("Imprimir Vale");
        gbc.gridx = 0;
        gbc.gridy = 4;
        gbc.gridwidth = 2;
        panel.add(checkImprimirVale, gbc);
        
        // Sección de textos
        JLabel lblSeccionTextos = new JLabel("Textos");
        lblSeccionTextos.setFont(new Font("Arial", Font.BOLD, 16));
        gbc.gridx = 0;
        gbc.gridy = 5;
        gbc.gridwidth = 2;
        panel.add(lblSeccionTextos, gbc);
        
        // Separador
        JSeparator separador2 = new JSeparator();
        gbc.gridx = 0;
        gbc.gridy = 6;
        gbc.gridwidth = 2;
        panel.add(separador2, gbc);
        
        // Frase del día
        JLabel lblFraseDelDia = new JLabel("Frase del Día:");
        gbc.gridx = 0;
        gbc.gridy = 7;
        gbc.gridwidth = 1;
        panel.add(lblFraseDelDia, gbc);
        
        txtFraseDelDia = new JTextField(15);
        gbc.gridx = 1;
        gbc.gridy = 7;
        panel.add(txtFraseDelDia, gbc);
        
        // Condiciones de entrada
        JLabel lblCondicionesEntrada = new JLabel("Condiciones de Entrada:");
        gbc.gridx = 0;
        gbc.gridy = 8;
        panel.add(lblCondicionesEntrada, gbc);
        
        txtCondicionesEntrada = new JTextField(15);
        gbc.gridx = 1;
        gbc.gridy = 8;
        panel.add(txtCondicionesEntrada, gbc);
        
        // Condiciones de consumición
        JLabel lblCondicionesConsumicion = new JLabel("Condiciones de Consumición:");
        gbc.gridx = 0;
        gbc.gridy = 9;
        panel.add(lblCondicionesConsumicion, gbc);
        
        txtCondicionesConsumicion = new JTextField(15);
        gbc.gridx = 1;
        gbc.gridy = 9;
        panel.add(txtCondicionesConsumicion, gbc);
        
        return panel;
    }
    
    /**
     * Crea el panel de configuración de base de datos
     * @return Panel con los campos de configuración de base de datos
     */
    private JPanel crearPanelBaseDatos() {
        JPanel panel = new JPanel(new GridBagLayout());
        panel.setBorder(new EmptyBorder(10, 10, 10, 10)); // Borde reducido
        
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.insets = new Insets(4, 5, 4, 5); // Espaciado reducido
        
        // Sección de configuración de base de datos
        JLabel lblSeccionBaseDatos = new JLabel("Configuración de Base de Datos");
        lblSeccionBaseDatos.setFont(new Font("Arial", Font.BOLD, 16));
        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.gridwidth = 2;
        panel.add(lblSeccionBaseDatos, gbc);
        
        // Separador
        JSeparator separador = new JSeparator();
        gbc.gridx = 0;
        gbc.gridy = 1;
        gbc.gridwidth = 2;
        panel.add(separador, gbc);
        
        // Host
        JLabel lblHost = new JLabel("Host:");
        gbc.gridx = 0;
        gbc.gridy = 2;
        gbc.gridwidth = 1;
        panel.add(lblHost, gbc);
        
        txtDbHost = new JTextField(15);
        gbc.gridx = 1;
        gbc.gridy = 2;
        panel.add(txtDbHost, gbc);
        
        // Puerto
        JLabel lblPort = new JLabel("Puerto:");
        gbc.gridx = 0;
        gbc.gridy = 3;
        panel.add(lblPort, gbc);
        
        txtDbPort = new JTextField(15);
        gbc.gridx = 1;
        gbc.gridy = 3;
        panel.add(txtDbPort, gbc);
        
        // Nombre de la base de datos
        JLabel lblName = new JLabel("Nombre de la BD:");
        gbc.gridx = 0;
        gbc.gridy = 4;
        panel.add(lblName, gbc);
        
        txtDbName = new JTextField(15);
        gbc.gridx = 1;
        gbc.gridy = 4;
        panel.add(txtDbName, gbc);
        
        // Usuario
        JLabel lblUser = new JLabel("Usuario:");
        gbc.gridx = 0;
        gbc.gridy = 5;
        panel.add(lblUser, gbc);
        
        txtDbUser = new JTextField(15);
        gbc.gridx = 1;
        gbc.gridy = 5;
        panel.add(txtDbUser, gbc);
        
        // Contraseña
        JLabel lblPassword = new JLabel("Contraseña:");
        gbc.gridx = 0;
        gbc.gridy = 6;
        panel.add(lblPassword, gbc);
        
        txtDbPassword = new JPasswordField(15);
        gbc.gridx = 1;
        gbc.gridy = 6;
        panel.add(txtDbPassword, gbc);
        
        // Botón para probar conexión
        JButton btnProbarConexion = new JButton("Probar Conexión");
        btnProbarConexion.addActionListener(e -> probarConexion());
        gbc.gridx = 0;
        gbc.gridy = 7;
        gbc.gridwidth = 2; // Ocupa dos columnas
        gbc.anchor = GridBagConstraints.CENTER;
        gbc.insets = new Insets(15, 0, 0, 0); // Espacio superior
        panel.add(btnProbarConexion, gbc);
        
        // Restaurar configuración del grid
        gbc.gridwidth = 1;
        gbc.anchor = GridBagConstraints.WEST;
        gbc.insets = new Insets(2, 5, 2, 5);
        
        return panel;
    }
    
    /**
     * Prueba la conexión a la base de datos con los parámetros actuales
     */
    private void probarConexion() {
        // Guardar temporalmente los valores actuales
        String host = txtDbHost.getText().trim();
        String port = txtDbPort.getText().trim();
        String db = txtDbName.getText().trim();
        String user = txtDbUser.getText().trim();
        // Obtener la contraseña de forma segura
        String pass = new String(txtDbPassword.getPassword());
        
        // Validar campos obligatorios
        if (host.isEmpty() || port.isEmpty() || db.isEmpty() || user.isEmpty()) {
            JOptionPane.showMessageDialog(this, 
                "Por favor complete todos los campos obligatorios.", 
                "Campos incompletos", 
                JOptionPane.WARNING_MESSAGE);
            return;
        }
        
        // Mostrar mensaje de espera
        JOptionPane.showMessageDialog(this, 
            "Probando conexión a la base de datos...", 
            "Probando conexión", 
            JOptionPane.INFORMATION_MESSAGE);
        
        // Intentar conectar en un hilo separado para no bloquear la interfaz
        new Thread(() -> {
            try {
                // Configurar temporalmente la conexión con los valores del formulario
                java.util.prefs.Preferences prefs = java.util.prefs.Preferences.userNodeForPackage(ConfiguracionWindow.class);
                prefs.put("db_host", host);
                prefs.put("db_port", port);
                prefs.put("db_name", db);
                prefs.put("db_user", user);
                prefs.put("db_pass", pass);
                
                // Forzar una nueva conexión con los parámetros actualizados
                com.mycompany.tikets.util.DatabaseConnection.cerrarConexion();
                
                // Intentar conectar
                try (java.sql.Connection conn = com.mycompany.tikets.util.DatabaseConnection.getConnection()) {
                    // Si llegamos aquí, la conexión fue exitosa
                    SwingUtilities.invokeLater(() -> {
                        JOptionPane.showMessageDialog(ConfiguracionWindow.this, 
                            "¡Conexión exitosa!\n" +
                            "Base de datos: " + db + "\n" +
                            "Servidor: " + host + ":" + port + "\n" +
                            "Usuario: " + user,
                            "Conexión exitosa", 
                            JOptionPane.INFORMATION_MESSAGE);
                    });
                }
            } catch (Exception ex) {
                // Mostrar mensaje de error en el hilo de la interfaz
                SwingUtilities.invokeLater(() -> {
                    JOptionPane.showMessageDialog(ConfiguracionWindow.this, 
                        "Error al conectar a la base de datos:\n" + ex.getMessage(), 
                        "Error de conexión", 
                        JOptionPane.ERROR_MESSAGE);
                });
                System.err.println("Error al probar conexión a la base de datos:");
                ex.printStackTrace();
            }
        }).start();
    }
    
    /**
     * Método para seleccionar el icono para tickets
     */
    
    /**
     * Método para seleccionar el icono para tickets desde la carpeta de iconos
     */
    private void seleccionarIcono() {
        // Verificar y crear la carpeta de iconos si no existe
        File carpetaIconos = new File("iconos");
        if (!carpetaIconos.exists()) {
            boolean creada = carpetaIconos.mkdir();
            if (!creada) {
                JOptionPane.showMessageDialog(this, 
                    "No se pudo crear la carpeta 'iconos'. Verifica los permisos.",
                    "Error", 
                    JOptionPane.ERROR_MESSAGE);
                return;
            }
        }
        
        JFileChooser fileChooser = new JFileChooser(carpetaIconos);
        fileChooser.setDialogTitle("Seleccionar Icono para Tickets");
        fileChooser.setFileFilter(new FileNameExtensionFilter("Imágenes", "jpg", "jpeg", "png", "gif"));
        fileChooser.setFileSelectionMode(JFileChooser.FILES_ONLY);
        
        // Deshabilitar la navegación fuera del directorio de iconos
        fileChooser.setAcceptAllFileFilterUsed(false);
        fileChooser.setFileHidingEnabled(false);
        fileChooser.setFileView(new FileView() {
            @Override
            public Boolean isTraversable(File f) {
                // Solo permitir navegar dentro de la carpeta de iconos
                return f.isDirectory() && f.getParentFile().equals(carpetaIconos);
            }
        });
        
        int resultado = fileChooser.showOpenDialog(this);
        if (resultado == JFileChooser.APPROVE_OPTION) {
            File archivoSeleccionado = fileChooser.getSelectedFile();
            
            // Verificar que el archivo esté dentro de la carpeta de iconos
            try {
                String rutaCanonicaIcono = archivoSeleccionado.getCanonicalPath();
                String rutaCanonicaCarpeta = carpetaIconos.getCanonicalPath();
                
                if (!rutaCanonicaIcono.startsWith(rutaCanonicaCarpeta)) {
                    JOptionPane.showMessageDialog(this, 
                        "Debes seleccionar un archivo de la carpeta 'iconos'.",
                        "Ubicación no permitida", 
                        JOptionPane.WARNING_MESSAGE);
                    return;
                }
                
                // Guardar solo el nombre del archivo, no la ruta completa
                txtRutaIcono.setText(archivoSeleccionado.getName());
                
                // Actualizar la vista previa
                actualizarVistaPrevia(archivoSeleccionado.getAbsolutePath(), lblVistaPreviaIcono);
                
            } catch (IOException ex) {
                JOptionPane.showMessageDialog(this, 
                    "Error al acceder al archivo: " + ex.getMessage(),
                    "Error", 
                    JOptionPane.ERROR_MESSAGE);
            }
        }
    }
    
    /**
     * Actualiza la vista previa de una imagen
     * @param rutaImagen Ruta de la imagen a mostrar
     * @param labelDestino Label donde se mostrará la vista previa
     */
    private void actualizarVistaPrevia(String rutaImagen, JLabel labelDestino) {
        try {
            // Cargar la imagen
            File archivo = new File(rutaImagen);
            if (archivo.exists()) {
                ImageIcon icono = new ImageIcon(rutaImagen);
                
                // Redimensionar la imagen para que quepa en el label
                Image imagen = icono.getImage();
                Image imagenEscalada = imagen.getScaledInstance(140, 140, Image.SCALE_SMOOTH);
                ImageIcon iconoEscalado = new ImageIcon(imagenEscalada);
                
                // Mostrar la imagen en el label
                labelDestino.setIcon(iconoEscalado);
                labelDestino.setText(""); // Quitar el texto
            } else {
                labelDestino.setIcon(null);
                labelDestino.setText("Archivo no encontrado");
            }
        } catch (Exception ex) {
            labelDestino.setIcon(null);
            labelDestino.setText("Error al cargar imagen");
            System.err.println("Error al cargar vista previa: " + ex.getMessage());
            ex.printStackTrace();
        }
    }
    
    /**
     * Carga la configuración guardada
     */
    private void cargarConfiguracion() {
        try {
            // Forzar una recarga de la configuración desde la base de datos
            ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
            config.cargarConfiguracion(); // Forzar recarga desde la base de datos
            
            System.out.println("Cargando configuración desde la base de datos...");
            System.out.println("Nombre del club: " + config.getNombreClub());
            System.out.println("CIF: " + config.getCif());
            System.out.println("Precio copa: " + config.getPrecioCopa());
            
            // Cargar datos del club
            txtNombreClub.setText(config.getNombreClub() != null ? config.getNombreClub() : "");
            txtCIF.setText(config.getCif() != null ? config.getCif() : "");
            txtDireccion1.setText(config.getDireccion1() != null ? config.getDireccion1() : "");
            txtDireccion2.setText(config.getDireccion2() != null ? config.getDireccion2() : "");
            txtPrecioCopa.setText(String.valueOf(config.getPrecioCopa()));
            txtPrecioCerveza.setText(String.valueOf(config.getPrecioCerveza()));
            txtPrecioSinConsumicion.setText(String.valueOf(config.getPrecioSinConsumicion()));
            txtPrecioSoloTicket.setText(String.valueOf(config.getPrecioSoloTicket()));
            txtRutaLogo.setText(config.getRutaLogo() != null ? config.getRutaLogo() : "");
            
            // Cargar ruta del icono (solo el nombre del archivo)
            String rutaIcono = config.getRutaIcono();
            txtRutaIcono.setText(rutaIcono != null ? rutaIcono : "");
            
            // Actualizar vista previa del icono si existe
            if (rutaIcono != null && !rutaIcono.isEmpty()) {
                File iconoFile = new File("iconos/" + rutaIcono);
                if (iconoFile.exists()) {
                    actualizarVistaPrevia(iconoFile.getAbsolutePath(), lblVistaPreviaIcono);
                }
            }
            
            // Cargar opciones de impresión desde la base de datos
            checkImprimirTicket.setSelected(config.isImprimirTicket());
            checkMostrarPrecio.setSelected(config.isMostrarPrecio());
            checkImprimirVale.setSelected(config.isImprimirVale());
            txtFraseDelDia.setText(config.getFraseDelDia() != null ? config.getFraseDelDia() : "");
            txtCondicionesEntrada.setText(config.getCondicionesEntrada() != null ? config.getCondicionesEntrada() : "");
            txtCondicionesConsumicion.setText(config.getCondicionesConsumicion() != null ? config.getCondicionesConsumicion() : "");
            
            // Configurar impresora seleccionada si existe
            String impresoraSeleccionada = config.getImpresora();
            if (impresoraSeleccionada != null && !impresoraSeleccionada.isEmpty()) {
                comboImpresoras.setSelectedItem(impresoraSeleccionada);
            }
        } catch (Exception e) {
            System.err.println("Error al cargar la configuración desde la base de datos: " + e.getMessage());
            e.printStackTrace();
            JOptionPane.showMessageDialog(this, "Error al cargar la configuración: " + e.getMessage(), 
                    "Error de carga", JOptionPane.ERROR_MESSAGE);
        }
        
        // Cargar configuración de base de datos desde preferencias
        try {
            Preferences prefs = Preferences.userNodeForPackage(com.mycompany.tikets.view.DBConfigWindow.class);
            txtDbHost.setText(prefs.get("db_host", "localhost"));
            txtDbPort.setText(prefs.get("db_port", "3306"));
            txtDbName.setText(prefs.get("db_name", "tikets_db"));
            txtDbUser.setText(prefs.get("db_user", "root"));
            // Usar setPassword solo si la contraseña no está vacía para evitar problemas con arrays nulos
            String password = prefs.get("db_pass", "");
            if (!password.isEmpty()) {
                txtDbPassword.setText(password);
            }
            System.out.println("Configuración de base de datos cargada desde preferencias locales");
        } catch (Exception e) {
            System.err.println("Error al cargar configuración de base de datos: " + e.getMessage());
            // Usar valores por defecto
            txtDbHost.setText("localhost");
            txtDbPort.setText("3306");
            txtDbName.setText("tikets_db");
            txtDbUser.setText("root");
            // Usar setEchoChar para asegurar que no se muestre texto en el campo de contraseña
            txtDbPassword.setEchoChar('\u2022'); // Carácter de punto medio
            txtDbPassword.setText(""); // Está bien usar setText con cadena vacía
        }
        
        // Cargar imagen del logo si existe
        actualizarVistaPrevia();
    }
    
    /**
     * Guarda la configuración en la base de datos y en las preferencias locales
     * @return true si se guardó correctamente, false en caso contrario
     */
    private boolean guardarConfiguracion() {
        try {
            // 1. Guardar la configuración de la base de datos en preferencias locales
            guardarConfiguracionBaseDatos();
            
            // 2. Guardar el resto de la configuración en la base de datos
            guardarConfiguracionClub();
            
            JOptionPane.showMessageDialog(this, 
                "Configuración guardada correctamente.",
                "Guardado exitoso", 
                JOptionPane.INFORMATION_MESSAGE);
            return true;
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, 
                "Error al guardar la configuración: " + e.getMessage(),
                "Error", 
                JOptionPane.ERROR_MESSAGE);
            e.printStackTrace();
            return false;
        }
    }
    
    /**
     * Guarda la configuración de la base de datos en las preferencias locales
     */
    private void guardarConfiguracionBaseDatos() {
        try {
            Preferences prefs = Preferences.userNodeForPackage(com.mycompany.tikets.view.DBConfigWindow.class);
            prefs.put("db_host", txtDbHost.getText());
            prefs.put("db_port", txtDbPort.getText());
            prefs.put("db_name", txtDbName.getText());
            prefs.put("db_user", txtDbUser.getText());
            // Obtener la contraseña de forma segura
            prefs.put("db_pass", new String(txtDbPassword.getPassword()));
            prefs.flush(); // Asegurar que los cambios se guarden inmediatamente
            
            System.out.println("Configuración de la base de datos guardada en preferencias locales");
            System.out.println("Host: " + txtDbHost.getText());
            System.out.println("Puerto: " + txtDbPort.getText());
            System.out.println("Base de datos: " + txtDbName.getText());
            System.out.println("Usuario: " + txtDbUser.getText());
            
            // Reiniciar la conexión para aplicar los nuevos parámetros
            // Usar true para forzar la recarga de la configuración en la próxima conexión
            DatabaseConnection.closeConnection(true);
        } catch (Exception e) {
            System.err.println("Error al guardar configuración de base de datos: " + e.getMessage());
            e.printStackTrace();
            JOptionPane.showMessageDialog(this, "Error al guardar la configuración de la base de datos: " + e.getMessage(), 
                    "Error de guardado", JOptionPane.ERROR_MESSAGE);
        }
    }
    
    /**
     * Guarda la configuración del club en la base de datos
     */
    private void guardarConfiguracionClub() {
        try {
            // Obtener la instancia de configuración
            ConfiguracionTikets config = ConfiguracionTikets.getInstancia();
            
            // Actualizar los datos del club en el objeto de configuración
            config.setNombreClub(txtNombreClub.getText());
            config.setCif(txtCIF.getText());
            config.setDireccion1(txtDireccion1.getText());
            config.setDireccion2(txtDireccion2.getText());
            
            // Convertir y establecer los precios
            try {
                config.setPrecioCopa(Double.parseDouble(txtPrecioCopa.getText().replace(',', '.')));
                config.setPrecioCerveza(Double.parseDouble(txtPrecioCerveza.getText().replace(',', '.')));
                config.setPrecioSinConsumicion(Double.parseDouble(txtPrecioSinConsumicion.getText().replace(',', '.')));
                config.setPrecioSoloTicket(Double.parseDouble(txtPrecioSoloTicket.getText().replace(',', '.')));
            } catch (NumberFormatException e) {
                JOptionPane.showMessageDialog(this, "Formato de precio inválido. Asegúrate de usar números válidos.", 
                        "Error de formato", JOptionPane.ERROR_MESSAGE);
                return;
            }
            
            // Establecer la ruta del logo
            config.setRutaLogo(txtRutaLogo.getText());
            
            // Establecer la ruta del icono (solo el nombre del archivo)
            String nombreIcono = txtRutaIcono.getText().trim();
            if (!nombreIcono.isEmpty()) {
                // Verificar que el archivo existe en la carpeta de iconos
                File iconoFile = new File("iconos/" + nombreIcono);
                if (!iconoFile.exists()) {
                    JOptionPane.showMessageDialog(this, 
                        "El archivo de icono no existe en la carpeta 'iconos'.",
                        "Icono no encontrado", 
                        JOptionPane.WARNING_MESSAGE);
                    return;
                }
                config.setRutaIcono(nombreIcono);
            } else {
                config.setRutaIcono("");
            }
            
            // Establecer opciones de impresión
            config.setImprimirTicket(checkImprimirTicket.isSelected());
            config.setMostrarPrecio(checkMostrarPrecio.isSelected());
            config.setImprimirVale(checkImprimirVale.isSelected());
            config.setFraseDelDia(txtFraseDelDia.getText());
            config.setCondicionesEntrada(txtCondicionesEntrada.getText());
            config.setCondicionesConsumicion(txtCondicionesConsumicion.getText());
            
            // Establecer la impresora seleccionada
            String impresoraSeleccionada = (String) comboImpresoras.getSelectedItem();
            if (impresoraSeleccionada != null && !impresoraSeleccionada.equals("(Seleccionar al imprimir)")) {
                config.setImpresora(impresoraSeleccionada);
            } else {
                config.setImpresora("");
            }
            
            // Guardar en la base de datos
            boolean guardadoExitoso = config.guardarConfiguracion();
            
            if (guardadoExitoso) {
                JOptionPane.showMessageDialog(this, "Configuración guardada correctamente.", 
                        "Configuración guardada", JOptionPane.INFORMATION_MESSAGE);
            } else {
                JOptionPane.showMessageDialog(this, "Error al guardar la configuración en la base de datos.", 
                        "Error de guardado", JOptionPane.ERROR_MESSAGE);
            }
        } catch (Exception e) {
            System.err.println("Error al guardar configuración del club: " + e.getMessage());
            e.printStackTrace();
            JOptionPane.showMessageDialog(this, "Error al guardar la configuración: " + e.getMessage(), 
                    "Error de guardado", JOptionPane.ERROR_MESSAGE);
        }
        
        // Este código ha sido movido al método guardarConfiguracionBaseDatos()
        
        // Guardar configuración en las preferencias es suficiente
        // No necesitamos crear un objeto ConfiguracionClub aquí
        // ya que los listeners se encargarán de actualizar el modelo
    }
    
    /**
     * Abre un diálogo para seleccionar el logo del club
     */
    private void seleccionarLogo() {
        // Primero verificar y crear las carpetas necesarias
        if (!verificarYCrearCarpetas()) {
            JOptionPane.showMessageDialog(this, 
                    "No se pudieron crear las carpetas necesarias para logos e iconos.\n" +
                    "Algunas opciones pueden no estar disponibles.", 
                    "Advertencia", JOptionPane.WARNING_MESSAGE);
        }
        
        // Crear un panel con opciones
        JPanel optionsPanel = new JPanel();
        optionsPanel.setLayout(new GridLayout(3, 1, 10, 10));
        
        JButton btnSeleccionarLogo = new JButton("Seleccionar logo (carpeta logo)");
        JButton btnSeleccionarIcono = new JButton("Seleccionar icono (carpeta iconos)");
        JButton btnSeleccionarArchivo = new JButton("Seleccionar archivo de imagen");
        
        optionsPanel.add(btnSeleccionarLogo);
        optionsPanel.add(btnSeleccionarIcono);
        optionsPanel.add(btnSeleccionarArchivo);
        
        // Mostrar diálogo con opciones
        JDialog dialog = new JDialog(this, "Seleccionar Logo o Icono", true);
        dialog.setLayout(new BorderLayout());
        dialog.add(new JLabel("Seleccione una opción:", JLabel.CENTER), BorderLayout.NORTH);
        dialog.add(optionsPanel, BorderLayout.CENTER);
        dialog.pack();
        dialog.setLocationRelativeTo(this);
        
        // Acción para seleccionar logo de la carpeta logo
        btnSeleccionarLogo.addActionListener(e -> {
            dialog.dispose();
            seleccionarImagenDeCarpeta("logo");
        });
        
        // Acción para seleccionar icono de la carpeta iconos
        btnSeleccionarIcono.addActionListener(e -> {
            dialog.dispose();
            seleccionarImagenDeCarpeta("iconos");
        });
        
        // Acción para seleccionar archivo personalizado
        btnSeleccionarArchivo.addActionListener(e -> {
            dialog.dispose();
            seleccionarArchivoLogo();
        });
        
        dialog.setVisible(true);
    }
    
    /**
     * Muestra un diálogo para seleccionar una imagen de una carpeta específica
     * @param carpeta Nombre de la carpeta donde buscar imágenes
     */
    private void seleccionarImagenDeCarpeta(String carpeta) {
        String[] posiblesRutas = {
            "src/main/resources/" + carpeta,
            "target/classes/" + carpeta,
            "classes/" + carpeta,
            carpeta,
            System.getProperty("user.dir") + "/" + carpeta
        };
        
        File imageDir = null;
        for (String ruta : posiblesRutas) {
            File dir = new File(ruta);
            if (dir.exists() && dir.isDirectory()) {
                System.out.println("Directorio " + carpeta + " encontrado en: " + ruta);
                imageDir = dir;
                break;
            }
        }
        
        if (imageDir == null) {
            JOptionPane.showMessageDialog(this, 
                    "No se encontró la carpeta '" + carpeta + "'. Asegúrate de que exista.", 
                    "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        // Filtrar solo archivos de imagen
        File[] imageFiles = imageDir.listFiles((dir, name) -> {
            String lowerName = name.toLowerCase();
            return lowerName.endsWith(".jpg") || lowerName.endsWith(".jpeg") || 
                   lowerName.endsWith(".png") || lowerName.endsWith(".gif");
        });
        
        if (imageFiles == null || imageFiles.length == 0) {
            JOptionPane.showMessageDialog(this, 
                    "No se encontraron imágenes en la carpeta '" + carpeta + "'.", 
                    "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        // Crear panel con imágenes en miniatura
        JPanel imagePanel = new JPanel(new GridLayout(0, 3, 10, 10));
        imagePanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));
        
        for (File imgFile : imageFiles) {
            try {
                // Crear miniatura
                ImageIcon icon = new ImageIcon(imgFile.getAbsolutePath());
                Image img = icon.getImage();
                Image newImg = img.getScaledInstance(100, 100, Image.SCALE_SMOOTH);
                JLabel imgLabel = new JLabel(new ImageIcon(newImg));
                imgLabel.setToolTipText(imgFile.getName());
                imgLabel.setBorder(BorderFactory.createLineBorder(Color.GRAY));
                
                // Añadir listener para seleccionar la imagen
                imgLabel.addMouseListener(new MouseAdapter() {
                    @Override
                    public void mouseClicked(MouseEvent e) {
                        txtRutaLogo.setText(imgFile.getAbsolutePath());
                        actualizarVistaPrevia();
                        Window window = SwingUtilities.getWindowAncestor(imagePanel);
                        if (window != null) {
                            window.dispose();
                        }
                    }
                    
                    @Override
                    public void mouseEntered(MouseEvent e) {
                        imgLabel.setBorder(BorderFactory.createLineBorder(Color.BLUE, 2));
                    }
                    
                    @Override
                    public void mouseExited(MouseEvent e) {
                        imgLabel.setBorder(BorderFactory.createLineBorder(Color.GRAY));
                    }
                });
                
                imagePanel.add(imgLabel);
            } catch (Exception e) {
                System.err.println("Error al cargar imagen: " + imgFile.getName());
            }
        }
        
        // Crear scroll pane para el panel de imágenes
        JScrollPane scrollPane = new JScrollPane(imagePanel);
        scrollPane.setPreferredSize(new Dimension(400, 300));
        
        // Mostrar diálogo con imágenes
        JDialog dialog = new JDialog(this, "Seleccionar Logo Predefinido", true);
        dialog.setLayout(new BorderLayout());
        dialog.add(new JLabel("Haga clic en una imagen para seleccionarla:", JLabel.CENTER), BorderLayout.NORTH);
        dialog.add(scrollPane, BorderLayout.CENTER);
        
        JButton btnCancelar = new JButton("Cancelar");
        btnCancelar.addActionListener(e -> dialog.dispose());
        JPanel buttonPanel = new JPanel();
        buttonPanel.add(btnCancelar);
        dialog.add(buttonPanel, BorderLayout.SOUTH);
        
        dialog.pack();
        dialog.setLocationRelativeTo(this);
        dialog.setVisible(true);
    }
    
    /**
     * Abre un diálogo para seleccionar un archivo de imagen personalizado
     */
    private void seleccionarArchivoLogo() {
        // Verificar si la carpeta de imágenes existe, si no, crearla
        File carpetaImagenes = new File("img");
        if (!carpetaImagenes.exists()) {
            boolean creada = carpetaImagenes.mkdirs();
            if (!creada) {
                JOptionPane.showMessageDialog(this, 
                    "No se pudo crear la carpeta 'img' para guardar las imágenes.",
                    "Error", 
                    JOptionPane.ERROR_MESSAGE);
                return;
            }
        }
        
        JFileChooser fileChooser = new JFileChooser();
        fileChooser.setDialogTitle("Seleccionar Logo");
        
        // Establecer el directorio inicial en la carpeta de imágenes si existe
        fileChooser.setCurrentDirectory(carpetaImagenes);
        
        // Filtrar solo archivos de imagen
        FileNameExtensionFilter filter = new FileNameExtensionFilter(
            "Archivos de imagen (JPG, PNG, GIF)", "jpg", "jpeg", "png", "gif");
        fileChooser.setFileFilter(filter);
        
        int resultado = fileChooser.showOpenDialog(this);
        
        if (resultado == JFileChooser.APPROVE_OPTION) {
            File archivoSeleccionado = fileChooser.getSelectedFile();
            
            // Verificar que el archivo sea una imagen
            if (!esArchivoImagenValido(archivoSeleccionado)) {
                JOptionPane.showMessageDialog(this, 
                    "El archivo seleccionado no es una imagen válida.",
                    "Error", 
                    JOptionPane.ERROR_MESSAGE);
                return;
            }
            
            try {
                // Copiar el archivo a la carpeta de imágenes
                String nombreArchivo = archivoSeleccionado.getName();
                File destino = new File(carpetaImagenes, nombreArchivo);
                
                // Si el archivo ya existe, añadir un sufijo numérico
                int contador = 1;
                String nombreBase = nombreArchivo.substring(0, nombreArchivo.lastIndexOf('.'));
                String extension = nombreArchivo.substring(nombreArchivo.lastIndexOf('.'));
                
                while (destino.exists()) {
                    nombreArchivo = String.format("%s_%d%s", nombreBase, contador, extension);
                    destino = new File(carpetaImagenes, nombreArchivo);
                    contador++;
                }
                
                Files.copy(archivoSeleccionado.toPath(), destino.toPath(), 
                    java.nio.file.StandardCopyOption.REPLACE_EXISTING);
                
                // Actualizar el campo de texto con la ruta relativa
                txtRutaLogo.setText(nombreArchivo);
                
                // Mostrar vista previa
                mostrarVistaPrevia(destino, lblVistaPrevia);
                
            } catch (IOException ex) {
                JOptionPane.showMessageDialog(this, 
                    "Error al copiar la imagen: " + ex.getMessage(),
                    "Error", 
                    JOptionPane.ERROR_MESSAGE);
                ex.printStackTrace();
            }
        }
    }
    
    /**
     * Verifica si un archivo es una imagen válida
     * @param archivo Archivo a verificar
     * @return true si es una imagen válida, false en caso contrario
     */
    private boolean esArchivoImagenValido(File archivo) {
        if (archivo == null || !archivo.exists() || !archivo.isFile()) {
            return false;
        }
        
        String nombre = archivo.getName().toLowerCase();
        return nombre.endsWith(".jpg") || nombre.endsWith(".jpeg") || 
               nombre.endsWith(".png") || nombre.endsWith(".gif");
    }
    
    /**
     * Muestra una vista previa de una imagen en un JLabel
     * @param archivoImagen Archivo de la imagen a mostrar
     * @param label JLabel donde se mostrará la vista previa
     */
    private void mostrarVistaPrevia(File archivoImagen, JLabel label) {
        if (archivoImagen == null || !archivoImagen.exists()) {
            label.setIcon(null);
            label.setText("<html><div style='text-align: center;'>No hay imagen<br>seleccionada</div>");
            return;
        }
        
        try {
            // Cargar la imagen
            ImageIcon icono = new ImageIcon(archivoImagen.getAbsolutePath());
            
            // Redimensionar manteniendo la relación de aspecto
            int ancho = label.getWidth() > 0 ? label.getWidth() : 150;
            int alto = label.getHeight() > 0 ? label.getHeight() : 120;
            
            // Calcular dimensiones manteniendo la relación de aspecto
            int nuevoAncho = icono.getIconWidth();
            int nuevoAlto = icono.getIconHeight();
            
            if (nuevoAncho > ancho) {
                nuevoAlto = (nuevoAlto * ancho) / nuevoAncho;
                nuevoAncho = ancho;
            }
            
            if (nuevoAlto > alto) {
                nuevoAncho = (nuevoAncho * alto) / nuevoAlto;
                nuevoAlto = alto;
            }
            
            // Asegurarse de que no se haga la imagen demasiado pequeña
            if (nuevoAncho < 50) nuevoAncho = 50;
            if (nuevoAlto < 50) nuevoAlto = 50;
            
            // Escalar la imagen
            Image img = icono.getImage().getScaledInstance(nuevoAncho, nuevoAlto, Image.SCALE_SMOOTH);
            label.setIcon(new ImageIcon(img));
            label.setText("");
            
        } catch (Exception e) {
            label.setIcon(null);
            label.setText("<html><div style='text-align: center;'>Error al cargar<br>la imagen</div>");
            e.printStackTrace();
        }
    }
    
    /**
     * Actualiza la vista previa del logo
     */
    private void actualizarVistaPrevia() {
        String rutaLogo = txtRutaLogo.getText();
        if (rutaLogo != null && !rutaLogo.isEmpty()) {
            // Verificar si la ruta es relativa a la carpeta de imágenes
            File archivoLogo = new File(rutaLogo);
            if (!archivoLogo.isAbsolute()) {
                // Si es relativa, buscar en la carpeta de imágenes
                archivoLogo = new File("img/" + rutaLogo);
            }
            mostrarVistaPrevia(archivoLogo, lblVistaPrevia);
        } else {
            lblVistaPrevia.setIcon(null);
            lblVistaPrevia.setText("<html><div style='text-align: center;'>No hay imagen<br>seleccionada</div>");
        }
    }
    
    /**
     * Interfaz para notificar cambios en la configuración
     */
    public interface ConfiguracionListener {
        void configuracionActualizada();
    }
    
    /**
     * Añade un listener para notificar cambios en la configuración
     * @param listener El listener a añadir
     */
    public void addConfiguracionListener(ConfiguracionListener listener) {
        listeners.add(listener);
    }
    
    /**
     * Notifica a todos los listeners que la configuración ha cambiado
     */
    private void notificarCambios() {
        for (ConfiguracionListener listener : listeners) {
            listener.configuracionActualizada();
        }
    }
    
    /**
     * Actualiza la lista de impresoras disponibles en el combobox
     */
    private void actualizarListaImpresoras() {
        // Limpiar la lista actual
        comboImpresoras.removeAllItems();
        
        // Añadir opción para no seleccionar impresora
        comboImpresoras.addItem("(Seleccionar al imprimir)");
        
        // Obtener todas las impresoras disponibles
        PrintService[] services = PrintServiceLookup.lookupPrintServices(null, null);
        
        // Añadir cada impresora al combobox
        for (PrintService service : services) {
            comboImpresoras.addItem(service.getName());
        }
        
        // Seleccionar la impresora guardada en preferencias
        Preferences prefs = Preferences.userNodeForPackage(ConfiguracionWindow.class);
        String impresoraGuardada = prefs.get("impresora", "");
        if (!impresoraGuardada.isEmpty()) {
            for (int i = 0; i < comboImpresoras.getItemCount(); i++) {
                if (comboImpresoras.getItemAt(i).equals(impresoraGuardada)) {
                    comboImpresoras.setSelectedIndex(i);
                    break;
                }
            }
        }
    }
    
    /**
     * Verifica y crea las carpetas necesarias para logos e iconos
     * @return True si las carpetas existen o fueron creadas correctamente
     */
    private boolean verificarYCrearCarpetas() {
        // Verificar que existan las carpetas para logos e iconos
        String[] carpetasNecesarias = {"logo", "iconos"};
        String basePath = System.getProperty("user.dir");
        
        boolean todoOK = true;
        
        for (String carpeta : carpetasNecesarias) {
            File dir = new File(basePath + "/" + carpeta);
            if (!dir.exists()) {
                System.out.println("La carpeta '" + carpeta + "' no existe. Intentando crearla...");
                if (dir.mkdir()) {
                    System.out.println("Carpeta '" + carpeta + "' creada correctamente en: " + dir.getAbsolutePath());
                } else {
                    System.err.println("No se pudo crear la carpeta '" + carpeta + "'.");
                    JOptionPane.showMessageDialog(this,
                            "No se pudo crear la carpeta '" + carpeta + "'. \n" +
                            "Verifica que tienes permisos de escritura en: " + basePath,
                            "Error", JOptionPane.ERROR_MESSAGE);
                    todoOK = false;
                }
            } else {
                System.out.println("Carpeta '" + carpeta + "' encontrada en: " + dir.getAbsolutePath());
            }
        }
        
        return todoOK;
    }
}
