<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
require 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = ucwords(strtolower($conn->real_escape_string($_POST['name'])));
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($confirm_password)) {
        $error = "All fields are required!";
    } else if ($password !== $confirm_password) {
        $error = "Password does not match!";
    } else {

        // Check if email already exists
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, phone) VALUES ('$name', '$email', '$hashed_password', '$phone')";
            if ($conn->query($sql) === TRUE) {
                // Redirect to login page with a success flag in the URL
                header("Location: login.php?signup=success");
                exit(); // Always include exit() after a header redirect
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — CarePlus</title>
    <meta name="description" content="Create a CarePlus account to start booking doctor appointments online.">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
</head>

<body class="auth-page">

    <div class="card" style="max-width:480px;">

        <!-- Brand mark -->
        <div class="flex-align gap-4" style="margin-bottom: 28px; justify-content: center;">
            <div style="width:36px; height:36px; background:var(--accent-light); border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--accent); flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                    <line x1="12" y1="11" x2="12" y2="15"></line>
                    <line x1="10" y1="13" x2="14" y2="13"></line>
                </svg>
            </div>
            <span style="font-weight:700; font-size:1.0625rem; letter-spacing:-0.02em; color:var(--text);">CarePlus</span>
        </div>

        <h2 style="font-size:1.375rem; font-weight:700; margin-bottom:6px; text-align:center; letter-spacing:-0.025em;">Create your account</h2>
        <p class="text-muted" style="text-align:center; font-size:0.875rem; margin-bottom:24px;">Join CarePlus to book doctor appointments online</p>

        <?php if ($error)
            echo "<div class='alert error'>$error</div>"; ?>
        <?php if ($success)
            echo "<div class='alert success'>$success</div>"; ?>

        <?php if (!$success): ?>
            <form action="signup.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="10-digit phone number"
                        pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters"
                        minlength="6" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Re-enter your password" minlength="6" required>
                </div>

                <button type="submit" class="btn primary-btn block-btn" style="margin-top:8px;">Create Account</button>
            </form>
        <?php endif; ?>

        <p class="mt-2 text-center" style="font-size:0.875rem;">Already have an account? <a href="login.php">Sign In</a></p>
        <p class="text-center" style="font-size:0.875rem; margin-top:6px;"><a href="index.php" style="color:var(--text-secondary);">← Back to home</a></p>
    </div>

</body>

</html>