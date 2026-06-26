<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
require 'db_connect.php';
$doctors_res = $conn->query("SELECT * FROM doctors ORDER BY specialty ASC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePlus - Doctor Appointment Booking</title>
    <meta name="description"
        content="CarePlus connects you with top medical professionals for easy, secure, and hassle-free appointment booking across 10 specialties.">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
</head>

<body class="landing-bg-scroll">

    <!-- Navbar -->
    <nav class="navbar glass-nav" id="main-nav">
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
            <button class="mobile-menu-btn" onclick="document.querySelector('.nav-links').classList.toggle('active')"
                style="background: none; border: none; font-size: 1.8rem; cursor: pointer; display: none;">
                ☰
            </button>
            <div class="nav-links">
                <a href="#" class="active">Home</a>
                <a href="#about">About Us</a>
                <a href="#services">Services</a>
                <a href="#doctors">Our Doctors</a>
                <a href="#why-us">Why Us</a>
                <a href="#contact">Contact</a>
                <a href="login.php" class="btn secondary-btn btn-sm">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero section-padding">
        <div class="container">
            <div class="hero-content text-center glass-card-large">
                <span class="hero-badge">🏥 Trusted Healthcare Platform</span>
                <h1 class="hero-title">Your Health,<br><span class="hero-title-accent">Our Priority</span></h1>
                <p class="hero-subtitle">Book consultations with top medical professionals. Fast, secure, and
                    hassle-free appointment scheduling at your fingertips.</p>
                <div class="button-group mt-4 flex-center gap-4">
                    <a href="signup.php" class="btn primary-btn btn-lg" id="hero-cta">Book an Appointment</a>
                    <a href="#services" class="btn secondary-btn btn-lg">Explore Services</a>
                </div>
                <div class="hero-stats mt-4">
                    <div class="hero-stat"><strong>20+</strong><span>Doctors</span></div>
                    <div class="hero-stat"><strong>10</strong><span>Specialties</span></div>
                    <div class="hero-stat"><strong>24/7</strong><span>Booking</span></div>
                    <div class="hero-stat"><strong>100%</strong><span>Secure</span></div>
                </div>
            </div>
        </div>
    </header>

    <!-- About Us Section -->
    <section id="about" class="about-section section-padding bg-white">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <span class="section-badge">About CarePlus</span>
                    <h2 class="section-title">Connecting Patients with the Best Medical Care</h2>
                    <p class="about-desc">CarePlus was founded with a single mission — to make quality healthcare
                        accessible to everyone. We believe that booking a doctor's appointment should be as simple as a
                        few clicks, with no waiting on hold and no paperwork hassle.</p>
                    <p class="about-desc">Our platform brings together a curated network of board-certified specialists
                        across 10 medical fields, ensuring you always get expert care tailored to your needs.</p>
                    <div class="about-values mt-4">
                        <div class="about-value-item">
                            <span class="about-value-icon">🎯</span>
                            <div>
                                <strong>Our Mission</strong>
                                <p>Making healthcare accessible, affordable and stress-free for every patient.</p>
                            </div>
                        </div>
                        <div class="about-value-item">
                            <span class="about-value-icon">👁️</span>
                            <div>
                                <strong>Our Vision</strong>
                                <p>A world where quality healthcare is just a click away for everyone.</p>
                            </div>
                        </div>
                        <div class="about-value-item">
                            <span class="about-value-icon">💎</span>
                            <div>
                                <strong>Our Values</strong>
                                <p>Compassion, integrity, innovation, and patient-first thinking.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-stats-panel">
                    <div class="stat-big-card">
                        <div class="stat-big-icon">🏅</div>
                        <div class="stat-big-num">5+</div>
                        <div class="stat-big-label">Years of Service</div>
                    </div>
                    <div class="stat-big-card">
                        <div class="stat-big-icon">👨‍⚕️</div>
                        <div class="stat-big-num">20+</div>
                        <div class="stat-big-label">Specialist Doctors</div>
                    </div>
                    <div class="stat-big-card">
                        <div class="stat-big-icon">📅</div>
                        <div class="stat-big-num">5,000+</div>
                        <div class="stat-big-label">Appointments Booked</div>
                    </div>
                    <div class="stat-big-card">
                        <div class="stat-big-icon">⭐</div>
                        <div class="stat-big-num">4.9/5</div>
                        <div class="stat-big-label">Patient Rating</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section section-padding bg-light">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-badge">What We Offer</span>
                <h2 class="section-title">Our Medical Specialties</h2>
                <p class="text-muted">Comprehensive care across 10 medical fields, all under one roof.</p>
            </div>
            <div class="grid-3 mt-4">
                <div class="service-card text-center">
                    <div class="icon-lg">🫀</div>
                    <h3>Cardiology</h3>
                    <p>Expert heart care from diagnosis to treatment and rehabilitation.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">🧠</div>
                    <h3>Neurology</h3>
                    <p>Advanced care for brain, spinal cord, and nervous system conditions.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">👶</div>
                    <h3>Pediatrics</h3>
                    <p>Dedicated healthcare services for infants, children, and adolescents.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">🦴</div>
                    <h3>Orthopedics</h3>
                    <p>Treatment for bone, joint, ligament, tendon, and muscle issues.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">👁️</div>
                    <h3>Ophthalmology</h3>
                    <p>Comprehensive eye exams, vision care, and surgical procedures.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">🩺</div>
                    <h3>General Practice</h3>
                    <p>Routine checkups, preventive care, and primary health services.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">🧴</div>
                    <h3>Dermatology</h3>
                    <p>Skin, hair, and nail treatments by certified dermatologists.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">🧘</div>
                    <h3>Psychiatry</h3>
                    <p>Mental health consultations, therapy, and wellness support.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">🦷</div>
                    <h3>Dentistry</h3>
                    <p>Complete dental care from cleanings to oral surgery and orthodontics.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Doctors Section -->
    <section id="doctors" class="doctors-section section-padding bg-white">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-badge">Meet the Team</span>
                <h2 class="section-title">Our Expert Doctors</h2>
                <p class="text-muted">Board-certified specialists committed to your well-being.</p>
            </div>
            <div class="doctors-grid mt-4">
                <?php if ($doctors_res && $doctors_res->num_rows > 0): ?>
                    <?php while ($doc = $doctors_res->fetch_assoc()): ?>
                        <div class="doctor-card">
                            <div class="doctor-avatar"><?php echo strtoupper(substr($doc['name'], 0, 1)); ?></div>
                            <div class="doctor-info">
                                <h3 class="doctor-name">Dr. <?php echo htmlspecialchars($doc['name']); ?></h3>
                                <span class="doctor-specialty-badge"><?php echo htmlspecialchars($doc['specialty']); ?></span>
                                <p class="doctor-contact">📧 <?php echo htmlspecialchars($doc['email']); ?></p>
                                <p class="doctor-contact">📞 <?php echo htmlspecialchars($doc['phone']); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="signup.php" class="btn primary-btn">Book with a Doctor</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="why-us" class="why-us-section section-padding bg-light">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-badge">Why CarePlus</span>
                <h2 class="section-title">Why Choose CarePlus?</h2>
                <p class="text-muted">We provide a seamless healthcare experience from start to finish.</p>
            </div>
            <div class="grid-2 mt-4">
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">⏰</div>
                    <div>
                        <h3>24/7 Easy Booking</h3>
                        <p>Schedule your appointments anytime, anywhere without waiting on hold.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">👨‍⚕️</div>
                    <div>
                        <h3>Verified Specialists</h3>
                        <p>All our doctors are highly qualified, board-certified, and reviewed by patients.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">🔒</div>
                    <div>
                        <h3>Secure Records</h3>
                        <p>Your medical data and appointment history are kept strictly confidential.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">📱</div>
                    <div>
                        <h3>Reminders &amp; Alerts</h3>
                        <p>Get timely notifications so you never miss an important consultation.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">💊</div>
                    <div>
                        <h3>10 Specialties</h3>
                        <p>Choose from a wide range of medical specialties under one platform.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">⚡</div>
                    <div>
                        <h3>Instant Confirmation</h3>
                        <p>Receive instant booking confirmation with a unique booking ID.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section section-padding bg-white">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-badge">Patient Stories</span>
                <h2 class="section-title">What Our Patients Say</h2>
                <p class="text-muted">Real experiences from people who trust CarePlus.</p>
            </div>
            <div class="testimonials-grid mt-4">
                <div class="testimonial-card">
                    <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"CarePlus made it so easy to book my cardiology appointment. I had a
                        confirmed slot within minutes. Absolutely love the platform!"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">A</div>
                        <div><strong>Aisha Rahman</strong><span>Cardiac Patient</span></div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"Finding a pediatrician for my son used to be stressful. CarePlus solved
                        it — I just pick a specialty, pick a doctor, and I'm done!"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">M</div>
                        <div><strong>Mark Sullivan</strong><span>Parent</span></div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">"The secure booking system and instant confirmation gave me peace of
                        mind. I highly recommend CarePlus to anyone looking for quality healthcare."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">P</div>
                        <div><strong>Priya Sharma</strong><span>Regular Patient</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section section-padding bg-light">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-badge">Get in Touch</span>
                <h2 class="section-title">Contact Us</h2>
                <p class="text-muted">Have questions? We're here to help you 24/7.</p>
            </div>
            <div class="contact-grid mt-4">
                <div class="contact-info-panel">
                    <div class="contact-info-item">
                        <div class="contact-info-icon">📍</div>
                        <div>
                            <strong>Address</strong>
                            <p>123 Medical Center Drive, Health City, HC 10001</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">📞</div>
                        <div>
                            <strong>Phone</strong>
                            <p>+1 (555) 000-1234</p>
                            <p>+1 (555) 000-5678</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">📧</div>
                        <div>
                            <strong>Email</strong>
                            <p>info@careplus.com</p>
                            <p>support@careplus.com</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">🕐</div>
                        <div>
                            <strong>Working Hours</strong>
                            <p>Mon – Fri: 8:00 AM – 8:00 PM</p>
                            <p>Sat – Sun: 9:00 AM – 5:00 PM</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-panel card glass">
                    <h3 style="margin-bottom:1.5rem;">Send Us a Message</h3>
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Your Email</label>
                        <input type="email" placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" placeholder="How can we help?">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea rows="4" placeholder="Write your message here..."></textarea>
                    </div>
                    <button class="btn primary-btn block-btn mt-2">Send Message</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white">
        <div class="footer-main">
            <div class="container footer-grid">
                <div class="footer-brand">
                    <h2 class="footer-logo">🏥 CarePlus</h2>
                    <p>Providing quality healthcare access to everyone, everywhere. Your health is our top priority.</p>
                    <div class="footer-socials">
                        <a href="#" class="social-btn">🐦</a>
                        <a href="#" class="social-btn">📘</a>
                        <a href="#" class="social-btn">📸</a>
                        <a href="#" class="social-btn">💼</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#doctors">Our Doctors</a></li>
                        <li><a href="#why-us">Why Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Specialties</h4>
                    <ul>
                        <li><a href="signup.php">Cardiology</a></li>
                        <li><a href="signup.php">Neurology</a></li>
                        <li><a href="signup.php">Pediatrics</a></li>
                        <li><a href="signup.php">Orthopedics</a></li>
                        <li><a href="signup.php">Dermatology</a></li>
                        <li><a href="signup.php">Psychiatry</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Patient Resources</h4>
                    <ul>
                        <li><a href="signup.php">Book Appointment</a></li>
                        <li><a href="login.php">Patient Login</a></li>
                        <li><a href="signup.php">Register</a></li>
                        <li><a href="#contact">Emergency Contact</a></li>
                        <li><a href="#about">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container footer-bottom-content">
                <p>&copy; <?php echo date('Y'); ?> CarePlus Doctor Appointment Booking. All rights reserved.</p>
                <p>Made with ❤️ for better healthcare.</p>
            </div>
        </div>
    </footer>

</body>

</html>