<?php
include '../includes/config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$type = $_GET['type'];
$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

if ($type === 'birth') {
    $result = $conn->query("SELECT * FROM birth_applications WHERE id=$id AND (registered_by=$user_id OR '$_SESSION[role]'='admin' OR '$_SESSION[role]'='registrar')");
    $title = "BIRTH CERTIFICATE";
} else {
    $result = $conn->query("SELECT * FROM death_applications WHERE id=$id AND (registered_by=$user_id OR '$_SESSION[role]'='admin' OR '$_SESSION[role]'='registrar')");
    $title = "DEATH CERTIFICATE";
}

if ($result->num_rows == 0) {
    echo "Certificate not found or you don't have permission to view it.";
    exit;
}

$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - <?php echo $data['certificate_number']; ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; background: #f0f0f0; padding: 2rem; }
        .certificate {
            background: white;
            width: 800px;
            margin: 0 auto;
            padding: 3rem;
            border: 10px double #2c3e50;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 1rem; margin-bottom: 2rem; }
        .header h1 { margin: 0; color: #2c3e50; }
        .cert-no { position: absolute; top: 20px; right: 20px; font-weight: bold; }
        .content { font-size: 1.2rem; line-height: 2; text-align: center; }
        .seal { margin-top: 3rem; text-align: right; }
        .print-btn { display: block; width: 150px; margin: 2rem auto; padding: 1rem; background: #34495e; color: white; border: none; cursor: pointer; text-align: center; border-radius: 5px; }
        @media print {
            .print-btn { display: none; }
            body { background: white; padding: 0; }
            .certificate { box-shadow: none; border: 5px solid #000; width: 100%; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()">Print Certificate</button>

    <div class="certificate">
        <div class="cert-no">No: <?php echo $data['certificate_number']; ?></div>
        <div class="header">
            <h1>Republic of Civil Registration</h1>
            <h2>OFFICIAL <?php echo $title; ?></h2>
        </div>

        <div class="content">
            <?php if($type === 'birth'): ?>
                <p>This is to certify that according to the registers of the Civil Registration Office,</p>
                <p><strong><?php echo strtoupper($data['child_fullname']); ?></strong></p>
                <p>was born on <strong><?php echo date('jS F, Y', strtotime($data['date_of_birth'])); ?></strong></p>
                <p>at <strong><?php echo $data['place_of_birth']; ?></strong></p>
                <p>Gender: <strong><?php echo ucfirst($data['gender']); ?></strong></p>
                <p>Father: <strong><?php echo $data['father_fullname']; ?></strong></p>
                <p>Mother: <strong><?php echo $data['mother_fullname']; ?></strong></p>
            <?php else: ?>
                <p>This is to certify that according to the registers of the Civil Registration Office,</p>
                <p><strong><?php echo strtoupper($data['deceased_fullname']); ?></strong></p>
                <p>died on <strong><?php echo date('jS F, Y', strtotime($data['date_of_death'])); ?></strong></p>
                <p>at <strong><?php echo $data['place_of_death']; ?></strong></p>
                <p>Gender: <strong><?php echo ucfirst($data['gender']); ?></strong></p>
                <p>Age: <strong><?php echo $data['age_at_death']; ?> years</strong></p>
                <p>Cause of Death: <strong><?php echo $data['cause_of_death']; ?></strong></p>
            <?php endif; ?>
        </div>

        <div class="seal">
            <p>__________________________</p>
            <p>Registrar General</p>
            <p>Issued on: <?php echo date('d M Y'); ?></p>
        </div>
    </div>

</body>
</html>
