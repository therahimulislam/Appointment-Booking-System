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
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_name'])) {
    $new_name = $conn->real_escape_string($_POST['name']);
    if (empty($new_name)) {
        $error = "Name cannot be empty.";
    } else {
        $update_sql = "UPDATE users SET name='$new_name' WHERE id='$user_id'";
        if ($conn->query($update_sql) === TRUE) {
            $_SESSION['user_name'] = $new_name;
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch user details to get the current hash
    $user_check_sql = "SELECT password FROM users WHERE id='$user_id'";
    $user_check_result = $conn->query($user_check_sql);
    $user_data = $user_check_result->fetch_assoc();

    if (!password_verify($current_password, $user_data['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pwd_sql = "UPDATE users SET password='$hashed_password' WHERE id='$user_id'";
        if ($conn->query($update_pwd_sql) === TRUE) {
            $success = "Password updated successfully!";
        } else {
            $error = "Error updating password: " . $conn->error;
        }
    }
}

// Fetch user details
$sql = "SELECT * FROM users WHERE id='$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CarePlus</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" media="screen and (max-width:768px)"
        href="mobile.css?v=<?php echo time(); ?>">
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
                <a href="appointments.php">My Appointments</a>
                <a href="profile.php" class="active">Profile</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 center-content no-height">
        <div class="card glass">
            <h2>My Profile</h2>

            <?php if ($error)
                echo "<div class='alert error'>$error</div>"; ?>
            <?php if ($success)
                echo "<div class='alert success'>$success</div>"; ?>

            <div class="profile-info mt-4 mb-4">
                <div class="info-row" style="align-items: center;">
                    <span class="text-muted">Full Name:</span>

                    <div id="nameDisplay" class="flex-align">
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        <button
                            onclick="document.getElementById('nameDisplay').style.display='none'; document.getElementById('nameEdit').style.display='flex';"
                            class="btn secondary-btn btn-sm"
                            style="margin-left:10px; padding:0.25rem 0.5rem; font-size:0.75rem;">Edit</button>
                    </div>

                    <div id="nameEdit" style="display: <?php echo $error ? 'flex' : 'none'; ?>; align-items: center;">
                        <form action="profile.php" method="POST"
                            style="display:flex; align-items:center; gap:5px; margin:0;">
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                                required
                                style="padding: 0.25rem 0.5rem; font-size: 0.9rem; margin-bottom: 0; width: 200px; border: 1px solid #d1d5db; border-radius: 4px;">
                            <button type="submit" name="update_name" class="btn primary-btn btn-sm"
                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Save</button>
                            <button type="button"
                                onclick="document.getElementById('nameEdit').style.display='none'; document.getElementById('nameDisplay').style.display='flex';"
                                class="btn"
                                style="background:#e5e7eb; color:#374151; padding: 0.25rem 0.5rem; font-size: 0.8rem; border:none; border-radius:4px; cursor:pointer;">Cancel</button>
                        </form>
                    </div>
                </div>
                <div class="info-row mt-2"><span class="text-muted">Email:</span>
                    <strong><?php echo htmlspecialchars($user['email']); ?></strong></div>
                <div class="info-row mt-2"><span class="text-muted">Phone Number:</span>
                    <strong><?php echo htmlspecialchars($user['phone']); ?></strong></div>
                <div class="info-row mt-2"><span class="text-muted">Joined On:</span>
                    <strong><?php echo date('F j, Y', strtotime($user['created_at'])); ?></strong></div>
            </div>

            <hr class="mt-4 mb-4" style="border:0; border-top:1px solid #e5e7eb;">

            <div class="flex-align" style="justify-content: space-between; margin-bottom: 15px;">
                <h3 style="margin-bottom:0;">Security Settings</h3>
                <button
                    onclick="document.getElementById('pwdForm').style.display = document.getElementById('pwdForm').style.display === 'none' ? 'block' : 'none';"
                    class="btn secondary-btn btn-sm" style="padding:0.4rem 0.8rem; font-size:0.85rem;">Change
                    Password</button>
            </div>

            <div id="pwdForm"
                style="display: <?php echo (isset($_POST['update_password']) && $error) ? 'block' : 'none'; ?>; background:#f9fafb; padding:20px; border-radius:8px; border:1px solid #e5e7eb;">
                <form action="profile.php" method="POST">
                    <div class="form-group mb-2">
                        <label>Current Password:</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>New Password:</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group mb-2">
                        <label>Confirm New Password:</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                    <div class="flex-align mt-2" style="gap:10px;">
                        <button type="submit" name="update_password" class="btn primary-btn btn-sm">Update
                            Password</button>
                        <button type="button" onclick="document.getElementById('pwdForm').style.display='none'"
                            class="btn btn-sm" style="background:#e5e7eb; color:#374151; border:none;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        </div>

    <script>
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