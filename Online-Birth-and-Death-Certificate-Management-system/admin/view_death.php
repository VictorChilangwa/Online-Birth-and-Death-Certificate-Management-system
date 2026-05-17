<?php
include '../includes/config.php';

if (!isLoggedIn() || (!isAdmin() && !isRegistrar())) {
    header("Location: ../login.php");
    exit;
}

$id = (int)$_GET['id'];
$result = $conn->query("SELECT d.*, u.fullname as staff_name, u.hospital_name FROM death_applications d JOIN users u ON d.registered_by = u.id WHERE d.id=$id");

if ($result->num_rows == 0) {
    echo "Application not found.";
    exit;
}

$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];
    $remarks = $conn->real_escape_string($_POST['remarks']);
    
    $update = "UPDATE death_applications SET status='$status', admin_remarks='$remarks' WHERE id=$id";
    if ($conn->query($update)) {
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Death Application - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav>
        <a href="../index.php" class="logo"><?php echo SITE_NAME; ?></a>
        <ul>
            <li><a href="dashboard.php">Back to Dashboard</a></li>
        </ul>
    </nav>

    <div class="form-container" style="max-width: 900px; margin-top: 2rem;">
        <h2>Review Death Application</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
            <div>
                <h3>Subject Details</h3>
                <p><strong>Deceased Name:</strong> <?php echo $row['deceased_fullname']; ?></p>
                <p><strong>Gender:</strong> <?php echo $row['gender']; ?></p>
                <p><strong>DOD:</strong> <?php echo $row['date_of_death']; ?></p>
                <p><strong>Place:</strong> <?php echo $row['place_of_death']; ?></p>
                <p><strong>Cause:</strong> <?php echo $row['cause_of_death']; ?></p>
                <p><strong>Age:</strong> <?php echo $row['age_at_death']; ?></p>

                <h3 style="margin-top: 1.5rem;">Third-Party (Registrant) Details</h3>
                <p><strong>Name:</strong> <?php echo $row['third_party_name']; ?></p>
                <p><strong>National ID:</strong> <?php echo $row['third_party_nid']; ?></p>
                <p><strong>Relation:</strong> <?php echo $row['third_party_relation']; ?></p>
                <p><strong>Contact:</strong> <?php echo $row['third_party_contact']; ?></p>
            </div>
            <div>
                <h3>Processing Info</h3>
                <p><strong>Certificate Code:</strong> <?php echo $row['certificate_number']; ?></p>
                <p><strong>Payment Status:</strong> <?php echo ucfirst($row['payment_status']); ?> (K<?php echo $row['fee_amount']; ?>)</p>
                <p><strong>Registered By:</strong> <?php echo $row['staff_name']; ?> (<?php echo $row['hospital_name']; ?>)</p>
                
                <?php if($row['status'] === 'approved'): ?>
                    <p style="margin-top: 1rem;">
                        <a href="../staff/view_certificate.php?type=death&id=<?php echo $row['id']; ?>" class="btn" style="background:#3498db; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;"><i class="fas fa-certificate"></i> View Official Certificate</a>
                    </p>
                <?php endif; ?>
                
                <h3 style="margin-top: 1.5rem;">Supporting Documents</h3>
                <p><a href="../uploads/<?php echo $row['supporting_doc']; ?>" target="_blank" class="btn btn-secondary">View Uploaded Document</a></p>
                
                <form method="POST" style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem;">
                    <div class="form-group">
                        <label>Status Action</label>
                        <select name="status" required>
                            <option value="approved" <?php echo $row['status']=='approved'?'selected':''; ?>>Approve</option>
                            <option value="pending" <?php echo $row['status']=='pending'?'selected':''; ?>>Pending</option>
                            <option value="rejected" <?php echo $row['status']=='rejected'?'selected':''; ?>>Reject</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" rows="3" placeholder="Explain reason if rejected..."><?php echo $row['admin_remarks']; ?></textarea>
                    </div>
                    <button type="submit" class="btn">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
