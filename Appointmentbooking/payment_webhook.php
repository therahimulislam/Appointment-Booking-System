<?php
// ============================================================
//  payment_webhook.php  —  Cashfree Webhook Handler
//  ⚠️  This file receives server-to-server notifications from
//  Cashfree when a payment status changes (PAID, FAILED, etc.)
//
//  This is the BACKUP/FALLBACK mechanism. It fires even if the
//  user closes the browser before payment_return.php loads.
//
//  Cashfree Dashboard Setup:
//  → Developers → Webhooks → Add Endpoint
//  → URL:     https://careplus.xo.je/payment_webhook.php
//  → Version: 2023-08-01
//  → Events:  PAYMENT_SUCCESS, PAYMENT_FAILED, PAYMENT_USER_DROPPED
// ============================================================

// ── No session needed — this is server-to-server ─────────────
require 'db_connect.php';
require 'config.php';

// ── Only accept POST requests ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// ── Read raw POST body ───────────────────────────────────────
$rawBody = file_get_contents('php://input');

// ── STEP 1: Verify Cashfree webhook signature ────────────────
// Cashfree sends these headers (API version 2023-08-01):
//   x-webhook-signature  → HMAC-SHA256(timestamp + body, secret_key) base64
//   x-webhook-timestamp  → Unix timestamp in milliseconds
//   x-webhook-version    → should be '2023-08-01'
$receivedSignature  = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
$timestamp          = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP']  ?? '';
$webhookVersion     = $_SERVER['HTTP_X_WEBHOOK_VERSION']    ?? '';

if (empty($receivedSignature) || empty($timestamp)) {
    http_response_code(400);
    error_log('Cashfree Webhook: Missing signature or timestamp headers.');
    exit('Bad Request');
}

// Log if version mismatches (for debugging, not blocking)
if ($webhookVersion && $webhookVersion !== '2023-08-01') {
    error_log('Cashfree Webhook: Unexpected webhook version: ' . $webhookVersion);
}

// Compute expected signature: HMAC-SHA256(timestamp + rawBody, SECRET_KEY)
$signaturePayload  = $timestamp . $rawBody;
$expectedSignature = base64_encode(hash_hmac('sha256', $signaturePayload, CF_SECRET_KEY, true));

if (!hash_equals($expectedSignature, $receivedSignature)) {
    http_response_code(403);
    error_log('Cashfree Webhook: Signature mismatch! Possible spoofed request.');
    exit('Forbidden');
}

// ── STEP 2: Decode the payload ───────────────────────────────
$payload = json_decode($rawBody, true);

if (!$payload || !isset($payload['data']['order']['order_id'])) {
    http_response_code(400);
    error_log('Cashfree Webhook: Invalid payload — ' . $rawBody);
    exit('Bad Payload');
}

$cfOrderId     = $payload['data']['order']['order_id']     ?? '';
$orderStatus   = $payload['data']['order']['order_status'] ?? '';
$paymentStatus = $payload['data']['payment']['payment_status'] ?? '';
$eventType     = $payload['type'] ?? '';

error_log("Cashfree Webhook received: event={$eventType}, order={$cfOrderId}, status={$orderStatus}, payment={$paymentStatus}");

// ── STEP 3: Handle PAID payments ─────────────────────────────
if ($orderStatus === 'PAID' || $paymentStatus === 'SUCCESS') {

    // Check if booking already saved (avoid duplicate insert)
    $existing = $conn->prepare("SELECT id FROM bookings WHERE cf_order_id = ? AND payment_status = 'paid'");
    $existing->bind_param('s', $cfOrderId);
    $existing->execute();
    $existingResult = $existing->get_result();
    $existing->close();

    if ($existingResult->num_rows > 0) {
        // Already processed — respond OK (idempotent)
        http_response_code(200);
        echo json_encode(['status' => 'already_processed']);
        exit();
    }

    // ── STEP 3a: Check if a booking row exists in 'pending' state ──
    // This happens when payment_return.php already inserted the row
    // but with payment_status = 'pending' (race condition safety net)
    $pendingRow = $conn->prepare("SELECT id FROM bookings WHERE cf_order_id = ? AND payment_status = 'pending'");
    $pendingRow->bind_param('s', $cfOrderId);
    $pendingRow->execute();
    $pendingResult = $pendingRow->get_result();
    $pendingRow->close();

    if ($pendingResult->num_rows > 0) {
        // Update existing row to 'paid'
        $update = $conn->prepare("UPDATE bookings SET payment_status = 'paid' WHERE cf_order_id = ?");
        $update->bind_param('s', $cfOrderId);
        $update->execute();
        $update->close();
        error_log("Cashfree Webhook: Updated existing pending booking to paid for order {$cfOrderId}");
    } else {
        // ── STEP 3b: No row exists yet — this means user closed browser
        // before payment_return.php ran. We must create the booking now.
        // We need to look up what was intended for this order via Cashfree API.
        // (Since session data is gone, we fetch order details from Cashfree)

        // Call Cashfree to get order details including note
        $ch = curl_init(CF_API_URL . '/orders/' . urlencode($cfOrderId));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET        => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-version: '  . CF_API_VERSION,
                'x-client-id: '    . CF_APP_ID,
                'x-client-secret: '. CF_SECRET_KEY,
            ],
            CURLOPT_TIMEOUT => 20,
        ]);
        $cfResp   = curl_exec($ch);
        $curlErr  = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr || $httpCode !== 200) {
            error_log("Cashfree Webhook: Could not fetch order details for {$cfOrderId}. cURL: {$curlErr}, HTTP: {$httpCode}");
            // Respond 200 so Cashfree doesn't retry, but log the error
            http_response_code(200);
            echo json_encode(['status' => 'order_fetch_failed']);
            exit();
        }

        $cfOrder      = json_decode($cfResp, true);
        $customerId   = $cfOrder['customer_details']['customer_id']    ?? ''; // 'user_123'
        $customerName = $cfOrder['customer_details']['customer_name']  ?? '';

        // Extract user_id from customer_id string ('user_123' → 123)
        $userId = (int) str_replace('user_', '', $customerId);

        if ($userId <= 0) {
            error_log("Cashfree Webhook: Cannot determine user_id for order {$cfOrderId}");
            http_response_code(200);
            echo json_encode(['status' => 'user_id_unknown']);
            exit();
        }

        // ── NOTE: Without session data, we cannot recover the specific
        // doctor/date/time. This fallback logs the paid order so support
        // can manually complete the booking if the main flow didn't run.
        // In production, consider storing pending booking in DB (not just session)
        // for full webhook recovery.
        error_log("Cashfree Webhook: PAID order {$cfOrderId} for user {$userId} — booking data not in session. Manual intervention may be needed.");

        // Mark the order as needing manual review
        // (Optional: insert a placeholder row)
    }

// ── STEP 4: Handle FAILED payments ───────────────────────────
} elseif ($orderStatus === 'EXPIRED' || $paymentStatus === 'FAILED' || $paymentStatus === 'USER_DROPPED') {

    // Update any pending booking row for this order to 'failed'
    $update = $conn->prepare("UPDATE bookings SET payment_status = 'failed' WHERE cf_order_id = ? AND payment_status = 'pending'");
    $update->bind_param('s', $cfOrderId);
    $update->execute();
    $update->close();

    error_log("Cashfree Webhook: Payment failed/expired for order {$cfOrderId}, status={$paymentStatus}");
}

// ── STEP 5: Always respond 200 to Cashfree ───────────────────
// If we respond with any non-2xx, Cashfree will retry the webhook.
http_response_code(200);
echo json_encode(['status' => 'ok']);
