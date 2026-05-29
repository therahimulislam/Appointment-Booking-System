<?php if(isset($_GET['msg']) && $_GET['msg'] == 'cancelled') echo "<div class='alert success'>Appointment successfully cancelled.</div>"; ?>

<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';

// Check if an ID was passed in the URL
if(isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Verify this booking actually belongs to the logged-in user before deleting!
    $check_sql = "SELECT id FROM bookings WHERE id='$booking_id' AND user_id='$user_id'";
    $check_result = $conn->query($check_sql);

    if($check_result->num_rows > 0) {
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