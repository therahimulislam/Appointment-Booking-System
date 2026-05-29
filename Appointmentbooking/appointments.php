<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$sql = "SELECT b.*, d.name AS doctor_name, d.specialty AS doctor_specialty 
        FROM bookings b 
        LEFT JOIN doctors d ON b.doctor_id = d.id 
        WHERE b.user_id='$user_id' 
        ORDER BY b.date ASC, b.time ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Appointment Booking</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body class="dashboard-bg">

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

    <div class="container mt-4">
        <div class="card glass full-width">
            <div class="flex-align" style="justify-content: space-between; align-items: center;">
                <h2>My Appointments</h2>
                <?php if ($result->num_rows > 0): ?>
                    <button onclick="downloadPDF()" class="btn secondary-btn btn-sm">📥 Export</button>
                <?php endif; ?>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div id="appointmentData" class="table-responsive"> 
                    <h3 style="display: none;" class="pdf-header">CarePlus - Appointment History</h3>
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient Name</th>
                                <th>Doctor</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['booking_id']); ?></strong></td>
                                    <td><?php echo date('F j, Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['time'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td>Dr.
                                        <?php echo htmlspecialchars($row['doctor_name'] ? $row['doctor_name'] : 'Unassigned'); ?>
                                        <span
                                            class="text-muted small">(<?php echo htmlspecialchars($row['doctor_specialty'] ? $row['doctor_specialty'] : 'N/A'); ?>)</span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['details'] ? $row['details'] : 'N/A'); ?></td>

                                    <td>
                                        <?php
                                        $today = date('Y-m-d');
                                        if ($row['date'] < $today) {
                                            echo '<span class="badge" style="background-color: #e5e7eb; color: #4b5563;">Completed</span>';
                                        } else {
                                            echo '<span class="badge success-badge">Upcoming</span>';
                                        }
                                        ?>
                                    </td>

                                    <td class="action-cell">
                                        <button class="kebab-btn"
                                            onclick="toggleDropdown('drop-<?php echo $row['id']; ?>')">⋮</button>
                                        <div id="drop-<?php echo $row['id']; ?>" class="dropdown-content">
                                            <a href="#"
                                                onclick="exportSingleTicket('<?php echo htmlspecialchars($row['booking_id']); ?>', '<?php echo htmlspecialchars($row['patient_name']); ?>', '<?php echo htmlspecialchars($row['doctor_name']); ?>', '<?php echo date('F j, Y', strtotime($row['date'])); ?>', '<?php echo date('h:i A', strtotime($row['time'])); ?>')">📄
                                                Download Ticket</a>

                                            <?php if ($row['date'] >= $today): ?>
                                                <a href="edit_appointment.php?id=<?php echo $row['id']; ?>">✏️ Edit Details</a>
                                                <a href="#" onclick="confirmCancellation(<?php echo $row['id']; ?>)"
                                                    class="text-danger">🚫 Cancel Booking</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state mt-4">
                    <p>You have no booked appointments yet.</p>
                    <a href="book.php" class="btn primary-btn mt-2 inline-block">Book an Appointment</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // ==========================================
        // 1. EXPORT ENTIRE TABLE (The fixed function!)
        // ==========================================
        function downloadPDF() {
            const element = document.getElementById('appointmentData');

            // Safety check in case the ID was accidentally deleted
            if (!element) {
                alert("Error: Table ID missing. Please check step 2!");
                return;
            }

            const header = element.querySelector('.pdf-header');
            if (header) {
                header.style.display = 'block';
                header.style.marginBottom = '20px';
            }

            const opt = {
                margin: 10,
                filename: 'CarePlus_Appointments_History.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                if (header) header.style.display = 'none';
            });
        }

        // ==========================================
        // 2. TOGGLE 3-DOTS DROPDOWN
        // ==========================================
        function toggleDropdown(id) {
            document.querySelectorAll('.dropdown-content').forEach(el => {
                if (el.id !== id) el.classList.remove('show-dropdown');
            });
            document.getElementById(id).classList.toggle('show-dropdown');
        }

        // Close dropdowns if clicking elsewhere
        window.onclick = function (event) {
            if (!event.target.matches('.kebab-btn')) {
                document.querySelectorAll('.dropdown-content').forEach(el => {
                    el.classList.remove('show-dropdown');
                });
            }
        }

        // ==========================================
        // 3. CANCEL BOOKING WARNING
        // ==========================================
        function confirmCancellation(bookingId) {
            const warningMessage = "Are you sure you want to cancel this appointment?\n\nPlease note: This action is permanent and cannot be undone. If you need a new time slot later, you will have to create a new booking.";
            if (confirm(warningMessage)) {
                window.location.href = "cancel_appointment.php?id=" + bookingId;
            }
        }

        // ==========================================
        // 4. EXPORT SINGLE TICKET
        // ==========================================
        function exportSingleTicket(bookingId, patient, doctor, date, time) {
            const tempDiv = document.createElement('div');
            tempDiv.style.padding = '40px';
            tempDiv.style.fontFamily = 'Inter, sans-serif';
            tempDiv.innerHTML = `
                <h2 style="color: #4f46e5; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px;">CarePlus Appointment Ticket</h2>
                <div style="margin-top: 20px; line-height: 2;">
                    <p><strong>Booking ID:</strong> ${bookingId}</p>
                    <p><strong>Patient Name:</strong> ${patient}</p>
                    <p><strong>Doctor:</strong> Dr. ${doctor}</p>
                    <p><strong>Date:</strong> ${date}</p>
                    <p><strong>Time:</strong> ${time}</p>
                    <p><strong>Status:</strong> Confirmed</p>
                </div>
                <p style="margin-top: 40px; font-size: 0.8rem; color: #6b7280;">Please arrive 10 minutes prior to your scheduled time.</p>
            `;

            const opt = {
                margin: 10,
                filename: `CarePlus_Ticket_${bookingId}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(tempDiv).save();
        }
    </script>
</body>

</html>