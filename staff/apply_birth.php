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
    $child_fullname = $conn->real_escape_string($_POST['child_fullname']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $place = $conn->real_escape_string($_POST['place']);
    $father = $conn->real_escape_string($_POST['father']);
    $mother = $conn->real_escape_string($_POST['mother']);
    
    $tp_name = $conn->real_escape_string($_POST['tp_name']);
    $tp_nid = $conn->real_escape_string($_POST['tp_nid']);
    $tp_rel = $conn->real_escape_string($_POST['tp_rel']);
    $tp_contact = $conn->real_escape_string($_POST['tp_contact']);
    
    $payment_status = $_POST['payment_status'];
    
    // Generate unique code: BRT-YYYY-RANDOM
    $year = date("Y");
    $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    $cert_code = "BRT-$year-$random";
    
    // Logic for file upload (simplified)
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir);
    
    $file_name = time() . "_" . basename($_FILES["supporting_doc"]["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES["supporting_doc"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO birth_applications (certificate_number, child_fullname, gender, date_of_birth, place_of_birth, father_fullname, mother_fullname, third_party_name, third_party_nid, third_party_relation, third_party_contact, fee_amount, payment_status, supporting_doc, registered_by, status) 
                VALUES ('$cert_code', '$child_fullname', '$gender', '$dob', '$place', '$father', '$mother', '$tp_name', '$tp_nid', '$tp_rel', '$tp_contact', 35.00, '$payment_status', '$file_name', '$registered_by', 'pending')";
        
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
    <title>Register Birth - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav>
        <a href="../index.php" class="logo"><?php echo SITE_NAME; ?></a>
        <ul>
            <li><a href="dashboard.php">Back to Dashboard</a></li>
        </ul>
    </nav>

    <div class="form-container" style="max-width: 800px; margin-top: 2rem;">
        <h2>Register New Birth Event</h2>
        <?php if($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #ccc; padding-bottom: 0.5rem;">Subject Information</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Child's Full Name</label>
                    <input type="text" name="child_fullname" required>
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
                    <label>Date of Birth</label>
                    <input type="date" name="dob" required>
                </div>
                <div class="form-group">
                    <label>Place of Birth</label>
                    <input type="text" name="place" placeholder="Hospital or City" required>
                </div>
                <div class="form-group">
                    <label>Father's Full Name</label>
                    <input type="text" name="father">
                </div>
                <div class="form-group">
                    <label>Mother's Full Name</label>
                    <input type="text" name="mother" required>
                </div>
            </div>

            <h3 style="margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid #ccc; padding-bottom: 0.5rem;">Third-Party Details (Registrant)</h3>
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
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                        <option value="Guardian">Guardian</option>
                        <option value="Relative">Relative</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="tp_contact" required>
                </div>
            </div>

            <h3 style="margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid #ccc; padding-bottom: 0.5rem;">Payment & Documents</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Application Fee</label>
                    <input type="text" value="K35" readonly style="background-color: #eee;">
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
                <label>Supporting Document (Proof of birth, e.g., Hospital Record)</label>
                <input type="file" name="supporting_doc" required>
            </div>
            <button type="submit" class="btn" style="margin-top: 1rem;">Submit Registration</button>
        </form>
    </div>
</body>
</html>
