<?php
// ============================================================
//  initiate_payment.php  —  AJAX endpoint
//  Called via fetch() from book.php when user clicks
//  "Confirm Booking". Validates data, then creates a Cashfree
//  order and returns the payment_session_id as JSON.
// ============================================================
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit();
}

require 'db_connect.php';
// config.php is already loaded by db_connect.php

header('Content-Type: application/json');



// ── 1. Read & sanitise inputs ────────────────────────────────
$date         = $conn->real_escape_string(trim($_POST['date']         ?? ''));
$time         = $conn->real_escape_string(trim($_POST['time']         ?? ''));
$patient_name = $conn->real_escape_string(trim($_POST['patient_name'] ?? ''));
$details      = $conn->real_escape_string(trim($_POST['details']      ?? ''));
$doctor_id    = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
$user_id      = (int)$_SESSION['user_id'];

// ── 2. Validation ────────────────────────────────────────────
if (empty($date) || empty($time) || empty($patient_name) || $doctor_id === 0) {
    echo json_encode(['success' => false, 'error' => 'Name, date, time and doctor are required.']);
    exit();
}

$today  = date('Y-m-d');
$now    = new DateTime('now');
$cutoff = clone $now;
$cutoff->modify('+30 minutes');

if ($date < $today) {
    echo json_encode(['success' => false, 'error' => 'You cannot book an appointment for a past date.']);
    exit();
}

$apptDatetime = new DateTime("$date $time");
if ($apptDatetime < $cutoff) {
    if ($apptDatetime <= $now) {
        echo json_encode(['success' => false, 'error' => 'That time slot has already passed. Please choose a future time.']);
    } else {
        $diff = $now->diff($apptDatetime);
        $mins = $diff->h * 60 + $diff->i;
        echo json_encode(['success' => false, 'error' => "Please book at least 30 minutes in advance. This slot is only ~{$mins} minute(s) away."]);
    }
    exit();
}

// ── 3. Conflict check ────────────────────────────────────────
$check = $conn->query("SELECT id FROM bookings WHERE date='$date' AND time='$time' AND doctor_id='$doctor_id' AND payment_status != 'failed'");
if ($check && $check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'This time slot is already booked. Please choose another time or doctor.']);
    exit();
}

// ── 4. Fetch user info for Cashfree order ───────────────────
$user_stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found.']);
    exit();
}

// ── 5. Fetch doctor name for order note ─────────────────────
$doc_stmt = $conn->prepare("SELECT name, specialty FROM doctors WHERE id = ?");
$doc_stmt->bind_param('i', $doctor_id);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();
$doctor = $doc_result->fetch_assoc();
$doc_stmt->close();

// ── 6. Generate unique Cashfree order ID ────────────────────
$cf_order_id = 'CP-' . strtoupper(substr(uniqid('', true), -10));

// ── 7. Build Cashfree Create Order request ──────────────────
$orderData = [
    'order_id'       => $cf_order_id,
    'order_amount'   => CONSULTATION_FEE,
    'order_currency' => 'INR',
    'order_note'     => 'Consultation: Dr. ' . ($doctor['name'] ?? 'N/A') . ' (' . ($doctor['specialty'] ?? '') . ') on ' . $date . ' at ' . date('h:i A', strtotime($time)),
    'customer_details' => [
        'customer_id'    => 'user_' . $user_id,
        'customer_name'  => $user['name'],
        'customer_email' => $user['email'],
        'customer_phone' => preg_replace('/\D/', '', $user['phone'] ?? '9999999999'), // digits only
    ],
    'order_meta' => [
        'return_url'    => APP_BASE_URL . '/payment_return.php?order_id={order_id}',
        'notify_url'    => APP_BASE_URL . '/payment_webhook.php',
    ],
];

// Ensure phone is 10 digits (Cashfree requires it)
$phone = $orderData['customer_details']['customer_phone'];
if (strlen($phone) > 10) {
    // Strip country code prefix
    $phone = substr($phone, -10);
}
if (strlen($phone) < 10) {
    $phone = str_pad($phone, 10, '9', STR_PAD_LEFT);
}
$orderData['customer_details']['customer_phone'] = $phone;

// ── 8. Call Cashfree API ─────────────────────────────────────
$ch = curl_init(CF_API_URL . '/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($orderData),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-version: ' . CF_API_VERSION,
        'x-client-id: '     . CF_APP_ID,
        'x-client-secret: ' . CF_SECRET_KEY,
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$cfResponse = curl_exec($ch);
$curlError  = curl_error($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError) {
    error_log('Cashfree cURL error: ' . $curlError);
    echo json_encode(['success' => false, 'error' => 'Payment gateway connection failed. Please try again.']);
    exit();
}

$cfData = json_decode($cfResponse, true);

if ($httpCode !== 200 || empty($cfData['payment_session_id'])) {
    $errMsg = $cfData['message'] ?? ($cfData['error_detail']['error_reason'] ?? 'Unknown Cashfree error');
    error_log('Cashfree API error (' . $httpCode . '): ' . $cfResponse);
    echo json_encode(['success' => false, 'error' => 'Payment gateway error: ' . $errMsg]);
    exit();
}

// ── 9. Store pending booking data in session ─────────────────
$_SESSION['pending_booking'] = [
    'user_id'      => $user_id,
    'doctor_id'    => $doctor_id,
    'patient_name' => $patient_name,
    'date'         => $date,
    'time'         => $time,
    'details'      => $details,
    'cf_order_id'  => $cf_order_id,
    'amount'       => CONSULTATION_FEE,
    'created_at'   => time(),
];

// ── 10. Return session ID to frontend ────────────────────────
echo json_encode([
    'success'            => true,
    'payment_session_id' => $cfData['payment_session_id'],
    'cf_order_id'        => $cf_order_id,
    'amount'             => CONSULTATION_FEE,
]);
