CREATE DATABASE IF NOT EXISTS proy1v1procesoregistro;

USE proy1v1procesoregistro;

CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(255) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE members 
ADD COLUMN rol ENUM('cliente', 'empleado') DEFAULT 'cliente' AFTER pass; 