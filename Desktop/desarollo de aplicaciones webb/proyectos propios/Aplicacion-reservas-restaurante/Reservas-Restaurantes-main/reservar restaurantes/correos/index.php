<?php
                    // Listar todos los correos simulados
                    $correos = glob(__DIR__ . '/*.html');
                    rsort($correos); // Ordenar por fecha (más reciente primero)
                    
                    echo '<h1>Correos Simulados</h1>';
                    echo '<p>Estos correos han sido guardados localmente en lugar de ser enviados.</p>';
                    
                    if (empty($correos)) {
                        echo '<p>No hay correos simulados.</p>';
                    } else {
                        echo '<ul>';
                        foreach ($correos as $correo) {
                            $nombre = basename($correo);
                            $fecha = substr($nombre, 0, 19);
                            $fecha = str_replace('_', ' ', $fecha);
                            echo '<li><a href="' . $nombre . '">' . $fecha . '</a></li>';
                        }
                        echo '</ul>';
                    }
                    