DROP DATABASE IF EXISTS consultas;
CREATE DATABASE consultas CHARACTER SET utf8mb4;
USE consultas;
CREATE TABLE psicologos(
    NumColegiado VARCHAR(9) PRIMARY KEY,
    DNI VARCHAR(9) NOT NULL,
    Nombre VARCHAR(20) NOT NULL,
    Apellido1 VARCHAR(20) NOT NULL,
    Apellido2 VARCHAR(20) NOT NULL,
    AnioTitulacion YEAR NOT NULL
);

CREATE TABLE pacientes(
    DNI VARCHAR(9) NOT NULL,
    Nombre VARCHAR(20) NOT NULL,
    Apellido1 VARCHAR(20) NOT NULL,
    Apellido2 VARCHAR(20) NOT NULL,
    FechaNacimiento DATE NOT NULL,
    FechaPrimeraConsulta DATE NULL,
    FechaUltimaConsulta DATE NULL,
    NumColegiado VARCHAR(9) NOT NULL,
    FOREIGN KEY (NumColegiado) REFERENCES psicologos(NumColegiado) ON DELETE CASCADE ON UPDATE CASCADE
);