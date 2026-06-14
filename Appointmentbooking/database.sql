
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
 ('Jethalal', 'Cardiologist', 'jethalal@careplus.com', '+1 (555) 019-2834'),
    ('Babita Jii', 'Cardiologist', 'babita@careplus.com', '+1 (555) 019-5829'),
    ('Choota Bheem', 'Pediatrician', 'bheem@careplus.com', '+1 (555) 014-3920'),
    ('Sophia Patel', 'Pediatrician', 'sophia.patel@careplus.com', '+1 (555) 014-8833'),
    ('Jhatkaram', 'Dermatologist', 'jhatka@careplus.com', '+1 (555) 017-4829'),
    ('Donald Pandey', 'Dermatologist', 'pandey@careplus.com', '+1 (555) 017-9012'),
    ('Osama Bin Laden', 'General Physician', '9_11@careplus.com', '+1 (555) 012-9843'),
    ('Motuu', 'General Physician', 'Motu@careplus.com', '+1 (555) 012-2290'),
    ('Patlu from phurphurinagar', 'Neurologist', 'phurphurinagar@careplus.com', '+1 (555) 018-8839'),
    ('Raja Inderverma', 'Neurologist', 'indraverma@careplus.com', '+1 (555) 018-4433'),
    ('Chutki', 'Orthopedic Surgeon', 'chutki@careplus.com', '+1 (555) 011-3829'),
    ('Mia Khalifa', 'Orthopedic Surgeon', 'khalifa@careplus.com', '+1 (555) 011-9922'),
    ('Sanwas Uddin', 'General Nurse', 'uddin@careplus.com', '+1 (555) 015-1100'),
    ('Indumati', 'Ophthalmologist', 'indumati@careplus.com', '+1 (555) 015-9988'),
    ('Manoj Kumar', 'Psychiatrist', 'manojkumar@careplus.com', '+1 (555) 016-3344'),
    ('Sallu Bhai', 'Psychiatrist', 'sallu@careplus.com', '+1 (555) 016-5566'),
    ('Vikash Nau', 'Gynecologist', 'Vikashnau@careplus.com', '+1 (555) 013-1122'),
    ('Ranchor Das', 'Gynecologist', 'randi@careplus.com', '+1 (555) 013-7788'),
    ('Travis Head', 'Dentist', 'travishead@careplus.com', '+1 (555) 020-4455'),
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