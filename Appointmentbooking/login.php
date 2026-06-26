<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
require 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Both fields are required!";
    } else {

        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with that email!";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Appointment Booking</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
</head>

<body class="center-content landing-bg">

    <div class="card glass">
        <h2>Sign In</h2>

        <?php if ($error)
            echo "<div class='alert error'>$error</div>"; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" placeholder="Email Address"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn primary-btn">Sign In</button>
        </form>

        <p class="mt-2 text-center">Don't have an account? <a href="signup.php">Sign Up</a></p>
        <p class="text-center"><a href="index.php">Back to Home</a></p>
    </div>

</body>

</html>