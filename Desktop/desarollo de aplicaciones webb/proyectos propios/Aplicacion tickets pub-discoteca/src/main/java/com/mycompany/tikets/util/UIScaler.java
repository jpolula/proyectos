package com.mycompany.tikets.util;

import java.awt.Component;
import java.awt.Dimension;
import java.awt.Font;
import java.awt.GraphicsDevice;
import java.awt.GraphicsEnvironment;
import javax.swing.JButton;
import javax.swing.JDialog;
import javax.swing.JFrame;
import javax.swing.JPanel;
import javax.swing.UIManager;
import javax.swing.plaf.FontUIResource;
import java.util.Enumeration;

/**
 * Clase de utilidad para escalar componentes de la interfaz de usuario
 */
public class UIScaler {
    
    // Factor de escala para todas las ventanas (1.3 = 30% más grande)
    private static final float SCALE_FACTOR = 1.3f;
    
    // Factor de escala para fuentes
    private static final float FONT_SCALE_FACTOR = 1.2f;
    
    // Factor de escala para botones principales
    private static final float MAIN_BUTTON_SCALE_FACTOR = 1.5f;
    
    /**
     * Escala una dimensión según el factor de escala global
     * @param width Ancho original
     * @param height Alto original
     * @return Dimensión escalada
     */
    public static Dimension getScaledDimension(int width, int height) {
        return new Dimension((int)(width * SCALE_FACTOR), (int)(height * SCALE_FACTOR));
    }
    
    /**
     * Aplica el escalado a un JFrame y lo pone en pantalla completa
     * @param frame El JFrame a escalar
     * @param originalWidth Ancho original
     * @param originalHeight Alto original
     */
    public static void scaleFrame(JFrame frame, int originalWidth, int originalHeight) {
        // Configurar para pantalla completa
        frame.setExtendedState(JFrame.MAXIMIZED_BOTH);
        
        // Como respaldo, también establecer un tamaño escalado
        Dimension scaledSize = getScaledDimension(originalWidth, originalHeight);
        frame.setSize(scaledSize);
        frame.setPreferredSize(scaledSize);
    }
    
    /**
     * Aplica el escalado a un JDialog y lo pone en pantalla completa
     * @param dialog El JDialog a escalar
     * @param originalWidth Ancho original
     * @param originalHeight Alto original
     */
    public static void scaleDialog(JDialog dialog, int originalWidth, int originalHeight) {
        // Configurar para pantalla completa
        GraphicsDevice device = GraphicsEnvironment.getLocalGraphicsEnvironment().getDefaultScreenDevice();
        Dimension screenSize = new Dimension(device.getDisplayMode().getWidth(), device.getDisplayMode().getHeight());
        dialog.setSize(screenSize);
        
        // Como respaldo, también establecer un tamaño escalado
        Dimension scaledSize = getScaledDimension(originalWidth, originalHeight);
        dialog.setPreferredSize(scaledSize);
    }
    
    /**
     * Aumenta el tamaño de fuente de un componente
     * @param component El componente al que se le aumentará la fuente
     */
    public static void scaleFont(Component component) {
        Font currentFont = component.getFont();
        int newSize = (int)(currentFont.getSize() * FONT_SCALE_FACTOR);
        Font newFont = new Font(currentFont.getName(), currentFont.getStyle(), newSize);
        component.setFont(newFont);
    }
    
    /**
     * Aumenta el tamaño de fuente de todos los componentes en un panel
     * @param panel El panel cuyos componentes se escalarán
     */
    public static void scaleFontsInPanel(JPanel panel) {
        for (Component comp : panel.getComponents()) {
            scaleFont(comp);
            if (comp instanceof JPanel) {
                scaleFontsInPanel((JPanel) comp);
            }
        }
    }
    
    /**
     * Escala un botón principal haciéndolo más grande
     * @param button El botón a escalar
     */
    public static void scaleMainButton(JButton button) {
        // Escalar la fuente
        Font currentFont = button.getFont();
        int fontNewSize = (int)(currentFont.getSize() * MAIN_BUTTON_SCALE_FACTOR);
        Font newFont = new Font(currentFont.getName(), Font.BOLD, fontNewSize);
        button.setFont(newFont);
        
        // Escalar el tamaño del botón
        Dimension currentSize = button.getPreferredSize();
        Dimension buttonNewSize = new Dimension(
            (int)(currentSize.width * MAIN_BUTTON_SCALE_FACTOR),
            (int)(currentSize.height * MAIN_BUTTON_SCALE_FACTOR)
        );
        button.setPreferredSize(buttonNewSize);
        button.setMinimumSize(buttonNewSize);
    }
    
    /**
     * Aumenta el tamaño de todas las fuentes en la aplicación
     */
    public static void scaleGlobalFonts() {
        Enumeration<Object> keys = UIManager.getDefaults().keys();
        while (keys.hasMoreElements()) {
            Object key = keys.nextElement();
            Object value = UIManager.get(key);
            if (value instanceof FontUIResource) {
                FontUIResource font = (FontUIResource) value;
                int newSize = (int)(font.getSize() * FONT_SCALE_FACTOR);
                UIManager.put(key, new FontUIResource(font.getName(), font.getStyle(), newSize));
            }
        }
    }
}
