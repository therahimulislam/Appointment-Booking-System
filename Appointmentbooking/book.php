<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

date_default_timezone_set('Asia/Kolkata');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date        = $conn->real_escape_string($_POST['date']);
    $time        = $conn->real_escape_string($_POST['time']);
    $patient_name = $conn->real_escape_string($_POST['patient_name']);
    $details     = $conn->real_escape_string($_POST['details']);
    $doctor_id   = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
    $user_id     = $_SESSION['user_id'];

    if(empty($date) || empty($time) || empty($patient_name) || empty($doctor_id)) {
        $error = "Name, date, time, and doctor selection are required!";
    } else {

        // ── Date / time validation ──────────────────────────────────────
        $today    = date('Y-m-d');
        $now      = new DateTime('now');                  // current local time
        $cutoff   = clone $now;
        $cutoff->modify('+30 minutes');                   // must be ≥ now + 30 min

        // Reject past dates
        if ($date < $today) {
            $error = "You cannot book an appointment for a past date.";
        } else {
            // For today: reject slots that are within 30 minutes of now
            $apptDatetime = new DateTime("$date $time");
            if ($apptDatetime < $cutoff) {
                // Calculate how many minutes away the slot is for a helpful message
                $diff = $now->diff($apptDatetime);
                if ($apptDatetime <= $now) {
                    $error = "That time slot has already passed. Please choose a future time.";
                } else {
                    $mins = $diff->h * 60 + $diff->i;
                    $error = "Please book at least 30 minutes in advance. This slot is only ~{$mins} minute(s) away.";
                }
            } else {
                // ── Conflict check ──────────────────────────────────────
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
        // ────────────────────────────────────────────────────────────────
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment — CarePlus</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)" href="mobile.css?v=<?php echo time(); ?>">
</head>
<body class="app-page">

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
            <form action="book.php" method="POST" id="bookingForm" novalidate>
                <div class="form-group">
                    <label>Patient Name:</label>
                    <input type="text" name="patient_name" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                </div>

                <?php
                // Fetch all doctors and build specialty list
                $doctors_data = [];
                $specialties  = [];
                $doctors_res  = $conn->query("SELECT id, name, specialty FROM doctors ORDER BY name ASC");
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
                    <label>Appointment Date:</label>
                    <input type="date" name="date" id="appt-date" required
                           min="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Time Slot:</label>
                    <select name="time" id="appt-time" required>
                        <option value="">-- Select Time --</option>
                        <option value="09:00:00">09:00 AM</option>
                        <option value="10:00:00">10:00 AM</option>
                        <option value="11:00:00">11:00 AM</option>
                        <option value="12:00:00">12:00 PM</option>
                        <option value="14:00:00">02:00 PM</option>
                        <option value="15:00:00">03:00 PM</option>
                        <option value="16:00:00">04:00 PM</option>
                    </select>
                    <!-- Inline hint shown when slots are filtered for today -->
                    <p id="time-hint" style="display:none; margin-top:6px; font-size:0.8125rem; color:var(--text-secondary);">
                        Slots within 30 minutes of now are unavailable for today.
                    </p>
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
    (function () {
        // ── Doctor cascade ──────────────────────────────────────────────
        const doctors      = <?php echo json_encode($doctors_data); ?>;
        const specialtyEl  = document.getElementById('specialty');
        const doctorEl     = document.getElementById('doctor_id');

        specialtyEl.addEventListener('change', function () {
            doctorEl.innerHTML = '<option value="">-- Select Doctor --</option>';
            if (this.value) {
                doctors
                    .filter(d => d.specialty === this.value)
                    .forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = 'Dr. ' + d.name;
                        doctorEl.appendChild(opt);
                    });
                doctorEl.disabled = false;
            } else {
                doctorEl.disabled = true;
            }
        });

        // ── Date / time validation (UX layer) ──────────────────────────
        const dateEl    = document.getElementById('appt-date');
        const timeEl    = document.getElementById('appt-time');
        const timeHint  = document.getElementById('time-hint');

        // All available slots as { value, label } pairs (HH:MM:SS → HH:MM for comparison)
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

            // Current time + 30-minute buffer
            const now    = new Date();
            const cutoff = new Date(now.getTime() + 30 * 60 * 1000);

            // Save currently selected value so we can restore it if still valid
            const prevVal = timeEl.value;

            // Rebuild options
            timeEl.innerHTML = '<option value="">-- Select Time --</option>';

            slots.forEach(slot => {
                const [slotH, slotM] = slot.hhmm.split(':').map(Number);
                
                // Construct a date object for this slot today
                const slotDate = new Date();
                slotDate.setHours(slotH, slotM, 0, 0);

                const isPast = isToday && (slotDate < cutoff);

                const opt = document.createElement('option');
                opt.value = slot.value;
                opt.textContent = slot.label;

                if (isPast) {
                    opt.disabled = true;
                    opt.style.color = '#C7C7CC';
                    opt.textContent = slot.label + ' — unavailable';
                } else {
                    if (slot.value === prevVal) opt.selected = true;
                }

                timeEl.appendChild(opt);
            });

            // Show hint only when today is selected
            timeHint.style.display = isToday ? 'block' : 'none';

            // If the previously selected slot is now disabled, reset to placeholder
            if (prevVal) {
                const stillValid = [...timeEl.options].some(o => o.value === prevVal && !o.disabled);
                if (!stillValid) timeEl.value = '';
            }
        }

        // Re-filter whenever the date changes
        dateEl.addEventListener('change', filterSlots);

        // Also run on page load if a date is already pre-filled (e.g. back from error)
        if (dateEl.value) filterSlots();

        // Client-side guard before submit
        document.getElementById('bookingForm').addEventListener('submit', function (e) {
            const selectedDate = dateEl.value;
            const selectedTime = timeEl.value;

            if (!selectedDate || !selectedTime) return; // let server handle empty fields

            const todayStr = new Date().toISOString().split('T')[0];

            // Block past dates
            if (selectedDate < todayStr) {
                e.preventDefault();
                alert('You cannot book an appointment for a past date.');
                return;
            }

            // Block slots within 30 minutes for today
            if (selectedDate === todayStr) {
                const [h, m] = selectedTime.split(':').map(Number);
                const now    = new Date();
                const cutoff = new Date(now.getTime() + 30 * 60 * 1000);
                const appt   = new Date();
                appt.setHours(h, m, 0, 0);

                if (appt < cutoff) {
                    e.preventDefault();
                    alert('Please book at least 30 minutes in advance.');
                    return;
                }
            }
        });
    })();
    </script>
</body>
</html>