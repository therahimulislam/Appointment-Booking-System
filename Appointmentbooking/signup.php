<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
require 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];

    if(empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "All fields are required!";
    } else {
        // Check if email already exists
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, phone) VALUES ('$name', '$email', '$hashed_password', '$phone')";
            if($conn->query($sql) === TRUE) {
                $success = "Registration successful! You can now <a href='login.php'>Sign In</a>.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Appointment Booking</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="center-content landing-bg">

    <div class="card glass">
        <h2>Sign Up</h2>
        
        <?php if($error) echo "<div class='alert error'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert success'>$success</div>"; ?>
        
        <?php if(!$success): ?>
        <form action="signup.php" method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number:</label>
                <input type="tel" name="phone" placeholder="Phone Number" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn primary-btn">Sign Up</button>
        </form>
        <?php endif; ?>
        
        <p class="mt-2 text-center">Already have an account? <a href="login.php">Sign In</a></p>
        <p class="text-center"><a href="index.php">Back to Home</a></p>
    </div>

</body>
</html>
