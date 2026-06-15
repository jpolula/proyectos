/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package com.mycompany.proyecto;

import javafx.scene.control.ListCell;

/**
 *
 * @author Juan Pedro
 */
public class Seleccion extends ListCell<String>
{
     @Override
    protected void updateItem(String item, boolean empty) {
        super.updateItem(item, empty);

        if (empty || item == null) 
        {
            setText(null);
            setStyle(""); // Limpiar cualquier estilo aplicado previamente
        } else {
            setText(item);

            // Establezco un estilo diferente para el elemento seleccionado
            if (isSelected()) 
            {
                // Establezco los estilos para el elemento seleccionado
                setStyle("-fx-background-color: black; -fx-text-fill: white;");
            } else {
                setStyle(""); // Limpiar el estilo si el elemento no está seleccionado
            }
        }
    }
}
