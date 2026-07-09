<?php
// Load environment variables (reads .env file)
require_once __DIR__ . '/config.php';

$servername = env('DB_HOST', 'localhost');
$username   = env('DB_USER', 'root');
$password   = env('DB_PASS', '');
$dbname     = env('DB_NAME', 'appointment_db');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
