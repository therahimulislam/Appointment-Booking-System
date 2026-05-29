-- In Xampp use this to create database--
CREATE DATABASE IF NOT EXISTS appointment_db;

USE appointment_db;

DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO doctors (name, specialty, email, phone) VALUES 
('Sarah Jenkins', 'Cardiologist', 'sarah.jenkins@careplus.com', '+1 (555) 019-2834'),
('Robert Chen', 'Cardiologist', 'robert.chen@careplus.com', '+1 (555) 019-5829'),
('Michael Chang', 'Pediatrician', 'michael.chang@careplus.com', '+1 (555) 014-3920'),
('Sophia Patel', 'Pediatrician', 'sophia.patel@careplus.com', '+1 (555) 014-8833'),
('Emily Rodriguez', 'Dermatologist', 'emily.rodriguez@careplus.com', '+1 (555) 017-4829'),
('Alan Smith', 'Dermatologist', 'alan.smith@careplus.com', '+1 (555) 017-9012'),
('David Patel', 'General Physician', 'david.patel@careplus.com', '+1 (555) 012-9843'),
('Lisa Warren', 'General Physician', 'lisa.warren@careplus.com', '+1 (555) 012-2290'),
('James Wilson', 'Neurologist', 'james.wilson@careplus.com', '+1 (555) 018-8839'),
('Elena Rostova', 'Neurologist', 'elena.rostova@careplus.com', '+1 (555) 018-4433'),
('Marcus Vance', 'Orthopedic Surgeon', 'marcus.vance@careplus.com', '+1 (555) 011-3829'),
('Diana Prince', 'Orthopedic Surgeon', 'diana.prince@careplus.com', '+1 (555) 011-9922'),
('William Green', 'Ophthalmologist', 'william.green@careplus.com', '+1 (555) 015-1100'),
('Grace Hopper', 'Ophthalmologist', 'grace.hopper@careplus.com', '+1 (555) 015-9988'),
('Andrew Taylor', 'Psychiatrist', 'andrew.taylor@careplus.com', '+1 (555) 016-3344'),
('Karen Miller', 'Psychiatrist', 'karen.miller@careplus.com', '+1 (555) 016-5566'),
('Maria Santos', 'Gynecologist', 'maria.santos@careplus.com', '+1 (555) 013-1122'),
('Jessica Taylor', 'Gynecologist', 'jessica.taylor@careplus.com', '+1 (555) 013-7788'),
('Thomas Wayne', 'Dentist', 'thomas.wayne@careplus.com', '+1 (555) 020-4455'),
('Alice Johnson', 'Dentist', 'alice.johnson@careplus.com', '+1 (555) 020-8899');

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    booking_id VARCHAR(20) NOT NULL UNIQUE,
    patient_name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);