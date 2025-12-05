-- Create the database
CREATE DATABASE IF NOT EXISTS dolphin_crm;
USE dolphin_crm;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS Notes;
DROP TABLE IF EXISTS Contacts;
DROP TABLE IF EXISTS Users;

-- Users table
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Contacts table
CREATE TABLE Contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50),
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    telephone VARCHAR(20),
    company VARCHAR(200),
    type VARCHAR(50) NOT NULL,
    assigned_to INT,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES Users(id),
    FOREIGN KEY (created_by) REFERENCES Users(id)
);

-- Notes table
CREATE TABLE Notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES Contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES Users(id)
);

-- Insert admin user
-- Password is 'password123' hashed
INSERT INTO Users (firstname, lastname, password, email, role) 
VALUES ('Admin', 'User', '$2y$10$6X/9Qh9Z7eKKZ8N.vJQQfOQ7sL8sL8sL8sL8sL8sL8sL8sL8sL8sL', 'admin@project2.com', 'Admin');