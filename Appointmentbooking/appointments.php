<?php
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
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
    <title>My Appointments — CarePlus</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)" href="mobile.css?v=<?php echo time(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>

<body class="app-page">

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
                <a href="appointments.php" class="active">My Appointments</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card glass full-width">
            <div class="flex-align" style="justify-content: space-between; align-items: center;">
                <h2>My Appointments</h2>
                <?php if ($result->num_rows > 0): ?>
                    <button onclick="downloadPDF()" class="btn secondary-btn btn-sm">Export PDF</button>
                <?php endif; ?>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div id="appointmentData" class="table-responsive">
                    <!-- Professional Print Header -->
                    <div class="pdf-header" style="display: none; border-bottom: 2px solid #0071E3; padding-bottom: 12px; margin-bottom: 20px; font-family: system-ui, -apple-system, sans-serif;">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; background: #EBF3FC; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #0071E3;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><line x1="12" y1="11" x2="12" y2="15"></line><line x1="10" y1="13" x2="14" y2="13"></line></svg>
                                </div>
                                <span style="font-weight: 700; font-size: 1.25rem; color: #1D1D1F; letter-spacing: -0.02em;">CarePlus Medical Center</span>
                            </div>
                            <div style="text-align: right; color: #6E6E73; font-size: 0.75rem; line-height: 1.4;">
                                <strong style="color: #1D1D1F; font-size: 0.85rem;">Appointment History Report</strong><br>
                                Generated: <?php echo date('F j, Y'); ?>
                            </div>
                        </div>
                    </div>
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
                                <th>Payment</th>
                                <th>Action</th>
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

                                    <td>
                                        <?php
                                        $current_date = date('Y-m-d');
                                        $current_time = date('H:i:s');
                                        
                                        if ($row['date'] < $current_date || ($row['date'] == $current_date && $row['time'] < $current_time)) {
                                            echo '<span class="badge" style="background-color: #e5e7eb; color: #4b5563;">Completed</span>';
                                        } else {
                                            echo '<span class="badge success-badge">Upcoming</span>';
                                        }
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        $pmtStatus = $row['payment_status'] ?? 'paid'; // 'paid','pending','failed'
                                        if ($pmtStatus === 'paid') {
                                            echo '<span class="badge" style="background:#EBF3FC;color:#0071E3;border:1px solid #B6D4F8;display:inline-flex;align-items:center;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Paid</span>';
                                        } elseif ($pmtStatus === 'pending') {
                                            echo '<span class="badge" style="background:#fef9c3;color:#92400e;border:1px solid #fde68a;display:inline-flex;align-items:center;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Pending</span>';
                                        } else {
                                            echo '<span class="badge" style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;display:inline-flex;align-items:center;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Failed</span>';
                                        }
                                        ?>
                                    </td>

                                    <td class="action-cell">
                                        <button class="kebab-btn"
                                            onclick="toggleDropdown('drop-<?php echo $row['id']; ?>')">⋮</button>
                                        <div id="drop-<?php echo $row['id']; ?>" class="dropdown-content">
                                            <a href="#"
                                                onclick="exportSingleTicket('<?php echo htmlspecialchars($row['booking_id']); ?>', '<?php echo htmlspecialchars($row['patient_name']); ?>', '<?php echo htmlspecialchars($row['doctor_name'] ? $row['doctor_name'] : 'Unassigned'); ?>', '<?php echo htmlspecialchars($row['doctor_specialty'] ? $row['doctor_specialty'] : 'General Medicine'); ?>', '<?php echo date('F j, Y', strtotime($row['date'])); ?>', '<?php echo date('h:i A', strtotime($row['time'])); ?>', '<?php echo htmlspecialchars(ucfirst($pmtStatus)); ?>', '<?php echo number_format(CONSULTATION_FEE, 0); ?>', '<?php echo htmlspecialchars($row['cf_order_id'] ?? 'N/A'); ?>')">Download Receipt</a>

                                            <?php 
                                            $appt_timestamp = strtotime($row['date'] . ' ' . $row['time']);
                                            $time_diff = $appt_timestamp - time();
                                            if ($time_diff >= (6 * 3600)): 
                                            ?>
                                                <a href="edit_appointment.php?id=<?php echo $row['id']; ?>">Edit Details</a>
                                                <a href="#" onclick="confirmCancellation(<?php echo $row['id']; ?>)"
                                                    class="text-danger">Cancel Booking</a>
                                            <?php elseif ($appt_timestamp > time()): ?>
                                                <a href="#" class="text-muted" style="cursor:not-allowed;" title="Cannot cancel within 6 hours of appointment" onclick="alert('Appointments cannot be cancelled within 6 hours of the scheduled time.'); return false;">Cancel Booking</a>
                                            <?php endif; ?>
                                        </div>
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
    <div id="cancelModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Cancel Appointment</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this appointment?
                    This cannot be undone.
                </p>
                <p class="text-sm text-muted mt-2" style="font-size: 0.85rem; color: #6b7280;">This action is permanent
                    and cannot be undone. If you need a new time slot later, you will have to create a new booking.</p>
            </div>
            <div class="modal-footer">
                <button onclick="closeModal()" class="btn secondary-btn" style="border: 1px solid #d1d5db;">No, Keep it</button>
                <button onclick="executeCancellation()" class="btn danger-btn">Yes, cancel booking</button> 
            </div>
        </div>
    </div>
    <script>
        // ==========================================
        // 1. EXPORT ENTIRE TABLE
        // ==========================================
         function downloadPDF() {
             const element = document.getElementById('appointmentData');
             if (!element) return alert("Error: Table ID missing.");
             
             // Add printing class to hide action column and format layout
             element.classList.add('pdf-printing');

             const header = element.querySelector('.pdf-header');
             if (header) {
                 header.style.display = 'flex';
             }

             const opt = {
                 margin: 12,
                 filename: 'CarePlus_Appointments_History.pdf',
                 image: { type: 'jpeg', quality: 0.98 },
                 html2canvas: { scale: 2 },
                 jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
             };

             html2pdf().set(opt).from(element).save().then(() => {
                 // Restore layout state
                 element.classList.remove('pdf-printing');
                 if (header) {
                     header.style.display = 'none';
                 }
             });
         }

        // ==========================================
        // 2. TOGGLE 3-DOTS DROPDOWN
        // ==========================================
        function toggleDropdown(id) {
            document.querySelectorAll('.dropdown-content').forEach(el => {
                if (el.id !== id) el.classList.remove('show-dropdown');
            });
            document.getElementById(id).classList.toggle('show-dropdown');
        }

        window.onclick = function (event) {
            if (!event.target.matches('.kebab-btn')) {
                document.querySelectorAll('.dropdown-content').forEach(el => {
                    el.classList.remove('show-dropdown');
                });
            }
        }

        // ==========================================
        // 3. CUSTOM CANCEL MODAL LOGIC
        // ==========================================
        let pendingCancelId = null;

        function confirmCancellation(bookingId) {
            pendingCancelId = bookingId;
            document.getElementById('cancelModal').classList.add('show-modal');
        }

        function closeModal() {
            document.getElementById('cancelModal').classList.remove('show-modal');
            pendingCancelId = null;
        }

        function executeCancellation() {
            if (pendingCancelId) {
                window.location.href = "cancel_appointments.php?id=" + pendingCancelId;
            } else {
                alert("Error: Could not find the booking ID.");
            }
        }

        // ==========================================
        // 4. EXPORT SINGLE TICKET
        // ==========================================
        function exportSingleTicket(bookingId, patient, doctor, specialty, date, time, paymentStatus, paymentAmount, orderId) {
             const tempDiv = document.createElement('div');
             
             // Base ticket container styling — scaled for A4
             tempDiv.style.width = '700px';
             tempDiv.style.padding = '40px';
             tempDiv.style.background = '#FFF';
             tempDiv.style.fontFamily = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
             tempDiv.style.color = '#1D1D1F';
             
             // Construct current date string
             const generatedDateStr = new Date().toLocaleDateString('en-US', { 
                 month: 'short', 
                 day: 'numeric', 
                 year: 'numeric' 
             });

             tempDiv.innerHTML = `
                 <div style="border: 1px solid #E5E5E7; border-radius: 12px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                     <!-- Header -->
                     <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #E5E5E7; padding-bottom: 20px; margin-bottom: 20px; align-items: flex-start;">
                         <div style="display: flex; align-items: center; gap: 12px;">
                             <div style="width: 48px; height: 48px; background: #EBF3FC; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #0071E3;">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><line x1="12" y1="11" x2="12" y2="15"></line><line x1="10" y1="13" x2="14" y2="13"></line></svg>
                             </div>
                             <div>
                                 <div style="font-size: 1.4rem; font-weight: 700; color: #1D1D1F; letter-spacing: -0.02em;">CarePlus Center</div>
                                 <div style="font-size: 0.85rem; color: #6E6E73; margin-top: 2px;">Official Booking Receipt</div>
                             </div>
                         </div>
                         <div style="text-align: right;">
                             <div style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; color: #6E6E73; text-transform: uppercase;">Appointment Receipt</div>
                             <div style="margin-top: 8px;">
                                 <svg id="receipt-barcode"></svg>
                             </div>
                             <div style="margin-top: 4px;">
                                 <span style="display: inline-block; background: #EBF8EF; color: #166534; border: 1px solid rgba(52, 199, 89, 0.25); font-size: 0.7rem; font-weight: 600; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.02em;">Confirmed</span>
                             </div>
                         </div>
                     </div>

                     <!-- Patient details -->
                     <div style="margin-bottom: 16px;">
                         <div style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6E6E73; margin-bottom: 6px;">Patient Information</div>
                         <div style="background: #F8F8F8; border-radius: 8px; padding: 10px; border: 1px solid #E5E5E7;">
                             <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 4px;">
                                 <span style="color: #6E6E73;">Full Name:</span>
                                 <strong style="color: #1D1D1F; font-weight: 600;">${patient}</strong>
                             </div>
                             <div style="display: flex; justify-content: space-between; font-size: 0.82rem;">
                                 <span style="color: #6E6E73;">Patient Class:</span>
                                 <strong style="color: #1D1D1F; font-weight: 500;">Registered Outpatient</strong>
                             </div>
                         </div>
                     </div>

                     <!-- Appointment details -->
                     <div style="margin-bottom: 16px;">
                         <div style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6E6E73; margin-bottom: 6px;">Appointment Details</div>
                         <div style="border: 1px solid #E5E5E7; border-radius: 8px; overflow: hidden; background: #FFFFFF;">
                             <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem; text-align: left;">
                                 <tr style="border-bottom: 1px solid #E5E5E7;">
                                     <td style="padding: 8px 10px; background: #F8F8F8; color: #6E6E73; width: 30%; font-weight: 500;">Doctor</td>
                                     <td style="padding: 8px 10px; color: #1D1D1F; font-weight: 600;">Dr. ${doctor}</td>
                                 </tr>
                                 <tr style="border-bottom: 1px solid #E5E5E7;">
                                     <td style="padding: 8px 10px; background: #F8F8F8; color: #6E6E73; font-weight: 500;">Specialty</td>
                                     <td style="padding: 8px 10px; color: #1D1D1F; font-weight: 500;">${specialty}</td>
                                 </tr>
                                 <tr style="border-bottom: 1px solid #E5E5E7;">
                                     <td style="padding: 8px 10px; background: #F8F8F8; color: #6E6E73; font-weight: 500;">Date</td>
                                     <td style="padding: 8px 10px; color: #1D1D1F; font-weight: 500;">${date}</td>
                                 </tr>
                                 <tr style="border-bottom: 1px solid #E5E5E7;">
                                     <td style="padding: 8px 10px; background: #F8F8F8; color: #6E6E73; font-weight: 500;">Time Slot</td>
                                     <td style="padding: 8px 10px; color: #1D1D1F; font-weight: 600;">${time}</td>
                                 </tr>
                                 <tr style="border-bottom: 1px solid #E5E5E7;">
                                     <td style="padding: 8px 10px; background: #F8F8F8; color: #6E6E73; font-weight: 500;">Payment Status</td>
                                     <td style="padding: 8px 10px; color: ${paymentStatus === 'Paid' ? '#15803d' : '#b91c1c'}; font-weight: 600;">
                                         ${paymentStatus} (₹${paymentAmount})
                                     </td>
                                 </tr>
                                 <tr style="border-bottom: 1px solid #E5E5E7;">
                                     <td style="padding: 8px 10px; background: #F8F8F8; color: #6E6E73; font-weight: 500;">Order Ref</td>
                                     <td style="padding: 8px 10px; color: #1D1D1F; font-weight: 500; font-size: 0.75rem;">
                                         ${orderId !== 'N/A' && orderId !== '' ? orderId : 'N/A'}
                                     </td>
                                 </tr>
                                 <tr style="border-bottom: 1px solid #E5E5E7;">
                                     <td style="padding: 12px 14px; background: #F8F8F8; color: #6E6E73; font-weight: 500;">Payment Method</td>
                                     <td style="padding: 12px 14px; color: #1D1D1F; font-weight: 500;">
                                         ${orderId !== 'N/A' && orderId !== '' ? 'Online (Cashfree Gateway)' : 'Pay at Clinic'}
                                     </td>
                                 </tr>
                                 <tr>
                                     <td style="padding: 12px 14px; background: #F8F8F8; color: #6E6E73; font-weight: 500;">Location</td>
                                     <td style="padding: 12px 14px; color: #1D1D1F; font-weight: 400;">Main Building, Clinic Suite 204</td>
                                 </tr>
                             </table>
                         </div>
                     </div>

                     <!-- Instructions -->
                     <div style="border-top: 1px dashed #E5E5E7; padding-top: 12px; font-size: 0.72rem; color: #6E6E73; line-height: 1.45;">
                         <strong style="color: #1D1D1F; display: block; margin-bottom: 2px; font-weight: 600;">Important Instructions:</strong>
                         <ul style="margin: 0; padding-left: 14px; color: #6E6E73;">
                             <li>Please arrive at least 15 minutes before your scheduled appointment time.</li>
                             <li>Present this booking confirmation receipt at the front clinic desk.</li>
                         </ul>
                     </div>

                     <!-- Auto-generated Disclaimer Note -->
                     <div style="margin-top: 14px; background: #F8F8F8; border-radius: 6px; padding: 8px; border: 1px solid #E5E5E7; font-size: 0.65rem; color: #8E8E93; text-align: center; font-style: italic;">
                         * Note: This is an auto-generated e-receipt. No physical signature is required.
                     </div>

                     <!-- Receipt Footer -->
                     <div style="margin-top: 16px; border-top: 1px solid #E5E5E7; padding-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 0.65rem; color: #A1A1A6;">
                         <span>Generated: ${generatedDateStr}</span>
                         <span>CarePlus Clinic Management System</span>
                     </div>
                 </div>
             `;

             // Render Barcode before printing
             JsBarcode(tempDiv.querySelector("#receipt-barcode"), bookingId, {
                 format: "CODE128",
                 height: 40,
                 width: 1.5,
                 displayValue: true,
                 fontSize: 14,
                 margin: 0
             });

             const opt = {
                 margin: 15,
                 filename: `CarePlus_Receipt_${bookingId}.pdf`,
                 image: { type: 'jpeg', quality: 0.99 },
                 html2canvas: { scale: 2, useCORS: true },
                 jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
             };
             
             html2pdf().set(opt).from(tempDiv).save();
         }

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