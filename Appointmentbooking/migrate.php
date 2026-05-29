<?php
require 'db_connect.php';

// 1. Create doctors table
$create_doctors_table = "CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

if ($conn->query($create_doctors_table) === TRUE) {
    echo "Doctors table checked/created successfully.\n";
} else {
    die("Error creating doctors table: " . $conn->error . "\n");
}

// 2. Seed/update doctors list
$seed_query = "INSERT INTO doctors (name, specialty, email, phone) VALUES 
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
    ('Alice Johnson', 'Dentist', 'alice.johnson@careplus.com', '+1 (555) 020-8899')
ON DUPLICATE KEY UPDATE 
    name=VALUES(name),
    specialty=VALUES(specialty),
    phone=VALUES(phone)";

if ($conn->query($seed_query) === TRUE) {
    echo "Doctors seeded/updated successfully.\n";
} else {
    echo "Error seeding doctors: " . $conn->error . "\n";
}

// 3. Alter bookings table to add doctor_id if it doesn't exist
$check_col = $conn->query("SHOW COLUMNS FROM bookings LIKE 'doctor_id'");
if ($check_col->num_rows == 0) {
    // Add column
    $alter_sql = "ALTER TABLE bookings ADD COLUMN doctor_id INT NOT NULL AFTER user_id";
    if ($conn->query($alter_sql) === TRUE) {
        echo "doctor_id column added to bookings.\n";
        
        // Seed a default doctor_id for any existing bookings to avoid constraint violation
        $first_doc_query = $conn->query("SELECT id FROM doctors LIMIT 1");
        if ($first_doc_query && $first_doc_query->num_rows > 0) {
            $first_doc_id = $first_doc_query->fetch_assoc()['id'];
            $conn->query("UPDATE bookings SET doctor_id = '$first_doc_id'");
            echo "Assigned doctor ID $first_doc_id to existing bookings.\n";
        }
        
        // Add foreign key constraint
        $fk_sql = "ALTER TABLE bookings ADD FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE";
        if ($conn->query($fk_sql) === TRUE) {
            echo "Foreign key constraint added to bookings.\n";
        } else {
            echo "Error adding foreign key constraint: " . $conn->error . "\n";
        }
    } else {
        echo "Error adding doctor_id column: " . $conn->error . "\n";
    }
} else {
    echo "doctor_id column already exists in bookings.\n";
}

echo "Migration finished successfully.\n";
?>
