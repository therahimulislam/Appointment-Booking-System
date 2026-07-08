<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
date_default_timezone_set('Asia/Kolkata'); // Set to local time
$hour = date('H');
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 17) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}
require 'db_connect.php';
$user_id = $_SESSION['user_id'];
$count_query = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE user_id='$user_id'");
$count_result = $count_query->fetch_assoc();
$total_appointments = $count_result['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — CarePlus</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
</head>

<body class="app-page">

    <nav class="navbar glass-nav">
        <div class="container nav-content">
            <h2 class="nav-brand flex-align">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                    <line x1="12" y1="11" x2="12" y2="15"></line>
                    <line x1="10" y1="13" x2="14" y2="13"></line>
                </svg>
                CarePlus
            </h2>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="appointments.php">My Appointments</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <div class="dashboard-header flex-align gap-4 mb-4 glass-panel padding-lg border-radius-lg">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
            </div>
            <div>
                <h2 style="margin-bottom:2px; font-size:1.25rem;"><?php echo $greeting; ?>,
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p class="text-muted" style="font-size:0.875rem;">Here is an overview of your health schedule.</p>
            </div>
        </div>

        <div class="grid-3 mt-4 mb-4">
            <div class="stat-card flex-align gap-4 stat-card-professional">
                <div class="stat-icon stat-icon-blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="stat-details">
                    <h3 style="margin-bottom:2px; font-size:1.5rem; font-weight:700;"><?php echo $total_appointments; ?></h3>
                    <p class="text-muted small">Total Appointments</p>
                </div>
            </div>
            <div class="stat-card flex-align gap-4 stat-card-professional">
                <div class="stat-icon stat-icon-green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="stat-details">
                    <h3 style="margin-bottom:2px; font-size:1.5rem; font-weight:700;">Active</h3>
                    <p class="text-muted small">Notifications</p>
                </div>
            </div>
            <div class="stat-card flex-align gap-4 stat-card-professional">
                <div class="stat-icon stat-icon-indigo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="12" y1="11" x2="12" y2="15"/><line x1="10" y1="13" x2="14" y2="13"/></svg>
                </div>
                <div class="stat-details">
                    <h3 style="margin-bottom:2px; font-size:1.5rem; font-weight:700;">CarePlus</h3>
                    <p class="text-muted small">Primary Clinic</p>
                </div>
            </div>
        </div>

        <h3 class="mb-2 mt-4" style="font-size:1rem; font-weight:600; color:var(--text-secondary); letter-spacing:0.03em; text-transform:uppercase; font-size:0.75rem;">Quick Actions</h3>
        <div class="grid-2 action-cards mt-2">
            <a href="book.php" class="action-card hover-professional text-center no-underline">
                <div class="icon-lg primary-text mb-2" style="margin-inline:auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
                <h3 style="font-size:1rem; margin-bottom:4px;">Book Appointment</h3>
                <p class="text-muted small">Schedule a new visit</p>
            </a>

            <a href="appointments.php" class="action-card hover-professional text-center no-underline">
                <div class="icon-lg primary-text mb-2" style="margin-inline:auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </div>
                <h3 style="font-size:1rem; margin-bottom:4px;">View Records</h3>
                <p class="text-muted small">Check your history</p>
            </a>
        </div>

    </div>

</body>

</html>