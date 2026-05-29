<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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
    <title>Dashboard - CarePlus</title>
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
                <h2 style="margin-bottom:0;">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p class="text-muted">Here is an overview of your health schedule.</p>
            </div>
        </div>

        <div class="grid-3 mt-4 mb-4">
            <div class="stat-card flex-align gap-4 stat-card-professional">
                <div class="stat-icon stat-icon-blue">📅</div>
                <div class="stat-details">
                    <h3 style="margin-bottom:0; font-size:1.5rem;"><?php echo $total_appointments; ?></h3>
                    <p class="text-muted small">Total Appointments</p>
                </div>
            </div>
            <div class="stat-card flex-align gap-4 stat-card-professional">
                <div class="stat-icon stat-icon-green">🔔</div>
                <div class="stat-details">
                    <h3 style="margin-bottom:0; font-size:1.5rem;">Active</h3>
                    <p class="text-muted small">Notifications</p>
                </div>
            </div>
            <div class="stat-card flex-align gap-4 stat-card-professional">
                <div class="stat-icon stat-icon-indigo">🏥</div>
                <div class="stat-details">
                    <h3 style="margin-bottom:0; font-size:1.5rem;">CarePlus</h3>
                    <p class="text-muted small">Primary Clinic</p>
                </div>
            </div>
        </div>
        
        <h3 class="mb-2 mt-4">Quick Actions</h3>
        <div class="grid-2 action-cards mt-2">
            <a href="book.php" class="action-card hover-professional text-center no-underline">
                <div class="icon-lg primary-text mb-2">➕</div>
                <h3>Book Appointment</h3>
                <p class="text-muted small">Schedule a new visit</p>
            </a>
            
            <a href="appointments.php" class="action-card hover-professional text-center no-underline">
                <div class="icon-lg primary-text mb-2">📋</div>
                <h3>View Records</h3>
                <p class="text-muted small">Check your history</p>
            </a>
        </div>
        
    </div>

</body>
</html>