<?php
include 'includes/config.php';

$error = "";
$certificate = null;
$type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nid = $conn->real_escape_string($_POST['nid']);
    $code = $conn->real_escape_string($_POST['code']);
    
    // Check birth applications
    $sql_birth = "SELECT * FROM birth_applications WHERE third_party_nid='$nid' AND certificate_number='$code' AND status='approved'";
    $res_birth = $conn->query($sql_birth);
    
    if ($res_birth->num_rows > 0) {
        $certificate = $res_birth->fetch_assoc();
        $type = 'birth';
    } else {
        // Check death applications
        $sql_death = "SELECT * FROM death_applications WHERE third_party_nid='$nid' AND certificate_number='$code' AND status='approved'";
        $res_death = $conn->query($sql_death);
        
        if ($res_death->num_rows > 0) {
            $certificate = $res_death->fetch_assoc();
            $type = 'death';
        } else {
            $error = "No approved certificate found matching the provided National ID and Certificate Code.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cert-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 2rem; border-top: 5px solid #2ecc71; }
        .cert-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="login.php">Staff Login</a></li>
        </ul>
    </nav>

    <div class="form-container" style="max-width: 600px; margin-top: 3rem;">
        <h2>Verify Certificate Authenticity</h2>
        <p style="margin-bottom: 1.5rem; color: #555;">Enter the National ID of the registrant and the unique Certificate Code to verify an official record.</p>
        
        <?php if($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Registrant National ID</label>
                <input type="text" name="nid" placeholder="e.g. 123456/78/1" required>
            </div>
            <div class="form-group">
                <label>Certificate Code</label>
                <input type="text" name="code" placeholder="e.g. BRT-2026-000145" required>
            </div>
            <button type="submit" class="btn">Verify Certificate</button>
        </form>

        <?php if($certificate): ?>
            <div class="cert-card">
                <div class="cert-header">
                    <h3><?php echo strtoupper($type); ?> CERTIFICATE</h3>
                    <span style="background: #2ecc71; color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">VERIFIED</span>
                </div>
                <div style="line-height: 1.8;">
                    <?php if($type === 'birth'): ?>
                        <p><strong>Certificate No:</strong> <?php echo $certificate['certificate_number']; ?></p>
                        <p><strong>Child's Name:</strong> <?php echo $certificate['child_fullname']; ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo date('F j, Y', strtotime($certificate['date_of_birth'])); ?></p>
                        <p><strong>Place of Birth:</strong> <?php echo $certificate['place_of_birth']; ?></p>
                        <p><strong>Gender:</strong> <?php echo ucfirst($certificate['gender']); ?></p>
                        <p><strong>Registered By:</strong> <?php echo $certificate['third_party_name']; ?> (<?php echo $certificate['third_party_relation']; ?>)</p>
                    <?php else: ?>
                        <p><strong>Certificate No:</strong> <?php echo $certificate['certificate_number']; ?></p>
                        <p><strong>Deceased's Name:</strong> <?php echo $certificate['deceased_fullname']; ?></p>
                        <p><strong>Date of Death:</strong> <?php echo date('F j, Y', strtotime($certificate['date_of_death'])); ?></p>
                        <p><strong>Place of Death:</strong> <?php echo $certificate['place_of_death']; ?></p>
                        <p><strong>Age at Death:</strong> <?php echo $certificate['age_at_death']; ?></p>
                        <p><strong>Registered By:</strong> <?php echo $certificate['third_party_name']; ?> (<?php echo $certificate['third_party_relation']; ?>)</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
