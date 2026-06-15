package com.mycompany.proyectoenblanco;

import javafx.fxml.FXML;
import javafx.scene.control.Label;

public class PrimaryController {

    @FXML
    private Label lbl;
    
    @FXML
    private void initialize()
    //método que es llamado internamente justo después del constructor
    //En el constructor NO SE tiene acceso a las variables enlazadas con @FXML
    //En initialize() ya están las variables creadas y son accesibles
    {
        String cadena = "Para reutilizar este proyecto:\n\n"
                + "DESDE EL SISTEMA OPERATIVO:\n\n"
                + "0. (Opcional) Hacer una copia de la carpeta completa usando otro nombre\n\n"
                + "DESDE NETBEANS:\n\n"
                + "1. Cambiar nombre al proyecto con la opción 'rename' (botón derecho sobre él; cambiar las tres opciones)\n\n"
                + "2. Hacer refactor->rename del paquete com.mycompany.proyectoenblanco\n\n"
                + "    2.1. En Source Packages\n\n"
                + "    2.2. En Other Sources -> src/main/resources\n\n"
                + "3. En POM.XML actualizar la etiqueta <MainClass></MainClass>\n\n"
                + "4. En PRIMARY.FXML actualizar la etiqueta <Pane> para que apunte al controlador adecuado\n\n"
                + "5. En module-info.java (<default package>), actualizar la primera línea (module...)";
        
        lbl.setText(cadena);
    }
    
}
