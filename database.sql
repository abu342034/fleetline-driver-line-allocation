-- FleetLine Database Schema
CREATE DATABASE IF NOT EXISTS fleetline_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fleetline_db;

DROP TABLE IF EXISTS allocations;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS drivers;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','driver') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_no VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT NULL,
    status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    approved_by INT NULL DEFAULT NULL,
    rejection_reason TEXT NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate_no VARCHAR(20) UNIQUE NOT NULL,
    model VARCHAR(80) NOT NULL,
    capacity INT DEFAULT 0,
    status ENUM('active','maintenance','inactive') DEFAULT 'active'
);

CREATE TABLE routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(120) NOT NULL,
    origin VARCHAR(120) NOT NULL,
    destination VARCHAR(120) NOT NULL,
    distance_km DECIMAL(6,2) DEFAULT 0
);

CREATE TABLE allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    route_id INT NOT NULL,
    allocation_date DATE NOT NULL,
    allocation_time TIME NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

-- Seed Users (passwords: Admin@12345 / Driver@12345 - bcrypt hashed)
INSERT INTO users (full_name, email, password, role) VALUES
('System Administrator', 'admin@system.com', '$2y$10$E8t7xJkXr3yQ7O6c4GZ9zeZ0kE5Y9Q4o2Hh1n9vJh1f9Q2b0V3r4u', 'admin'),
('John Driver', 'driver@system.com', '$2y$10$E8t7xJkXr3yQ7O6c4GZ9zeZ0kE5Y9Q4o2Hh1n9vJh1f9Q2b0V3r4u', 'driver');

-- NOTE: Run setup_passwords.php once after import to set correct bcrypt hashes.

-- Test driver is pre-approved so existing logins keep working
INSERT INTO drivers (user_id, license_no, phone, address, status, approved_at, approved_by) VALUES
(2, 'KN-DRV-2024-001', '07047327262', 'Wudil, Kano State', 'Approved', CURRENT_TIMESTAMP, 1);

INSERT INTO vehicles (plate_no, model, capacity, status) VALUES
('KN-123-AB', 'Toyota Hiace', 14, 'active'),
('KN-456-CD', 'Mercedes Sprinter', 18, 'active'),
('KN-789-EF', 'Ford Transit', 12, 'active');

INSERT INTO routes (route_name, origin, destination, distance_km) VALUES
('Wudil Express', 'Wudil', 'Kano City', 42.5),
('Campus Shuttle', 'ADUSTECH Campus', 'Wudil Town', 8.0),
('Northern Line', 'Kano City', 'Dambatta', 65.0);
