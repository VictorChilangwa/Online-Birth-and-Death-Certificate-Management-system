<?php
include 'includes/config.php';

$nid = $conn->real_escape_string($_GET['nid'] ?? '');
$code = $conn->real_escape_string($_GET['code'] ?? '');

// Check birth applications
$result = $conn->query("SELECT * FROM birth_applications WHERE third_party_nid='$nid' AND certificate_number='$code' AND status='approved'");
$type = 'birth';
$title = "BIRTH CERTIFICATE";

if ($result->num_rows == 0) {
    // Check death applications
    $result = $conn->query("SELECT * FROM death_applications WHERE third_party_nid='$nid' AND certificate_number='$code' AND status='approved'");
    $type = 'death';
    $title = "DEATH CERTIFICATE";
}

if ($result->num_rows == 0) {
    echo "Certificate not found or verification details are incorrect.";
    exit;
}

$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official <?php echo $title; ?> - <?php echo $data['certificate_number']; ?></title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Font for Signature -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
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
        .signature-text {
            font-family: 'Great Vibes', cursive;
            font-size: 2.2rem;
            color: #2c3e50;
            margin: 0;
            padding: 0;
            line-height: 1;
            transform: rotate(-3deg);
            display: inline-block;
        }
        
        /* Actions Styling */
        .actions-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 0 auto 2rem auto;
            max-width: 800px;
        }
        .print-btn, .download-btn, .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            font-family: Arial, sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .print-btn { background: #34495e; }
        .print-btn:hover { background: #2c3e50; }
        .download-btn { background: #27ae60; }
        .download-btn:hover { background: #219653; }
        .back-btn { background: #7f8c8d; }
        .back-btn:hover { background: #95a5a6; }
        
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
            .actions-container { display: none; }
            body { background: white; padding: 0; margin: 0; }
            .certificate {
                box-shadow: none;
                border: 10px double #2c3e50;
                width: 100%;
                box-sizing: border-box;
                margin: 0;
                padding: 3rem;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="actions-container">
        <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Go Back</a>
        <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Certificate</button>
        <button class="download-btn" onclick="downloadPDF()"><i class="fas fa-download"></i> Download PDF</button>
    </div>

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
            <p class="signature-text">Natasha Banda</p>
            <p style="margin-top: 0.2rem; margin-bottom: 0.2rem;">__________________________</p>
            <p>Registrar General</p>
            <p>Issued on: <?php echo date('d M Y'); ?></p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    function downloadPDF() {
        const element = document.querySelector('.certificate');
        
        // Save original styles
        const originalBoxShadow = element.style.boxShadow;
        const originalMargin = element.style.margin;
        const originalPadding = element.style.padding;
        
        // Apply clean styles for single A4 page fit
        element.style.boxShadow = 'none';
        element.style.margin = '0';
        element.style.padding = '2.5rem';
        
        const opt = {
            margin:       15,
            filename:     '<?php echo str_replace(" ", "_", $title); ?>-<?php echo $data['certificate_number']; ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            // Restore original styles
            element.style.boxShadow = originalBoxShadow;
            element.style.margin = originalMargin;
            element.style.padding = originalPadding;
        });
    }
    </script>
</body>
</html>
