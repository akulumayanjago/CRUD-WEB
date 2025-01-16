-- Buat database
CREATE DATABASE barang_db;

-- Pilih database
USE barang_db;

-- Buat tabel
CREATE TABLE barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT NOT NULL
);

