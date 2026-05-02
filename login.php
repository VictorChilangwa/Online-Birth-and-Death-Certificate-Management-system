<?php
include 'includes/config.php';

if (isLoggedIn()) {
    if (isAdmin() || isRegistrar()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: staff/dashboard.php");
    }
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];
            
            if ($user['role'] === 'admin' || $user['role'] === 'registrar') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: staff/dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="verify.php">Verify</a></li>
        </ul>
    </nav>

    <div class="form-container">
        <h2>Staff Login</h2>
        <?php if($error): ?>
            <div style="color:red; margin-bottom:1rem;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
