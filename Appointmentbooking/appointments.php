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
            <h2>My Appointments</h2>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive mt-4">
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
                                    <td> <?php
                                    $today = date('Y-m-d');
                                    if ($row['date'] < $today) {
                                        echo '<span class="badge" style="background-color: #e5e7eb; color: #4b5563;">Completed</span>';
                                    } else {
                                        echo '<span class="badge success-badge">Upcoming</span>';
                                    }
                                    ?>
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

</body>

</html>