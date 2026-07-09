<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'db_connect.php';
// config.php is already loaded by db_connect.php



// book.php now only renders the form.
// All booking logic is handled by initiate_payment.php (AJAX)
// and payment_return.php (post-payment confirmation).
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment — CarePlus</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)" href="mobile.css?v=<?php echo time(); ?>">
    <!-- Cashfree Payment Gateway JS SDK -->
    <script src="<?php echo CF_JS_SDK; ?>"></script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <style>
        /* ── Payment loading overlay ───────────────────────────── */
        .payment-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 16px;
        }
        .payment-overlay.active {
            display: flex;
        }
        .payment-spinner {
            width: 52px;
            height: 52px;
            border: 4px solid rgba(255,255,255,0.25);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .payment-overlay-text {
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }
        .payment-overlay-sub {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
        }

        /* ── Fee badge shown next to submit button ─────────────── */
        .fee-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid rgba(0,113,227,0.2);
            border-radius: 10px;
            margin-top: 12px;
            margin-bottom: 4px;
        }
        .fee-info .fee-icon { font-size: 1.2rem; }
        .fee-info .fee-text {
            flex: 1;
            font-size: 0.85rem;
            color: #1e40af;
            font-weight: 500;
        }
        .fee-info .fee-amount {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0071E3;
        }

        /* ── Client-side error banner ───────────────────────────── */
        #js-error {
            display: none;
            padding: 12px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #b91c1c;
            font-size: 0.875rem;
            margin-bottom: 12px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

        /* ── Cashfree mode badge ────────────────────────────────── */
        .cf-mode-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 5px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-left: 8px;
            vertical-align: middle;
        }
        .cf-mode-badge.sandbox {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .cf-mode-badge.production {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        /* ── Button loading state ───────────────────────────────── */
        .btn-paying {
            opacity: 0.75;
            pointer-events: none;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="app-page">

    <!-- Payment loading overlay -->
    <div class="payment-overlay" id="paymentOverlay">
        <div class="payment-spinner"></div>
        <div class="payment-overlay-text" id="overlayText">Processing Payment…</div>
        <div class="payment-overlay-sub" id="overlaySub">Please wait, do not close this window.</div>
    </div>

    <nav class="navbar glass-nav">
        <div class="container nav-content">
            <h2 class="nav-brand flex-align">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><line x1="12" y1="11" x2="12" y2="15"></line><line x1="10" y1="13" x2="14" y2="13"></line></svg>
                CarePlus
            </h2>
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle navigation" aria-expanded="false">
                <span class="ham-bar"></span>
                <span class="ham-bar"></span>
                <span class="ham-bar"></span>
            </button>
            <div class="nav-links" id="nav-links">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>
                <a href="dashboard.php">Dashboard</a>
                <a href="appointments.php">My Appointments</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 center-content no-height">
        <div class="card glass">
            <h2>
                Book an Appointment
                <span class="cf-mode-badge <?php echo CF_ENV; ?>">
                    <?php echo CF_ENV === 'sandbox' ? '🧪 Test Mode' : '🔒 Live'; ?>
                </span>
            </h2>

            <!-- JS-driven error display -->
            <div id="js-error"></div>

            <form id="bookingForm" novalidate>
                <div class="form-group">
                    <label>Patient Name:</label>
                    <input type="text" name="patient_name" id="patient_name"
                           value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                </div>

                <?php
                // Fetch all doctors and build specialty list
                $doctors_data = [];
                $specialties  = [];
                $doctors_res  = $conn->query("SELECT id, name, specialty FROM doctors ORDER BY name ASC");
                if ($doctors_res) {
                    while($doc = $doctors_res->fetch_assoc()) {
                        $doctors_data[] = $doc;
                        if (!in_array($doc['specialty'], $specialties)) {
                            $specialties[] = $doc['specialty'];
                        }
                    }
                }
                sort($specialties);
                ?>

                <div class="form-group">
                    <label>Select Specialty (Profession):</label>
                    <select name="specialty" id="specialty" required>
                        <option value="">-- Select Specialty --</option>
                        <?php
                        foreach($specialties as $spec) {
                            echo "<option value='" . htmlspecialchars($spec) . "'>" . htmlspecialchars($spec) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Doctor:</label>
                    <select name="doctor_id" id="doctor_id" required disabled>
                        <option value="">-- Select Doctor --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Appointment Date:</label>
                    <input type="date" name="date" id="appt-date" required
                           min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Time Slot:</label>
                    <select name="time" id="appt-time" required>
                        <option value="">-- Select Time --</option>
                        <option value="09:00:00">09:00 AM</option>
                        <option value="10:00:00">10:00 AM</option>
                        <option value="11:00:00">11:00 AM</option>
                        <option value="12:00:00">12:00 PM</option>
                        <option value="14:00:00">02:00 PM</option>
                        <option value="15:00:00">03:00 PM</option>
                        <option value="16:00:00">04:00 PM</option>
                    </select>
                    <!-- Inline hint shown when slots are filtered for today -->
                    <p id="time-hint" style="display:none; margin-top:6px; font-size:0.8125rem; color:var(--text-secondary);">
                        Slots within 30 minutes of now are unavailable for today.
                    </p>
                </div>

                <div class="form-group">
                    <label>Additional Details (Optional):</label>
                    <textarea name="details" id="details" rows="3" placeholder="Any specific requirements…"></textarea>
                </div>

                <!-- Consultation fee info -->
                <div class="fee-info">
                    <span class="fee-icon">💳</span>
                    <span class="fee-text">Consultation fee payable via Cashfree (UPI, Cards, Net Banking & more)</span>
                    <span class="fee-amount">₹<?php echo number_format(CONSULTATION_FEE, 0); ?></span>
                </div>

                <button type="submit" class="btn primary-btn mt-2 block-btn" id="submitBtn">
                    🔒 Confirm &amp; Pay ₹<?php echo number_format(CONSULTATION_FEE, 0); ?>
                </button>
            </form>
        </div>
    </div>

    <script>
    (function () {
        // ── Cashfree SDK init ────────────────────────────────────
        const cashfree = Cashfree({ mode: '<?php echo CF_ENV; ?>' });

        // ── Doctor cascade ───────────────────────────────────────
        const doctors     = <?php echo json_encode($doctors_data); ?>;
        const specialtyEl = document.getElementById('specialty');
        const doctorEl    = document.getElementById('doctor_id');

        specialtyEl.addEventListener('change', function () {
            doctorEl.innerHTML = '<option value="">-- Select Doctor --</option>';
            if (this.value) {
                doctors
                    .filter(d => d.specialty === this.value)
                    .forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = 'Dr. ' + d.name;
                        doctorEl.appendChild(opt);
                    });
                doctorEl.disabled = false;
            } else {
                doctorEl.disabled = true;
            }
        });

        // ── Date / time validation (UX layer) ───────────────────
        const dateEl   = document.getElementById('appt-date');
        const timeEl   = document.getElementById('appt-time');
        const timeHint = document.getElementById('time-hint');

        const slots = [
            { value: '09:00:00', label: '09:00 AM', hhmm: '09:00' },
            { value: '10:00:00', label: '10:00 AM', hhmm: '10:00' },
            { value: '11:00:00', label: '11:00 AM', hhmm: '11:00' },
            { value: '12:00:00', label: '12:00 PM', hhmm: '12:00' },
            { value: '14:00:00', label: '02:00 PM', hhmm: '14:00' },
            { value: '15:00:00', label: '03:00 PM', hhmm: '15:00' },
            { value: '16:00:00', label: '04:00 PM', hhmm: '16:00' },
        ];

        function filterSlots() {
            const selectedDate = dateEl.value;
            const todayStr = new Date().toISOString().split('T')[0];
            const isToday  = (selectedDate === todayStr);
            const now      = new Date();
            const cutoff   = new Date(now.getTime() + 30 * 60 * 1000);
            const prevVal  = timeEl.value;

            timeEl.innerHTML = '<option value="">-- Select Time --</option>';

            slots.forEach(slot => {
                const [slotH, slotM] = slot.hhmm.split(':').map(Number);
                const slotDate = new Date();
                slotDate.setHours(slotH, slotM, 0, 0);
                const isPast = isToday && (slotDate < cutoff);

                const opt = document.createElement('option');
                opt.value = slot.value;
                opt.textContent = slot.label;

                if (isPast) {
                    opt.disabled = true;
                    opt.style.color = '#C7C7CC';
                    opt.textContent = slot.label + ' — unavailable';
                } else {
                    if (slot.value === prevVal) opt.selected = true;
                }

                timeEl.appendChild(opt);
            });

            timeHint.style.display = isToday ? 'block' : 'none';

            if (prevVal) {
                const stillValid = [...timeEl.options].some(o => o.value === prevVal && !o.disabled);
                if (!stillValid) timeEl.value = '';
            }
        }

        dateEl.addEventListener('change', filterSlots);
        if (dateEl.value) filterSlots();

        // ── Error display helper ─────────────────────────────────
        const errorBox = document.getElementById('js-error');
        function showError(msg) {
            errorBox.textContent = msg;
            errorBox.style.display = 'block';
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function clearError() {
            errorBox.style.display = 'none';
            errorBox.textContent = '';
        }

        // ── Overlay helpers ──────────────────────────────────────
        const overlay    = document.getElementById('paymentOverlay');
        const overlayTxt = document.getElementById('overlayText');
        const overlaySub = document.getElementById('overlaySub');

        function showOverlay(text, sub) {
            overlayTxt.textContent = text || 'Processing Payment…';
            overlaySub.textContent = sub  || 'Please wait, do not close this window.';
            overlay.classList.add('active');
        }
        function hideOverlay() {
            overlay.classList.remove('active');
        }

        // ── Main form submit handler ─────────────────────────────
        document.getElementById('bookingForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            clearError();

            // ─ Client-side validation ─────────────────────────
            const patient = document.getElementById('patient_name').value.trim();
            const date    = dateEl.value;
            const time    = timeEl.value;
            const doctor  = doctorEl.value;

            if (!patient) { showError('Please enter the patient name.'); return; }
            if (!doctor)  { showError('Please select a specialty and doctor.'); return; }
            if (!date)    { showError('Please select an appointment date.'); return; }
            if (!time)    { showError('Please select a time slot.'); return; }

            const todayStr = new Date().toISOString().split('T')[0];
            if (date < todayStr) { showError('You cannot book an appointment for a past date.'); return; }

            if (date === todayStr) {
                const [h, m] = time.split(':').map(Number);
                const now    = new Date();
                const cutoff = new Date(now.getTime() + 30 * 60 * 1000);
                const appt   = new Date(); appt.setHours(h, m, 0, 0);
                if (appt < cutoff) {
                    showError('Please book at least 30 minutes in advance.');
                    return;
                }
            }

            // ─ Lock button & show overlay ──────────────────────
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('btn-paying');
            submitBtn.textContent = '⏳ Initiating Payment…';
            showOverlay('Preparing your order…', 'Connecting to payment gateway…');

            // ─ Collect form data ───────────────────────────────
            const formData = new FormData(this);

            try {
                // ─ Call initiate_payment.php (server-side order creation) ─
                const res  = await fetch('initiate_payment.php', {
                    method: 'POST',
                    body: formData,
                });
                const data = await res.json();

                if (!data.success) {
                    hideOverlay();
                    submitBtn.classList.remove('btn-paying');
                    submitBtn.textContent = '🔒 Confirm & Pay ₹<?php echo number_format(CONSULTATION_FEE, 0); ?>';
                    showError(data.error || 'An unexpected error occurred. Please try again.');
                    return;
                }

                // ─ Open Cashfree payment modal ─────────────────
                overlayTxt.textContent = 'Opening Payment Gateway…';
                overlaySub.textContent = 'Please complete your payment in the popup window.';

                const checkoutResult = await cashfree.checkout({
                    paymentSessionId: data.payment_session_id,
                    returnUrl: '<?php echo APP_BASE_URL; ?>/payment_return.php?order_id={order_id}',
                });

                if (checkoutResult && checkoutResult.error) {
                    hideOverlay();
                    submitBtn.classList.remove('btn-paying');
                    submitBtn.textContent = '🔒 Confirm & Pay ₹<?php echo number_format(CONSULTATION_FEE, 0); ?>';
                    const errCode = checkoutResult.error.code || '';
                    const errMsg  = checkoutResult.error.message || 'Payment failed or was cancelled.';
                    showError(errCode === 'PAYMENT_CLOSED' ? 'Payment window was closed. You have not been charged. Please try again.' : errMsg);
                    return;
                }

                // Payment completed — redirect handled by returnUrl
                showOverlay('Verifying Payment…', 'Almost there! Confirming your booking…');

            } catch (err) {
                hideOverlay();
                submitBtn.classList.remove('btn-paying');
                submitBtn.textContent = '🔒 Confirm & Pay ₹<?php echo number_format(CONSULTATION_FEE, 0); ?>';
                showError('Network error. Please check your connection and try again.');
                console.error('Payment error:', err);
            }
        });
    })();

    // Hamburger Menu Logic
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const navLinks = document.getElementById('nav-links');
    if (mobileBtn && navLinks) {
        mobileBtn.addEventListener('click', () => {
            mobileBtn.classList.toggle('open');
            navLinks.classList.toggle('active');
        });
    }

    // Theme Toggle Logic
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
    </script>
</body>
</html>