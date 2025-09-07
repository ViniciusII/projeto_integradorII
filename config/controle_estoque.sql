CREATE DATABASE IF NOT EXISTS controle_estoque CHARACTER SET utf8 COLLATE utf8_general_ci;
USE controle_estoque;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    quantidade INT NOT NULL DEFAULT 0,
    preco DECIMAL(10,2) NOT NULL
);


CREATE TABLE operators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created DATETIME,
    modified DATETIME
);