<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);
    $patient_name = $conn->real_escape_string($_POST['patient_name']);
    $details = $conn->real_escape_string($_POST['details']);
    $doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
    $user_id = $_SESSION['user_id'];

    if(empty($date) || empty($time) || empty($patient_name) || empty($doctor_id)) {
        $error = "Name, date, time, and doctor selection are required!";
    } else {
        // Check if slot already booked for this specific doctor
        $check = $conn->query("SELECT * FROM bookings WHERE date='$date' AND time='$time' AND doctor_id='$doctor_id'");
        if ($check->num_rows > 0) {
            $error = "This time slot is already booked for the selected doctor! Please choose another time or doctor.";
        } else {
            $booking_id = 'CP-' . strtoupper(substr(uniqid(), -6));
            $sql = "INSERT INTO bookings (user_id, doctor_id, booking_id, patient_name, date, time, details) VALUES ('$user_id', '$doctor_id', '$booking_id', '$patient_name', '$date', '$time', '$details')";
            if ($conn->query($sql) === TRUE) {
                $success = "Appointment Booked Successfully! Your Booking ID is <strong>$booking_id</strong>. View your <a href='appointments.php'>appointments here</a>.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Appointment Booking</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-bg">

    <nav class="navbar glass-nav">
        <div class="container nav-content">
            <h2 class="nav-brand flex-align">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><line x1="12" y1="11" x2="12" y2="15"></line><line x1="10" y1="13" x2="14" y2="13"></line></svg>
                CarePlus
            </h2>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="appointments.php">My Appointments</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 center-content no-height">
        <div class="card glass">
            <h2>Book an Appointment</h2>
            
            <?php if($error) echo "<div class='alert error'>$error</div>"; ?>
            <?php if($success) echo "<div class='alert success'>$success</div>"; ?>
            
            <?php if(!$success): ?>
            <form action="book.php" method="POST" id="bookingForm">
                <div class="form-group">
                    <label>Patient Name:</label>
                    <input type="text" name="patient_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                </div>

                <?php
                // Fetch all doctors and build specialty list
                $doctors_data = [];
                $specialties = [];
                $doctors_res = $conn->query("SELECT id, name, specialty FROM doctors ORDER BY name ASC");
                if ($doctors_res) {
                    while($doc = $doctors_res->fetch_assoc()) {
                        $doctors_data[] = $doc;
                        if (!in_array($doc['specialty'], $specialties)) {
                            $specialties[] = $doc['specialty'];
                        }
                    }
                }
                sort($specialties);
                ?>

                <div class="form-group">
                    <label>Select Specialty (Profession):</label>
                    <select name="specialty" id="specialty" required>
                        <option value="">-- Select Specialty --</option>
                        <?php
                        foreach($specialties as $spec) {
                            echo "<option value='" . htmlspecialchars($spec) . "'>" . htmlspecialchars($spec) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Doctor:</label>
                    <select name="doctor_id" id="doctor_id" required disabled>
                        <option value="">-- Select Doctor --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="date" id="date" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Time Slot:</label>
                    <select name="time" id="time" required>
                        <option value="">-- Select Time --</option>
                        <option value="09:00:00">09:00 AM</option>
                        <option value="10:00:00">10:00 AM</option>
                        <option value="11:00:00">11:00 AM</option>
                        <option value="12:00:00">12:00 PM</option>
                        <option value="14:00:00">02:00 PM</option>
                        <option value="15:00:00">03:00 PM</option>
                        <option value="16:00:00">04:00 PM</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Additional Details (Optional):</label>
                    <textarea name="details" rows="3" placeholder="Any specific requirements..."></textarea>
                </div>

                <button type="submit" class="btn primary-btn mt-2 block-btn">Confirm Booking</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const doctors = <?php echo json_encode($doctors_data); ?>;
            const specialtySelect = document.getElementById('specialty');
            const doctorSelect = document.getElementById('doctor_id');

            specialtySelect.addEventListener('change', function() {
                const selectedSpecialty = this.value;
                
                // Clear existing options except the first one
                doctorSelect.innerHTML = '<option value="">-- Select Doctor --</option>';
                
                if (selectedSpecialty) {
                    // Filter doctors by specialty
                    const filteredDoctors = doctors.filter(doc => doc.specialty === selectedSpecialty);
                    
                    // Populate options
                    filteredDoctors.forEach(doc => {
                        const opt = document.createElement('option');
                        opt.value = doc.id;
                        opt.textContent = 'Dr. ' + doc.name;
                        doctorSelect.appendChild(opt);
                    });
                    
                    doctorSelect.disabled = false;
                } else {
                    doctorSelect.disabled = true;
                }
            });
        });
    </script>
</body>
</html>