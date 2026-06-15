/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.variasventanas;

import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.stage.Stage;

/**
 *
 * @author Cifu
 */
public class Secondary1Controller 
{
    @FXML
    Button cerrar;
    
    @FXML
    private void cerrar()
    {
        // get a handle to the stage
        Stage stage = (Stage) cerrar.getScene().getWindow();
        
        stage.close();
    }
    
}
