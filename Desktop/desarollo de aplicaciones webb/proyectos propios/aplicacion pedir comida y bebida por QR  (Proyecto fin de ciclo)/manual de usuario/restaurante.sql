DROP DATABASE IF EXISTS Restaurante;
CREATE DATABASE Restaurante;
USE Restaurante;

CREATE TABLE Categorias (
    codCategoria INT PRIMARY KEY AUTO_INCREMENT,
    Tipo VARCHAR(40),
    Nombre VARCHAR(40),
    Categoria VARCHAR(40)
) ENGINE=InnoDB;

CREATE TABLE Mesas (
    numMesa VARCHAR(10) PRIMARY KEY,
    estado ENUM('pendiente', 'preparando', 'listo', 'servido', 'cancelado') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Empleados (
    codEmpleado INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(40),
    Correo VARCHAR(60),
    Telefono VARCHAR(10),
    Rol VARCHAR(20)
) ENGINE=InnoDB;

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