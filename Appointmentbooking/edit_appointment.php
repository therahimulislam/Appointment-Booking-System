<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];
$booking = null;

// 1. Handle Form Submission (When user clicks Save)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = (int) $_POST['booking_id'];
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);
    $details = $conn->real_escape_string($_POST['details']);

    // Fetch the doctor_id to check for conflicts
    $doc_query = $conn->query("SELECT doctor_id FROM bookings WHERE id='$booking_id' AND user_id='$user_id'");

    if ($doc_query->num_rows > 0) {
        $doctor_id = $doc_query->fetch_assoc()['doctor_id'];

        // Check if the new time slot is already taken by someone else
        $conflict_check = $conn->query("SELECT id FROM bookings WHERE date='$date' AND time='$time' AND doctor_id='$doctor_id' AND id != '$booking_id'");

        if ($conflict_check->num_rows > 0) {
            $error = "This time slot is already booked! Please choose another time.";
        } else {
            // Update the booking
            $update_sql = "UPDATE bookings SET date='$date', time='$time', details='$details' WHERE id='$booking_id'";
            if ($conn->query($update_sql) === TRUE) {
                $success = "Appointment updated successfully! <a href='appointments.php'>Return to My Appointments</a>.";
            } else {
                $error = "Error updating appointment: " . $conn->error;
            }
        }
    } else {
        $error = "Unauthorized action.";
    }
}

// 2. Fetch existing data to populate the form
if (isset($_GET['id']) && !$success) {
    $booking_id = (int) $_GET['id'];
    $sql = "SELECT b.*, d.name AS doctor_name, d.specialty FROM bookings b LEFT JOIN doctors d ON b.doctor_id = d.id WHERE b.id='$booking_id' AND b.user_id='$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $booking = $result->fetch_assoc();
    } else {
        header("Location: appointments.php");
        exit();
    }
} else if (!isset($_POST['booking_id']) && !$success) {
    header("Location: appointments.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment — CarePlus</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
</head>

<body class="app-page">

    <nav class="navbar glass-nav">
        <div class="container nav-content">
            <h2 class="nav-brand flex-align">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    style="margin-right:8px;">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                    <line x1="12" y1="11" x2="12" y2="15"></line>
                    <line x1="10" y1="13" x2="14" y2="13"></line>
                </svg>
                CarePlus
            </h2>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="appointments.php" class="active">My Appointments</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 center-content no-height">
        <div class="card glass">
            <h2>Edit Appointment</h2>

            <?php if ($error)
                echo "<div class='alert error'>$error</div>"; ?>
            <?php if ($success)
                echo "<div class='alert success'>$success</div>"; ?>

            <?php if (!$success && $booking): ?>
                <div class="info-row mb-4" style="background: #f9fafb; padding: 15px; border-radius: 8px;">
                    <p class="text-sm text-muted mb-2"><strong>Booking ID:</strong>
                        <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                    <p class="text-sm text-muted mb-2"><strong>Patient:</strong>
                        <?php echo htmlspecialchars($booking['patient_name']); ?></p>
                    <p class="text-sm text-muted"><strong>Doctor:</strong> Dr.
                        <?php echo htmlspecialchars($booking['doctor_name']); ?>
                        (<?php echo htmlspecialchars($booking['specialty']); ?>)</p>
                </div>

                <form action="edit_appointment.php" method="POST">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">

                    <div class="form-group">
                        <label>Reschedule Date:</label>
                        <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>"
                            value="<?php echo isset($_POST['date']) ? $_POST['date'] : $booking['date']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Reschedule Time:</label>
                        <select name="time" required>
                            <?php
                            $times = ['09:00:00' => '09:00 AM', '10:00:00' => '10:00 AM', '11:00:00' => '11:00 AM', '12:00:00' => '12:00 PM', '14:00:00' => '02:00 PM', '15:00:00' => '03:00 PM', '16:00:00' => '04:00 PM'];
                            $currentTime = isset($_POST['time']) ? $_POST['time'] : $booking['time'];

                            foreach ($times as $val => $label) {
                                $selected = ($val == $currentTime) ? 'selected' : '';
                                echo "<option value='$val' $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Update Details (Optional):</label>
                        <textarea name="details"
                            rows="3"><?php echo htmlspecialchars(isset($_POST['details']) ? $_POST['details'] : $booking['details']); ?></textarea>
                    </div>

                    <button type="submit" class="btn primary-btn mt-2 block-btn">Save Changes</button>
                    <a href="appointments.php" class="btn mt-2 block-btn text-center"
                        style="background:#e5e7eb; color:#374151;">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>