<?php
include '../includes/config.php';

if (!isLoggedIn() || (!isAdmin() && !isRegistrar())) {
    header("Location: ../login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['role']);
    $hospital_name = ($role === 'hospital_staff') ? $conn->real_escape_string($_POST['hospital_name']) : NULL;
    
    // Validations
    $check_email = $conn->query("SELECT id FROM users WHERE email='$email'");
    $check_username = $conn->query("SELECT id FROM users WHERE username='$username'");
    
    if ($check_email->num_rows > 0) {
        $error = "Email address is already registered!";
    } elseif ($check_username->num_rows > 0) {
        $error = "Username is already taken!";
    } else {
        // Safe hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (fullname, email, username, password, role, hospital_name) 
                         VALUES ('$fullname', '$email', '$username', '$hashed_password', '$role', " . ($hospital_name ? "'$hospital_name'" : "NULL") . ")";
        
        if ($conn->query($insert_query)) {
            $success = "Worker account created successfully!";
        } else {
            $error = "Database Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Worker Account - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #2c3e50;
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .sidebar-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #3498db;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .sidebar-header p {
            font-size: 0.85rem;
            color: #bdc3c7;
        }
        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0;
            flex-grow: 1;
        }
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1.5rem;
            color: #ecf0f1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: rgba(255,255,255,0.05);
            color: #3498db;
            border-left-color: #3498db;
        }
        .sidebar-menu li a i {
            width: 20px;
            font-size: 1.1rem;
        }
        .main-content {
            flex-grow: 1;
            margin-left: 260px;
            padding: 2rem 3rem;
            background: #f4f7f6;
            min-height: 100vh;
        }
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><?php echo SITE_NAME; ?></h3>
                <p><?php echo isAdmin() ? 'Super Admin' : 'Registrar'; ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="create_worker.php" class="active"><i class="fas fa-user-plus"></i> Create Worker</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1>Create Worker Account</h1>
            <p style="color: #7f8c8d; margin-bottom: 2rem;">Register new hospital staff members or registrars in the system.</p>

            <div class="form-container" style="margin: 0; max-width: 600px;">
                <h2>Register Worker</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" id="workerForm">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" placeholder="e.g. John Mwansa" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="e.g. john@hospital.com" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="e.g. john_mwansa" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Minimum 6 characters" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="roleSelect" onchange="toggleHospitalInput()" required>
                            <option value="hospital_staff">Hospital Staff (Worker)</option>
                            <option value="registrar">Registrar</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group" id="hospitalGroup">
                        <label>Hospital / Clinic Name</label>
                        <input type="text" name="hospital_name" id="hospitalInput" placeholder="e.g. Lusaka General Hospital" required>
                    </div>
                    
                    <button type="submit" class="btn" style="margin-top: 1rem;"><i class="fas fa-user-plus"></i> Create Account</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function toggleHospitalInput() {
            const roleSelect = document.getElementById('roleSelect');
            const hospitalGroup = document.getElementById('hospitalGroup');
            const hospitalInput = document.getElementById('hospitalInput');
            
            if (roleSelect.value === 'hospital_staff') {
                hospitalGroup.style.display = 'block';
                hospitalInput.required = true;
            } else {
                hospitalGroup.style.display = 'none';
                hospitalInput.required = false;
                hospitalInput.value = '';
            }
        }
        // Initialize state on load
        toggleHospitalInput();
    </script>
</body>
</html>
