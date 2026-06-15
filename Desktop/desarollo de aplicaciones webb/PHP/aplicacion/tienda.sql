CREATE DATABASE IF NOT EXISTS tienda;
USE tienda;
	
DROP TABLE IF EXISTS moviles;
DROP TABLE IF EXISTS clientes;

create table clientes
(
    codigoCliente int not null auto_increment,
    nombre varchar(30) not null,
    apellido1 varchar(30) not null,
    apellido2 varchar(30) not null,
    DNI varchar(10) unique,
    primary key(codigoCliente)
);

create table moviles(
    codigoMovil int not null auto_increment,
    marca varchar(30) not null,
    modelo varchar(30) not null,
    precio float not null,
    codigoCliente int null,
    primary key(codigoMovil),
    foreign key(codigoCliente) references clientes(codigoCliente)
);

