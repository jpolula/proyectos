/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.variasventanas;


import javafx.fxml.FXML;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.stage.Stage;


public class Secondary3Controller 
{
    @FXML
    private Button cerrar;
    @FXML
    private Label lblRecibido;
    @FXML
    private Label lblRandom;
    
 
    
    @FXML 
    public void initialize()
    //este método es llamado tras el constructor y después de haber
    //inicializado cualquier atributo @FXML
    //Los atributos @FXML no son accesibles desde el constructor puesto que todavía no tienen memoria asociada
    {
       
        Singleton intercambio = Singleton.getInstancia();
            
        lblRecibido.setText("Me han enviado el siguiente valor: " + Integer.toString(intercambio.getDato()));
        
        //colocamos en un label un valor aleatorio que será devuelto al cerrar
        
        lblRandom.setText(Integer.toString((int)(Math.random()*10 + 1)));    
    }
    
    @FXML
    private void cerrar()
    {
        
         Singleton intercambio = Singleton.getInstancia();
         
         intercambio.setDato(Integer.valueOf(lblRandom.getText()));
         
        //cerramos la ventana actual a través de algún control de la misma
        Stage stage = (Stage) cerrar.getScene().getWindow();
        
        stage.close();
    }
}
