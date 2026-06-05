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
