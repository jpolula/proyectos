module com.mycompany.proyectoenblanco {
    requires javafx.controls;
    requires javafx.fxml;

    opens com.mycompany.proyectoenblanco to javafx.fxml;
    exports com.mycompany.proyectoenblanco;
}
