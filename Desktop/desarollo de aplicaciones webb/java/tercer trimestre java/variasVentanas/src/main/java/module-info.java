module com.mycompany.variasventanas {
    requires javafx.controls;
    requires javafx.fxml;

    opens com.mycompany.variasventanas to javafx.fxml;
    exports com.mycompany.variasventanas;
}
