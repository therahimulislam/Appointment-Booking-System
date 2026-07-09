<?php
// ============================================================
//  payment_return.php  —  Post-payment landing page
//  Cashfree redirects here after the user completes payment.
//  Verifies order status server-side and saves the booking.
// ============================================================
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';
require 'config.php';

date_default_timezone_set('Asia/Kolkata');

$order_id  = $_GET['order_id'] ?? '';
$status    = 'unknown';
$booking_id = '';
$errorMsg  = '';

// ── 1. Verify we have a pending booking in session ───────────
$pendingBooking = $_SESSION['pending_booking'] ?? null;

if (empty($order_id)) {
    $errorMsg = 'Invalid return URL. No order ID found.';
    $status   = 'error';
} elseif (!$pendingBooking || ($pendingBooking['cf_order_id'] !== $order_id)) {
    // Mismatch or session expired – still verify with Cashfree
    // (handles browser refresh scenarios)
    $pendingBooking = null;
}

// ── 2. Verify payment status with Cashfree (server-side) ────
if ($status !== 'error') {
    $ch = curl_init(CF_API_URL . '/orders/' . urlencode($order_id));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET        => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-version: '  . CF_API_VERSION,
            'x-client-id: '    . CF_APP_ID,
            'x-client-secret: '. CF_SECRET_KEY,
        ],
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $cfResponse = curl_exec($ch);
    $curlErr    = curl_error($ch);
    $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr || $httpCode !== 200) {
        $status   = 'error';
        $errorMsg = 'Could not verify payment status. Please contact support with Order ID: ' . htmlspecialchars($order_id);
        error_log('Cashfree verify cURL error: ' . $curlErr . ' | HTTP: ' . $httpCode);
    } else {
        $cfData      = json_decode($cfResponse, true);
        $orderStatus = $cfData['order_status'] ?? 'UNKNOWN';

        if ($orderStatus === 'PAID') {
            $status = 'paid';
        } elseif (in_array($orderStatus, ['ACTIVE', 'PENDING'])) {
            $status = 'pending';
        } else {
            $status   = 'failed';
            $errorMsg = 'Payment was not completed (Status: ' . htmlspecialchars($orderStatus) . ').';
        }
    }
}

// ── 3. On successful payment: save booking to DB ─────────────
if ($status === 'paid' && $pendingBooking) {
    // Check this order hasn't already been saved (idempotency)
    $existing = $conn->prepare("SELECT id, booking_id FROM bookings WHERE cf_order_id = ?");
    $existing->bind_param('s', $order_id);
    $existing->execute();
    $existingResult = $existing->get_result();
    $existing->close();

    if ($existingResult->num_rows > 0) {
        // Already saved — just show success
        $row        = $existingResult->fetch_assoc();
        $booking_id = $row['booking_id'];
    } else {
        // Double-check no slot conflict from another user
        $conflict = $conn->prepare("SELECT id FROM bookings WHERE date=? AND time=? AND doctor_id=? AND payment_status='paid'");
        $conflict->bind_param('ssi', $pendingBooking['date'], $pendingBooking['time'], $pendingBooking['doctor_id']);
        $conflict->execute();
        $conflictResult = $conflict->get_result();
        $conflict->close();

        if ($conflictResult->num_rows > 0) {
            $status   = 'conflict';
            $errorMsg = 'Unfortunately this time slot was just booked by another patient. Your payment will be refunded within 5-7 business days. Please contact support.';
        } else {
            // Generate booking ID
            $booking_id = 'CP-' . strtoupper(substr(uniqid(), -6));
            $payment_status = 'paid';

            $stmt = $conn->prepare(
                "INSERT INTO bookings (user_id, doctor_id, booking_id, cf_order_id, patient_name, date, time, details, payment_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'iisssssss',
                $pendingBooking['user_id'],
                $pendingBooking['doctor_id'],
                $booking_id,
                $order_id,
                $pendingBooking['patient_name'],
                $pendingBooking['date'],
                $pendingBooking['time'],
                $pendingBooking['details'],
                $payment_status
            );

            if ($stmt->execute()) {
                // Clear pending booking from session
                unset($_SESSION['pending_booking']);
            } else {
                $status   = 'error';
                $errorMsg = 'Payment received but booking could not be saved. Please contact support with Order ID: ' . htmlspecialchars($order_id);
                error_log('Booking insert error: ' . $stmt->error . ' | Order: ' . $order_id);
            }
            $stmt->close();
        }
    }
}

// ── Fetch doctor info if we have the pending booking ────────
$doctorName    = '';
$doctorSpec    = '';
$appointDate   = '';
$appointTime   = '';
$patientName   = '';
$appointAmount = '';

