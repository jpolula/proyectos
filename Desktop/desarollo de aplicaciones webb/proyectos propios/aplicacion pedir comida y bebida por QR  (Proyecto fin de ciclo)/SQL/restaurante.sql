DROP DATABASE IF EXISTS Restaurante;
CREATE DATABASE Restaurante;
USE Restaurante;

CREATE TABLE Categorias (
    codCategoria INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(40),
    Descripcion VARCHAR(100)
) ENGINE=InnoDB;

/*INSERCIONES QUE PONGO DE PRUEBA PARA VER COMO SE VE LA PAGINA*/
INSERT INTO Categorias (Nombre, Descripcion) VALUES
  ('Tapas', 'Tapas variadas'),
  ('Cafés', 'Cafés y bebidas calientes'),
  ('Refrescos', 'Refrescos y bebidas sin alcohol'),
  ('Bebidas alcohólicas', 'Cervezas, vinos y licores'),
  ('Postre', 'Postres dulces'),
  ('Raciones', 'Raciones para compartir');


CREATE TABLE Mesas (
    numMesa VARCHAR(10) PRIMARY KEY,
    estado ENUM('pendiente', 'preparando', 'listo', 'servido') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Empleados (
    codEmpleado INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(40),
    Correo VARCHAR(60),
    Telefono VARCHAR(10),
    Rol ENUM('administrador', 'camarero','cocinero','barra') NOT NULL
) ENGINE=InnoDB;

/*INSERCIONES DE PRUEBA PARA LOS EMPLEADOS*/
INSERT INTO Empleados (Nombre, Correo, Telefono, Rol) VALUES
  ('Ana Martínez', 'ana@gmail.com', '600123456', 'administrador'),
  ('Luis Pérez', 'luis@gmail.com', '600234567', 'camarero'),
  ('María López', 'maria@gmail.com', '600345678', 'cocinero'),
  ('Jorge Ruiz', 'jorge@gmail.com', '600456789', 'barra');

CREATE TABLE Productos (
    codProducto INT PRIMARY KEY AUTO_INCREMENT,
    Foto VARCHAR(255),
    codCategoria INT NOT NULL,
    Posicion VARCHAR(40),
    Nombre VARCHAR(40),
    Descripcion VARCHAR(100),
    Precio DECIMAL(10,2),
    QuienLoAtiende VARCHAR(40),
    Stock INT NOT NULL,
    FOREIGN KEY (codCategoria) REFERENCES Categorias(codCategoria)
) ENGINE=InnoDB;

/* INSERCIONES DE PRUEBA PARA LOS PRODUCTOS */
INSERT INTO Productos (Foto, codCategoria, Posicion, Nombre, Descripcion, Precio, QuienLoAtiende, Stock) VALUES
  ('tapa1.jpg', 1, 'Barra', 'Tortilla de patatas', 'Tapa clásica española', 2.50, 'barra', 20),
  ('tapa2.jpg', 1, 'Barra', 'Croquetas caseras', 'Croquetas de jamón', 3.00, 'barra', 15),
  ('cafe1.jpg', 2, 'Mesa', 'Café solo', 'Café espresso', 1.20, 'camarero', 50),
  ('cafe2.jpg', 2, 'Mesa', 'Café con leche', 'Café con leche cremosa', 1.50, 'camarero', 40),
  ('refresco1.jpg', 3, 'Barra', 'Coca-Cola', 'Refresco de cola', 1.80, 'barra', 30),
  ('refresco2.jpg', 3, 'Barra', 'Agua mineral', 'Botella de agua', 1.00, 'barra', 25),
  ('alcohol1.jpg', 4, 'Barra', 'Cerveza', 'Cerveza nacional', 2.00, 'barra', 35),
  ('alcohol2.jpg', 4, 'Barra', 'Vino tinto', 'Copa de vino tinto', 2.50, 'barra', 20),
  ('postre1.jpg', 5, 'Mesa', 'Tarta de queso', 'Porción de tarta de queso', 3.50, 'camarero', 10),
  ('postre2.jpg', 5, 'Mesa', 'Helado', 'Helado de vainilla', 2.00, 'camarero', 12),
  ('racion1.jpg', 6, 'Mesa', 'Calamares', 'Ración de calamares a la romana', 8.00, 'cocinero', 8),
  ('racion2.jpg', 6, 'Mesa', 'Patatas bravas', 'Ración de patatas bravas', 5.00, 'cocinero', 10);

CREATE TABLE Pedidos (
    codPedido INT PRIMARY KEY AUTO_INCREMENT,
    numMesa VARCHAR(10) NOT NULL,
    Fecha DATETIME,
    Observaciones VARCHAR(200) NULL,
    FOREIGN KEY (numMesa) REFERENCES Mesas(numMesa)
) ENGINE=InnoDB;

CREATE TABLE DetallePedidos (
    codDetallePedido INT NOT NULL,
    codPedido INT NOT NULL,
    codProducto INT,
    Cantidad INT,
    totalParcial DECIMAL(10,2),
    Estado VARCHAR(20),
    Observaciones VARCHAR(200) NULL,
    PRIMARY KEY (codDetallePedido),
    FOREIGN KEY (codPedido) REFERENCES Pedidos(codPedido),
    FOREIGN KEY (codProducto) REFERENCES Productos(codProducto)
) ENGINE=InnoDB;

CREATE TABLE EmpleadoDetallesPedidos (
    codEmpleado INT NOT NULL,
    Fecha DATETIME NOT NULL,
    codDetallePedido INT NOT NULL,
    cambioEstado ENUM('pendiente', 'preparando', 'listo', 'servido', 'cancelado') NOT NULL,
    PRIMARY KEY (codEmpleado, Fecha, codDetallePedido),
    FOREIGN KEY (codEmpleado) REFERENCES Empleados(codEmpleado),
    FOREIGN KEY (codDetallePedido) REFERENCES DetallePedidos(codDetallePedido)
) ENGINE=InnoDB;