<?php
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
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
    <title>CarePlus — Book Doctor Appointments Online</title>
    <meta name="description"
        content="CarePlus connects you with top medical professionals for easy, secure, and hassle-free appointment booking across 10 specialties.">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>

<body class="landing-bg-scroll">

    <!-- Navbar -->
    <nav class="navbar glass-nav" id="main-nav">
        <div class="container nav-content">
            <h2 class="nav-brand flex-align">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <div class="nav-links flex-align" style="gap: 16px;" id="nav-links">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                    <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>
                <a href="#" class="active">Home</a>
                <a href="#about">About Us</a>
                <a href="#services">Services</a>
                <a href="#doctors">Our Doctors</a>
                <a href="#why-us">Why Us</a>
                <a href="#contact">Contact</a>
                <a href="login.php" class="btn secondary-btn btn-sm" style="margin-left: 8px;">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero section-padding">
        <div class="container">
            <div class="hero-content text-center glass-card-large">
                <span class="hero-badge">Trusted Healthcare Platform</span>
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
                            <span class="about-value-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                            </span>
                            <div>
                                <strong>Our Mission</strong>
                                <p>Making healthcare accessible, affordable and stress-free for every patient.</p>
                            </div>
                        </div>
                        <div class="about-value-item">
                            <span class="about-value-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                            <div>
                                <strong>Our Vision</strong>
                                <p>A world where quality healthcare is just a click away for everyone.</p>
                            </div>
                        </div>
                        <div class="about-value-item">
                            <span class="about-value-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </span>
                            <div>
                                <strong>Our Values</strong>
                                <p>Compassion, integrity, innovation, and patient-first thinking.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-stats-panel">
                    <div class="stat-big-card">
                        <div class="stat-big-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                        </div>
                        <div class="stat-big-num">5+</div>
                        <div class="stat-big-label">Years of Service</div>
                    </div>
                    <div class="stat-big-card">
                        <div class="stat-big-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="stat-big-num">20+</div>
                        <div class="stat-big-label">Specialist Doctors</div>
                    </div>
                    <div class="stat-big-card">
                        <div class="stat-big-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <div class="stat-big-num">5,000+</div>
                        <div class="stat-big-label">Appointments Booked</div>
                    </div>
                    <div class="stat-big-card">
                        <div class="stat-big-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
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
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <h3>Cardiology</h3>
                    <p>Expert heart care from diagnosis to treatment and rehabilitation.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <h3>Neurology</h3>
                    <p>Advanced care for brain, spinal cord, and nervous system conditions.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3>Pediatrics</h3>
                    <p>Dedicated healthcare services for infants, children, and adolescents.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </div>
                    <h3>Orthopedics</h3>
                    <p>Treatment for bone, joint, ligament, tendon, and muscle issues.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <h3>Ophthalmology</h3>
                    <p>Comprehensive eye exams, vision care, and surgical procedures.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    </div>
                    <h3>General Practice</h3>
                    <p>Routine checkups, preventive care, and primary health services.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                    </div>
                    <h3>Dermatology</h3>
                    <p>Skin, hair, and nail treatments by certified dermatologists.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <h3>Psychiatry</h3>
                    <p>Mental health consultations, therapy, and wellness support.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
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
                                <p class="doctor-contact">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                    <?php echo htmlspecialchars($doc['email']); ?>
                                </p>
                                <p class="doctor-contact">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.24h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 7.29 7.29l1.66-1.67a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <?php echo htmlspecialchars($doc['phone']); ?>
                                </p>
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
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <h3>24/7 Easy Booking</h3>
                        <p>Schedule your appointments anytime, anywhere without waiting on hold.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                    </div>
                    <div>
                        <h3>Verified Specialists</h3>
                        <p>All our doctors are highly qualified, board-certified, and reviewed by patients.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <h3>Secure Records</h3>
                        <p>Your medical data and appointment history are kept strictly confidential.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <div>
                        <h3>Reminders &amp; Alerts</h3>
                        <p>Get timely notifications so you never miss an important consultation.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>
                    <div>
                        <h3>10 Specialties</h3>
                        <p>Choose from a wide range of medical specialties under one platform.</p>
                    </div>
                </div>
                <div class="feature-item flex-start gap-4">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
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
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"CarePlus made it so easy to book my cardiology appointment. I had a
                        confirmed slot within minutes. Absolutely love the platform!"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">A</div>
                        <div><strong>Aisha Rahman</strong><span>Cardiac Patient</span></div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="testimonial-text">"Finding a pediatrician for my son used to be stressful. CarePlus solved
                        it — I just pick a specialty, pick a doctor, and I'm done!"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">M</div>
                        <div><strong>Mark Sullivan</strong><span>Parent</span></div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
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
                        <div class="contact-info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <strong>Address</strong>
                            <p>123 Medical Center Drive, Health City, HC 10001</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.41 2 2 0 0 1 3.6 1.24h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.8a16 16 0 0 0 7.29 7.29l1.66-1.67a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <strong>Phone</strong>
                            <p>+1 (555) 000-1234</p>
                            <p>+1 (555) 000-5678</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <strong>Email</strong>
                            <p>info@careplus.com</p>
                            <p>support@careplus.com</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-info-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <strong>Working Hours</strong>
                            <p>Mon – Fri: 8:00 AM – 8:00 PM</p>
                            <p>Sat – Sun: 9:00 AM – 5:00 PM</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-panel card glass">
                    <h3 style="margin-bottom:1.5rem; font-size:1.125rem; font-weight:600;">Send Us a Message</h3>
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
                    <h2 class="footer-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                            <line x1="12" y1="11" x2="12" y2="15"></line>
                            <line x1="10" y1="13" x2="14" y2="13"></line>
                        </svg>
                        CarePlus
                    </h2>
                    <p>Providing quality healthcare access to everyone, everywhere. Your health is our top priority.</p>
                    <div class="footer-socials">
                        <a href="#" class="social-btn" aria-label="Twitter">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Facebook">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="Instagram">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                        </a>
                        <a href="#" class="social-btn" aria-label="LinkedIn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
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
                <p>&copy; <?php echo date('Y'); ?> CarePlus. All rights reserved.</p>
                <p>Made with care for better healthcare.</p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll behaviour — adds class for solid background
        (function () {
            var nav = document.getElementById('main-nav');
            if (!nav) return;
            function onScroll() {
                if (window.scrollY > 8) {
                    nav.classList.remove('top');
                } else {
                    nav.classList.add('top');
                }
            }
            nav.classList.add('top');
            window.addEventListener('scroll', onScroll, { passive: true });
        })();

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

        // Hamburger Menu Logic
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const navLinks = document.getElementById('nav-links');
        if (mobileBtn && navLinks) {
            mobileBtn.addEventListener('click', () => {
                mobileBtn.classList.toggle('open');
                navLinks.classList.toggle('active');
            });
        }
    </script>

</body>

</html>