if ($pendingBooking) {
    $docQ = $conn->prepare("SELECT name, specialty FROM doctors WHERE id = ?");
    $docQ->bind_param('i', $pendingBooking['doctor_id']);
    $docQ->execute();
    $docRow = $docQ->get_result()->fetch_assoc();
    $docQ->close();
    $doctorName    = $docRow['name']      ?? '';
    $doctorSpec    = $docRow['specialty'] ?? '';
    $appointDate   = date('F j, Y', strtotime($pendingBooking['date']));
    $appointTime   = date('h:i A', strtotime($pendingBooking['time']));
    $patientName   = $pendingBooking['patient_name'];
    $appointAmount = '₹' . number_format($pendingBooking['amount'] ?? CONSULTATION_FEE, 2);
}

// If we got booking_id from DB (idempotency path) but no pendingBooking
if ($status === 'paid' && $booking_id && !$pendingBooking) {
    // Fetch from DB
    $bkQ = $conn->prepare("SELECT b.*, d.name AS dname, d.specialty AS dspec FROM bookings b LEFT JOIN doctors d ON b.doctor_id=d.id WHERE b.cf_order_id=?");
    $bkQ->bind_param('s', $order_id);
    $bkQ->execute();
    $bkRow = $bkQ->get_result()->fetch_assoc();
    $bkQ->close();
    if ($bkRow) {
        $doctorName  = $bkRow['dname']       ?? '';
        $doctorSpec  = $bkRow['dspec']        ?? '';
        $appointDate = date('F j, Y', strtotime($bkRow['date']));
        $appointTime = date('h:i A', strtotime($bkRow['time']));
        $patientName = $bkRow['patient_name'];
        $appointAmount = '₹' . number_format(CONSULTATION_FEE, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        echo match($status) {
            'paid'    => 'Booking Confirmed — CarePlus',
            'pending' => 'Payment Pending — CarePlus',
            'failed'  => 'Payment Failed — CarePlus',
            'conflict'=> 'Slot Unavailable — CarePlus',
            default   => 'Payment Error — CarePlus',
        };
        ?>
    </title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)" href="mobile.css?v=<?php echo time(); ?>">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <style>
        /* ── Payment Result Page Styles ───────────────────────── */
        .payment-result-wrap {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .result-card {
            max-width: 520px;
            width: 100%;
            background: var(--card-bg, #fff);
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.10);
            overflow: hidden;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Status banner */
        .result-banner {
            padding: 32px 28px 24px;
            text-align: center;
        }
        .result-banner.paid    { background: linear-gradient(135deg, #10b981, #059669); }
        .result-banner.pending { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .result-banner.failed,
        .result-banner.error,
        .result-banner.conflict { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .status-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 2rem;
        }
        .result-banner h1 {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 6px;
        }
        .result-banner p {
            color: rgba(255,255,255,0.85);
            font-size: 0.9rem;
            margin: 0;
        }

        /* Booking details panel */
        .result-body {
            padding: 24px 28px 28px;
        }
        .booking-id-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent-light, #eff6ff);
            color: var(--accent, #0071E3);
            border: 1px solid rgba(0,113,227,0.2);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 20px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }
        .detail-item {
            background: var(--input-bg, #f5f5f7);
            border-radius: 10px;
            padding: 12px 14px;
        }
        .detail-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary, #6E6E73);
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--text-primary, #1D1D1F);
        }
        .amount-item {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid rgba(0,113,227,0.15);
        }
        .amount-item .detail-value {
            font-size: 1.25rem;
            color: #0071E3;
        }

        .result-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .result-actions .btn {
            flex: 1;
            min-width: 140px;
            text-align: center;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .result-actions .btn-primary {
            background: linear-gradient(135deg, #0071E3, #0058B0);
            color: #fff;
        }
        .result-actions .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(0,113,227,0.35);
        }
        .result-actions .btn-secondary {
            background: var(--input-bg, #f5f5f7);
            color: var(--text-primary, #1D1D1F);
            border: 1px solid var(--border, #e5e7eb);
        }
        .result-actions .btn-secondary:hover {
            background: var(--border, #e5e7eb);
        }

        .order-ref {
            margin-top: 18px;
            padding: 10px 14px;
            background: var(--input-bg, #f5f5f7);
            border-radius: 8px;
            font-size: 0.78rem;
            color: var(--text-secondary, #6E6E73);
        }
        .order-ref span { font-weight: 600; color: var(--text-primary, #1D1D1F); }

        /* Spinner for pending */
        .spinner {
            width: 28px;
            height: 28px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
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

    <div class="payment-result-wrap">
        <div class="result-card">

            <?php if ($status === 'paid'): ?>
            <!-- ── SUCCESS ─────────────────────────────────────── -->
            <div class="result-banner paid">
                <div class="status-icon">✅</div>
                <h1>Booking Confirmed!</h1>
                <p>Your payment was successful and your appointment is booked.</p>
            </div>
            <div class="result-body">
                <?php if ($booking_id): ?>
                <div class="booking-id-badge">
                    🎫 Booking ID: <?php echo htmlspecialchars($booking_id); ?>
                </div>
                <?php endif; ?>

                <?php if ($doctorName): ?>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Patient</div>
                        <div class="detail-value"><?php echo htmlspecialchars($patientName); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Doctor</div>
                        <div class="detail-value">Dr. <?php echo htmlspecialchars($doctorName); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date</div>
                        <div class="detail-value"><?php echo htmlspecialchars($appointDate); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Time</div>
                        <div class="detail-value"><?php echo htmlspecialchars($appointTime); ?></div>
                    </div>
                    <?php if ($doctorSpec): ?>
                    <div class="detail-item">
                        <div class="detail-label">Specialty</div>
                        <div class="detail-value"><?php echo htmlspecialchars($doctorSpec); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item amount-item">
                        <div class="detail-label">💳 Amount Paid</div>
                        <div class="detail-value"><?php echo htmlspecialchars($appointAmount); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="result-actions">
                    <a href="appointments.php" class="btn btn-primary">View My Appointments</a>
                    <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                </div>

                <div class="order-ref">
                    Cashfree Order Ref: <span><?php echo htmlspecialchars($order_id); ?></span>
                </div>
            </div>

            <?php elseif ($status === 'pending'): ?>
            <!-- ── PENDING ─────────────────────────────────────── -->
            <div class="result-banner pending">
                <div class="spinner"></div>
                <h1>Payment Pending</h1>
                <p>Your payment is still being processed. Please wait a moment.</p>
            </div>
            <div class="result-body">
                <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:20px;">
                    Your payment is in progress. If it succeeds, your booking will be confirmed automatically.
                    Please check your <strong>My Appointments</strong> page in a few minutes.
                </p>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:20px;">
                    If you are charged but the booking doesn't appear within 15 minutes, please contact support with Order ID: <strong><?php echo htmlspecialchars($order_id); ?></strong>
                </p>
                <div class="result-actions">
                    <a href="appointments.php" class="btn btn-primary">Check My Appointments</a>
                    <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                </div>
            </div>

            <?php elseif ($status === 'conflict'): ?>
            <!-- ── SLOT CONFLICT ──────────────────────────────── -->
            <div class="result-banner failed">
                <div class="status-icon">⚠️</div>
                <h1>Slot No Longer Available</h1>
                <p>Your payment succeeded but the slot was just taken by another patient.</p>
            </div>
            <div class="result-body">
                <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:20px;">
                    <?php echo htmlspecialchars($errorMsg); ?>
                </p>
                <div class="result-actions">
                    <a href="book.php" class="btn btn-primary">Book Another Slot</a>
                    <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                </div>
                <div class="order-ref">
                    Cashfree Order Ref: <span><?php echo htmlspecialchars($order_id); ?></span>
                </div>
            </div>

            <?php else: ?>
            <!-- ── FAILED / ERROR ─────────────────────────────── -->
            <div class="result-banner failed">
                <div class="status-icon">❌</div>
                <h1><?php echo $status === 'failed' ? 'Payment Failed' : 'Payment Error'; ?></h1>
                <p><?php echo $status === 'failed' ? 'Your payment was not completed.' : 'An error occurred during payment.'; ?></p>
            </div>
            <div class="result-body">
                <?php if ($errorMsg): ?>
                <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:20px;">
                    <?php echo htmlspecialchars($errorMsg); ?>
                </p>
                <?php endif; ?>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:20px;">
                    You have <strong>not been charged</strong>. Please try again or choose a different payment method.
                </p>
                <div class="result-actions">
                    <a href="book.php" class="btn btn-primary">Try Again</a>
                    <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                </div>
                <?php if ($order_id): ?>
                <div class="order-ref">
                    Cashfree Order Ref: <span><?php echo htmlspecialchars($order_id); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <?php if ($status === 'pending'): ?>
    <script>
        // Auto-refresh every 8 seconds for pending payments
        setTimeout(function() {
            window.location.reload();
        }, 8000);
    </script>
    <?php endif; ?>

</body>
</html>
