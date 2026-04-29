<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePlus - Doctor Appointment Booking</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="landing-bg-scroll">

    <!-- Navbar -->
    <nav class="navbar glass-nav">
        <div class="container nav-content">
            <h2 class="nav-brand flex-align">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle><line x1="12" y1="11" x2="12" y2="15"></line><line x1="10" y1="13" x2="14" y2="13"></line></svg>
                CarePlus
            </h2>
            <div class="nav-links">
                <a href="#services">Services</a>
                <a href="#why-us">Why Us</a>
                <a href="login.php" class="btn secondary-btn btn-sm">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero section-padding">
        <div class="container">
            <div class="hero-content text-center glass-card-large">
                <h1 class="hero-title">Your Health, Our Priority</h1>
                <p class="hero-subtitle">Book consultations with top medical professionals. Fast, secure, and hassle-free appointment scheduling at your fingertips.</p>
                <div class="button-group mt-4 flex-center gap-4">
                    <a href="signup.php" class="btn primary-btn btn-lg">Book an Appointment</a>
                    <a href="#services" class="btn secondary-btn btn-lg">Explore Services</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Services Section -->
    <section id="services" class="services-section section-padding bg-white">
        <div class="container">
            <div class="section-header text-center">
                <h2>Our Medical Specialties</h2>
                <p class="text-muted">Comprehensive care for you and your family.</p>
            </div>
            
            <div class="grid-3 mt-4">
                <div class="service-card text-center">
                    <div class="icon-lg primary-text">🫀</div>
                    <h3>Cardiology</h3>
                    <p>Expert heart care from diagnosis to treatment and rehabilitation.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg primary-text">🧠</div>
                    <h3>Neurology</h3>
                    <p>Advanced care for brain, spinal cord, and nervous system conditions.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg primary-text">👶</div>
                    <h3>Pediatrics</h3>
                    <p>Dedicated healthcare services for infants, children, and adolescents.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg primary-text">🦴</div>
                    <h3>Orthopedics</h3>
                    <p>Treatment for bone, joint, ligament, tendon, and muscle issues.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg primary-text">👁️</div>
                    <h3>Ophthalmology</h3>
                    <p>Comprehensive eye exams, vision care, and surgical procedures.</p>
                </div>
                <div class="service-card text-center">
                    <div class="icon-lg primary-text">🩺</div>
                    <h3>General Practice</h3>
                    <p>Routine checkups, preventive care, and primary health services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="why-us" class="why-us-section section-padding bg-light">
        <div class="container">
            <div class="section-header text-center">
                <h2>Why Choose CarePlus?</h2>
                <p class="text-muted">We provide a seamless healthcare experience.</p>
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
                        <h3>Reminders & Alerts</h3>
                        <p>Get timely notifications so you never miss an important consultation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white section-padding">
        <div class="container text-center">
            <h2>CarePlus Clinic</h2>
            <p class="mt-2">Providing quality healthcare access to everyone.</p>
            <div class="mt-4">
                <a href="signup.php" class="btn primary-btn">Get Started Now</a>
            </div>
            <p class="mt-4 text-muted small">&copy; <?php echo date('Y'); ?> CarePlus Doctor Appointment Booking. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
