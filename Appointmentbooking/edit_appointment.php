<?php
// Keep user logged in for 30 days
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$error   = '';
$success = '';
$appointment = null;
$user_id = (int)$_SESSION['user_id'];

// 1. Fetch the existing appointment data
if (isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT b.*, d.name AS doctor_name, d.specialty FROM bookings b LEFT JOIN doctors d ON b.doctor_id = d.id WHERE b.id = ? AND b.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
    } else {
        die("Error: Appointment not found or you don't have permission to edit it.");
    }
} elseif (isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
} else {
    header("Location: appointments.php");
    exit();
}

// 2. Handle Form Submission (When user clicks Save)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date       = $conn->real_escape_string($_POST['date']);
    $time       = $conn->real_escape_string($_POST['time']);
    $details    = $conn->real_escape_string($_POST['details']);

    // Fetch original doctor_id from the hidden input
    $doctor_id  = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;

    // ── Date / time validation ──
    $today  = date('Y-m-d');
    $now    = new DateTime('now');
    $cutoff = clone $now;
    $cutoff->modify('+30 minutes');

    if ($date < $today) {
        $error = "You cannot reschedule to a past date.";
    } else {
        $apptDatetime = new DateTime("$date $time");
        if ($apptDatetime < $cutoff) {
            $error = "Please reschedule at least 30 minutes in advance.";
        }
    }

    // ── Conflict Check & Update ──
    if (!$error && $doctor_id > 0) {
        // Make sure we only check for conflicts against valid (non-failed) payments
        $conflict_check = $conn->query("SELECT id FROM bookings WHERE date='$date' AND time='$time' AND doctor_id='$doctor_id' AND id != '$booking_id' AND payment_status != 'failed'");

        if ($conflict_check && $conflict_check->num_rows > 0) {
            $error = "This time slot is already booked! Please choose another time.";
        } else {
            $update_sql = "UPDATE bookings SET date='$date', time='$time', details='$details' WHERE id='$booking_id' AND user_id='$user_id'";
            if ($conn->query($update_sql) === TRUE) {
                $success = "Appointment updated successfully! <a href='appointments.php' style='color:#15803d; text-decoration:underline;'>Return to My Appointments</a>.";
                
                // Refresh local data to show new times on the page immediately
                $appointment['date'] = $date;
                $appointment['time'] = $time;
                $appointment['details'] = $details;
            } else {
                $error = "Error updating appointment: " . $conn->error;
            }
        }
    } elseif (!$error) {
        $error = "Missing doctor information. Cannot reschedule.";
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
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)" href="mobile.css?v=<?php echo time(); ?>">
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
            <div class="info-row mb-4" style="background: var(--bg-secondary); padding: 14px; border-radius: 8px; border: 1px solid var(--border);">
                <p class="text-sm text-muted mb-2"><strong>Booking ID:</strong> <?php echo htmlspecialchars($appointment['cf_order_id'] ?? $appointment['booking_id']); ?></p>
                <p class="text-sm text-muted mb-2"><strong>Patient:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></p>
                <p class="text-sm text-muted"><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($appointment['doctor_name']); ?> (<?php echo htmlspecialchars($appointment['specialty']); ?>)</p>
            </div>

            <form action="edit_appointment.php?id=<?php echo $appointment['id']; ?>" method="POST" id="editForm" novalidate>
                <input type="hidden" name="booking_id" value="<?php echo $appointment['id']; ?>">
                <input type="hidden" name="doctor_id" value="<?php echo $appointment['doctor_id']; ?>">

                <div class="form-group">
                    <label>Reschedule Date:</label>
                    <input type="date" name="date" id="appt-date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($appointment['date']); ?>">
                </div>

                <div class="form-group">
                    <label>Reschedule Time:</label>
                    <?php
                    $times = [
                        '09:00:00' => '09:00 AM',
                        '10:00:00' => '10:00 AM',
                        '11:00:00' => '11:00 AM',
                        '12:00:00' => '12:00 PM',
                        '14:00:00' => '02:00 PM',
                        '15:00:00' => '03:00 PM',
                        '16:00:00' => '04:00 PM',
                    ];
                    $currentTime = $appointment['time'];
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
                    <textarea name="details" rows="3"><?php echo htmlspecialchars($appointment['details']); ?></textarea>
                </div>

                <button type="submit" class="btn primary-btn mt-2 block-btn">Save Changes</button>
                <a href="appointments.php" class="btn secondary-btn mt-2 block-btn text-center" style="display:block; text-decoration:none;">Cancel</a>
            </form>
        <?php endif; ?>
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

            if (prevVal) timeEl.value = prevVal;
        }

        dateEl.addEventListener('change', filterSlots);
        if (dateEl.value) filterSlots();

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
</html>