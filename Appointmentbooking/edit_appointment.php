<?php
session_start();
require_once 'db_connect.php'; // Ensure this points to your actual database connection file

// Kick out unauthenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$appointment = null;

// 1. Fetch the existing appointment data
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        die("Error: Appointment not found or you don't have permission to edit it.");
    }
}

// 2. Handle the Form Submission (Updating the database)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_date = trim($_POST['date']);
    $new_time = trim($_POST['time']);
    $new_doctor = trim($_POST['doctor_name']);
    $new_details = trim($_POST['details']);
    $appointment_id = $_POST['appointment_id'];

    try {
        $update_stmt = $pdo->prepare("UPDATE appointments SET date = ?, time = ?, doctor_name = ?, details = ? WHERE id = ? AND user_id = ?");
        $update_stmt->execute([$new_date, $new_time, $new_doctor, $new_details, $appointment_id, $_SESSION['user_id']]);
        
        $success = "Appointment successfully updated!";
        
        // Refresh the variable with the new data
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ?");
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch();
        
    } catch (PDOException $e) {
        $error = "Failed to update appointment. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - CarePlus</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 768px)" href="mobile.css?v=<?php echo time(); ?>">
</head>
<body class="center-content landing-bg">

    <div class="card glass">
        <h2 style="text-align: center; margin-bottom: 20px;">Edit Appointment</h2>

        <?php if ($error): ?>
            <div class="alert error" style="background: #fee2e2; color: #ef4444; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success" style="background: #dcfce3; color: #22c55e; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($appointment): ?>
        <form action="edit_appointment.php?id=<?php echo $appointment['id']; ?>" method="POST">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">

            <div class="form-group">
                <label>Doctor:</label>
                <select name="doctor_name" required>
                    <option value="Dr. Smith" <?php if($appointment['doctor_name'] == 'Dr. Smith') echo 'selected'; ?>>Dr. Smith (Cardiology)</option>
                    <option value="Dr. Johnson" <?php if($appointment['doctor_name'] == 'Dr. Johnson') echo 'selected'; ?>>Dr. Johnson (Pediatrics)</option>
                    <option value="Dr. Williams" <?php if($appointment['doctor_name'] == 'Dr. Williams') echo 'selected'; ?>>Dr. Williams (General)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Date:</label>
                <input type="date" name="date" value="<?php echo $appointment['date']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label>Time:</label>
                <input type="time" name="time" value="<?php echo $appointment['time']; ?>" required>
            </div>

            <div class="form-group">
                <label>Additional Details:</label>
                <textarea name="details" rows="3"><?php echo htmlspecialchars($appointment['details']); ?></textarea>
            </div>

            <button type="submit" class="btn primary-btn block-btn" style="width: 100%; margin-bottom: 10px;">Update Appointment</button>
            <a href="appointments.php" class="btn secondary-btn block-btn" style="display: block; text-align: center; width: 100%; box-sizing: border-box;">Back to My Appointments</a>
        </form>
        <?php endif; ?>
    </div>

</body>
</html>