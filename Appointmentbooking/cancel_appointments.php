<?php
// Keep user logged in for 30 days
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

if(isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];

    // Verify this booking belongs to the logged-in user
    $check_sql = "SELECT id, date, time FROM bookings WHERE id='$booking_id' AND user_id='$user_id'";
    $check_result = $conn->query($check_sql);

    if($check_result && $check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        
        $appt_timestamp = strtotime($row['date'] . ' ' . $row['time']);
        $current_timestamp = time();
        $six_hours = 6 * 3600;
        
        // Block cancellations within 6 hours
        if ($appt_timestamp - $current_timestamp < $six_hours) {
            echo "<div style='padding:20px; text-align:center; margin-top:50px; font-family:sans-serif; color:#721c24; background-color:#f8d7da; border:1px solid #f5c6cb; border-radius:8px;'>
                    <h2>Cancellation Failed</h2>
                    <p>Appointments cannot be cancelled within 6 hours of the scheduled time.</p>
                    <a href='appointments.php' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#0071E3; color:#fff; text-decoration:none; border-radius:6px;'>Back to Appointments</a>
                  </div>";
            exit();
        }

        // Delete the appointment
        $delete_sql = "DELETE FROM bookings WHERE id='$booking_id'";
        if($conn->query($delete_sql) === TRUE) {
            header("Location: appointments.php?msg=cancelled");
            exit();
        } else {
            echo "Error cancelling appointment: " . $conn->error;
        }
    } else {
        echo "Unauthorized action or appointment not found.";
    }
} else {
    header("Location: appointments.php");
    exit();
}
?>