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
        .detail-card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }
        h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
        .detail-card p {
            margin-bottom: 0.8rem;
            font-size: 1rem;
        }
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="create_worker.php"><i class="fas fa-user-plus"></i> Create Worker</a></li>
                <li><a href="reports.php"><i class="fas fa-file-invoice"></i> Reports</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 style="color: #2c3e50; margin-bottom: 0.5rem;">Review Death Registration</h1>
            <p style="color: #7f8c8d; margin-bottom: 2rem;">Assess cause, age, and details validity to approve or reject registration.</p>

            <div class="detail-card">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                    <div>
                        <h3><i class="fas fa-user-alt-slash"></i> Subject Details</h3>
                        <p><strong>Deceased Name:</strong> <?php echo $row['deceased_fullname']; ?></p>
                        <p><strong>Gender:</strong> <?php echo ucfirst($row['gender']); ?></p>
                        <p><strong>DOD:</strong> <?php echo $row['date_of_death']; ?></p>
                        <p><strong>Place:</strong> <?php echo $row['place_of_death']; ?></p>
                        <p><strong>Cause of Death:</strong> <?php echo $row['cause_of_death'] ?: 'N/A'; ?></p>
                        <p><strong>Age at Death:</strong> <?php echo $row['age_at_death']; ?> years</p>

                        <h3 style="margin-top: 2rem;"><i class="fas fa-user-friends"></i> Third-Party (Registrant) Details</h3>
                        <p><strong>Name:</strong> <?php echo $row['third_party_name']; ?></p>
                        <p><strong>National ID:</strong> <?php echo $row['third_party_nid']; ?></p>
                        <p><strong>Relation:</strong> <?php echo $row['third_party_relation']; ?></p>
                        <p><strong>Contact:</strong> <?php echo $row['third_party_contact']; ?></p>
                    </div>
                    
                    <div>
                        <h3><i class="fas fa-info-circle"></i> Processing Info</h3>
                        <p><strong>Certificate Code:</strong> <strong style="color: #2c3e50;"><?php echo $row['certificate_number']; ?></strong></p>
                        <p><strong>Payment Status:</strong> <?php echo ucfirst($row['payment_status']); ?> (K<?php echo $row['fee_amount']; ?>)</p>
                        <p><strong>Registered By:</strong> <?php echo $row['staff_name']; ?> (<?php echo $row['hospital_name']; ?>)</p>
                        
                        <?php if($row['status'] === 'approved'): ?>
                            <p style="margin-top: 1rem; margin-bottom: 1.5rem;">
                                <a href="../staff/view_certificate.php?type=death&id=<?php echo $row['id']; ?>" class="btn" style="background:#3498db; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; width: auto;"><i class="fas fa-certificate"></i> View Official Certificate</a>
                            </p>
                        <?php endif; ?>
                        
                        <h3 style="margin-top: 2rem;"><i class="fas fa-file-alt"></i> Supporting Documents</h3>
                        <p><a href="../uploads/<?php echo $row['supporting_doc']; ?>" target="_blank" class="btn btn-secondary" style="width: auto; display: inline-flex; align-items: center; gap: 0.5rem;"><i class="fas fa-external-link-alt"></i> View Uploaded Document</a></p>
                        
                        <form method="POST" style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
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
                                <textarea name="remarks" rows="3" placeholder="Explain reason if rejected..." style="border: 1px solid #ccc; width: 100%; border-radius: 8px; padding: 0.8rem;"><?php echo $row['admin_remarks']; ?></textarea>
                            </div>
                            <button type="submit" class="btn" style="margin-top: 1rem;"><i class="fas fa-save"></i> Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
