<?php
include '../includes/config.php';

if (!isLoggedIn() || (!isStaff() && !isAdmin())) {
    header("Location: ../login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registered_by = $_SESSION['user_id'];
    $deceased_fullname = $conn->real_escape_string($_POST['deceased_fullname']);
    $gender = $_POST['gender'];
    $dod = $_POST['dod'];
    $place = $conn->real_escape_string($_POST['place']);
    $cause = $conn->real_escape_string($_POST['cause']);
    $age = (int)$_POST['age'];
    
    $tp_name = $conn->real_escape_string($_POST['tp_name']);
    $tp_nid = $conn->real_escape_string($_POST['tp_nid']);
    $tp_rel = $conn->real_escape_string($_POST['tp_rel']);
    $tp_contact = $conn->real_escape_string($_POST['tp_contact']);
    
    $payment_status = $_POST['payment_status'];
    
    // Generate unique code: DTH-YYYY-RANDOM
    $year = date("Y");
    $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    $cert_code = "DTH-$year-$random";
    
    // Logic for file upload (simplified)
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    
    $file_name = time() . "_" . basename($_FILES["supporting_doc"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["supporting_doc"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO death_applications (certificate_number, deceased_fullname, gender, date_of_death, place_of_death, cause_of_death, age_at_death, third_party_name, third_party_nid, third_party_relation, third_party_contact, fee_amount, payment_status, supporting_doc, registered_by, status) 
                VALUES ('$cert_code', '$deceased_fullname', '$gender', '$dod', '$place', '$cause', $age, '$tp_name', '$tp_nid', '$tp_rel', '$tp_contact', 45.00, '$payment_status', '$file_name', '$registered_by', 'pending')";
        
        if ($conn->query($sql)) {
            $success = "Application registered successfully! Certificate Code: <strong>$cert_code</strong>";
        } else {
            $error = "Database error: " . $conn->error;
        }
    } else {
        $error = "Failed to upload document.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Death - <?php echo SITE_NAME; ?></title>
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
    </style>
</head>
<body>

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><?php echo SITE_NAME; ?></h3>
                <p>Hospital Staff</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="apply_birth.php"><i class="fas fa-baby"></i> Register Birth</a></li>
                <li><a href="apply_death.php" class="active"><i class="fas fa-book-dead"></i> Register Death</a></li>
                <li><a href="reports.php"><i class="fas fa-file-invoice"></i> Reports</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="form-container" style="max-width: 800px; margin: 0; padding: 2.5rem;">
                <h2>Register New Death Event</h2>
                
                <?php if($error): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; font-weight: 600;"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; font-weight: 600;"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; color: #2c3e50;"><i class="fas fa-info-circle"></i> Subject Information</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Deceased's Full Name</label>
                            <input type="text" name="deceased_fullname" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date of Death</label>
                            <input type="date" name="dod" required>
                        </div>
                        <div class="form-group">
                            <label>Place of Death</label>
                            <input type="text" name="place" placeholder="Hospital or City" required>
                        </div>
                        <div class="form-group">
                            <label>Cause of Death</label>
                            <input type="text" name="cause" required>
                        </div>
                        <div class="form-group">
                            <label>Age at Death</label>
                            <input type="number" name="age" required min="0">
                        </div>
                    </div>

                    <h3 style="margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; color: #2c3e50;"><i class="fas fa-user-friends"></i> Third-Party Details (Registrant)</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="tp_name" required>
                        </div>
                        <div class="form-group">
                            <label>National ID Number</label>
                            <input type="text" name="tp_nid" required>
                        </div>
                        <div class="form-group">
                            <label>Relationship</label>
                            <select name="tp_rel" required>
                                <option value="Next of Kin">Next of Kin</option>
                                <option value="Relative">Relative</option>
                                <option value="Legal Representative">Legal Representative</option>
                                <option value="Hospital Official">Hospital Official</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="tp_contact" required>
                        </div>
                    </div>

                    <h3 style="margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; color: #2c3e50;"><i class="fas fa-credit-card"></i> Payment & Documents</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Application Fee</label>
                            <input type="text" value="K45" readonly style="background-color: #eee;">
                        </div>
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select name="payment_status" required>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label>Supporting Document (Proof of death, e.g., Doctor's Report)</label>
                        <input type="file" name="supporting_doc" required style="border: 1px dashed #ccc; padding: 1.5rem; background: #fafafa; text-align: center; cursor: pointer;">
                    </div>
                    <button type="submit" class="btn" style="margin-top: 1.5rem;"><i class="fas fa-paper-plane"></i> Submit Registration</button>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
