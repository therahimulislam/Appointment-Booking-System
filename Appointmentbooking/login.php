<?php
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — CarePlus</title>
    <meta name="description" content="Sign in to your CarePlus account to manage your appointments.">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>

<body class="auth-page">

    <div class="card">

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

        <h2 style="font-size:1.375rem; font-weight:700; margin-bottom:6px; text-align:center; letter-spacing:-0.025em;">Welcome back</h2>
        <p class="text-muted" style="text-align:center; font-size:0.875rem; margin-bottom:24px;">Sign in to your account to continue</p>

        <?php if ($error)
            echo "<div class='alert error'>$error</div>"; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Your password" required>
            </div>

            <button type="submit" class="btn primary-btn block-btn" style="margin-top:8px;">Sign In</button>
        </form>

        <p class="mt-2 text-center" style="font-size:0.875rem;">Don't have an account? <a href="signup.php">Create one</a></p>
        <p class="text-center" style="font-size:0.875rem; margin-top:6px;"><a href="index.php" style="color:var(--text-secondary);">← Back to home</a></p>
    </div>

</body>

</html>