<?php
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();
require_once 'db_connect.php'; // Ensure this points to your actual database connection file

// Kick out unauthenticated users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}



$error   = '';
$success = '';
$appointment = null;

// 1. Fetch the existing appointment data
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $appointment = $stmt->fetch();

    if (!$appointment) {
        die("Error: Appointment not found or you don't have permission to edit it.");
// 1. Handle Form Submission (When user clicks Save)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = (int) $_POST['booking_id'];
    $date       = $conn->real_escape_string($_POST['date']);
    $time       = $conn->real_escape_string($_POST['time']);
    $details    = $conn->real_escape_string($_POST['details']);

    // ── Date / time validation ────────────────────────────────────────
    $today  = date('Y-m-d');
    $now    = new DateTime('now');
    $cutoff = clone $now;
    $cutoff->modify('+30 minutes');

    if ($date < $today) {
        $error = "You cannot reschedule to a past date.";
    } else {
        $apptDatetime = new DateTime("$date $time");
        if ($apptDatetime < $cutoff) {
            if ($apptDatetime <= $now) {
                $error = "That time slot has already passed. Please choose a future time.";
            } else {
                $diff = $now->diff($apptDatetime);
                $mins = $diff->h * 60 + $diff->i;
                $error = "Please reschedule at least 30 minutes in advance. This slot is only ~{$mins} minute(s) away.";
            }
        }
    }
    // ─────────────────────────────────────────────────────────────────

    if (!$error) {
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
    <title>Edit Appointment — CarePlus</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
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

            <?php if (!$success && $appointment): ?>
                <div class="info-row mb-4" style="background: var(--bg-secondary); padding: 14px; border-radius: 8px; border: 1px solid var(--border);">
                    <p class="text-sm text-muted mb-2"><strong>Booking ID:</strong>
                        <?php echo htmlspecialchars($appointment['id']); ?></p>
                    <p class="text-sm text-muted mb-2"><strong>Patient:</strong>
                        <?php echo htmlspecialchars($appointment['patient_name']); ?></p>
                    <p class="text-sm text-muted"><strong>Doctor:</strong> Dr.
                        <?php echo htmlspecialchars($appointment['doctor_name']); ?>
                        (<?php echo htmlspecialchars($appointment['specialty']); ?>)</p>
                </div>

                <form action="edit_appointment.php" method="POST" id="editForm" novalidate>
                    <input type="hidden" name="booking_id" value="<?php echo $appointment['id']; ?>">

                    <div class="form-group">
                        <label>Reschedule Date:</label>
                        <input type="date" name="date" id="appt-date" required
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo isset($_POST['date']) ? $_POST['date'] : $appointment['date']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Reschedule Time:</label>
                        <?php
                        // Build times array — used by both PHP render and JS slot list
                        $times = [
                            '09:00:00' => '09:00 AM',
                            '10:00:00' => '10:00 AM',
                            '11:00:00' => '11:00 AM',
                            '12:00:00' => '12:00 PM',
                            '14:00:00' => '02:00 PM',
                            '15:00:00' => '03:00 PM',
                            '16:00:00' => '04:00 PM',
                        ];
                        $currentTime = isset($_POST['time']) ? $_POST['time'] : $appointment['time'];
                        ?>
                        <select name="time" id="appt-time" required>
                            <?php foreach ($times as $val => $label):
                                $selected = ($val == $currentTime) ? 'selected' : '';
                                echo "<option value='$val' $selected>$label</option>";
                            endforeach; ?>
                        </select>
                        <p id="time-hint" style="display:none; margin-top:6px; font-size:0.8125rem; color:var(--text-secondary);">
                            Slots within 30 minutes of now are unavailable for today.
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Update Details (Optional):</label>
                        <textarea name="details"
                            rows="3"><?php echo htmlspecialchars(isset($_POST['details']) ? $_POST['details'] : $appointment['details']); ?></textarea>
                    </div>

                    <button type="submit" class="btn primary-btn mt-2 block-btn">Save Changes</button>
                    <a href="appointments.php" class="btn secondary-btn mt-2 block-btn text-center">Cancel</a>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function () {
        const dateEl   = document.getElementById('appt-date');
        const timeEl   = document.getElementById('appt-time');
        const timeHint = document.getElementById('time-hint');
        if (!dateEl || !timeEl) return;

        const slots = [
            { value: '09:00:00', label: '09:00 AM', hhmm: '09:00' },
            { value: '10:00:00', label: '10:00 AM', hhmm: '10:00' },
            { value: '11:00:00', label: '11:00 AM', hhmm: '11:00' },
            { value: '12:00:00', label: '12:00 PM', hhmm: '12:00' },
            { value: '14:00:00', label: '02:00 PM', hhmm: '14:00' },
            { value: '15:00:00', label: '03:00 PM', hhmm: '15:00' },
            { value: '16:00:00', label: '04:00 PM', hhmm: '16:00' },
        ];

        function filterSlots() {
            const selectedDate = dateEl.value;
            const todayStr = new Date().toISOString().split('T')[0];
            const isToday  = (selectedDate === todayStr);

            const now    = new Date();
            const cutoff = new Date(now.getTime() + 30 * 60 * 1000);

            const prevVal = timeEl.value;

            timeEl.innerHTML = '';

            slots.forEach(slot => {
                const [slotH, slotM] = slot.hhmm.split(':').map(Number);
                
                // Construct a date object for this slot today
                const slotDate = new Date();
                slotDate.setHours(slotH, slotM, 0, 0);

                const isPast = isToday && (slotDate < cutoff);

                const opt = document.createElement('option');
                opt.value = slot.value;
                opt.textContent = isPast ? slot.label + ' — unavailable' : slot.label;
                opt.disabled = isPast;
                if (isPast) opt.style.color = '#C7C7CC';
                if (slot.value === prevVal && !isPast) opt.selected = true;

                timeEl.appendChild(opt);
            });

            timeHint.style.display = isToday ? 'block' : 'none';

            // If previously selected slot is now disabled, leave it selected
            // (server will catch it with a clear error message)
            if (prevVal) timeEl.value = prevVal;
        }

        dateEl.addEventListener('change', filterSlots);

        // Run on load so that if today is pre-filled, slots filter immediately
        if (dateEl.value) filterSlots();

        // Client-side guard before submit
        document.getElementById('editForm').addEventListener('submit', function (e) {
            const selectedDate = dateEl.value;
            const selectedTime = timeEl.value;

            if (!selectedDate || !selectedTime) return;

            const todayStr = new Date().toISOString().split('T')[0];

            if (selectedDate < todayStr) {
                e.preventDefault();
                alert('You cannot reschedule to a past date.');
                return;
            }

            if (selectedDate === todayStr) {
                const [h, m] = selectedTime.split(':').map(Number);
                const now    = new Date();
                const cutoff = new Date(now.getTime() + 30 * 60 * 1000);
                const appt   = new Date();
                appt.setHours(h, m, 0, 0);

                if (appt < cutoff) {
                    e.preventDefault();
                    alert('Please reschedule at least 30 minutes in advance.');
                    return;
                }
            }
        });
    })();
    </script>
</body>

</body>
</html>