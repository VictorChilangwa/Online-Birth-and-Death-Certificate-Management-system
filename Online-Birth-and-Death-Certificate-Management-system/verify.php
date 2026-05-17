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
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid #eee; padding-top: 1.5rem; display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="view_official_certificate.php?nid=<?php echo urlencode($nid); ?>&code=<?php echo urlencode($code); ?>" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; background: #34495e; text-decoration: none; padding: 0.8rem 1.5rem; font-family: Arial, sans-serif; font-weight: 600;"><i class="fas fa-eye"></i> View Certificate</a>
                    <button onclick="downloadOfficialCertificate('<?php echo addslashes($nid); ?>', '<?php echo addslashes($code); ?>', '<?php echo strtoupper($type); ?>_CERTIFICATE-<?php echo $code; ?>.pdf')" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; background: #27ae60; border: none; cursor: pointer; padding: 0.8rem 1.5rem; font-family: Arial, sans-serif; font-weight: 600; color: white;"><i class="fas fa-download"></i> Download PDF</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    function downloadOfficialCertificate(nid, code, filename) {
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        
        fetch(`view_official_certificate.php?nid=${encodeURIComponent(nid)}&code=${encodeURIComponent(code)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(htmlString => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlString, 'text/html');
                
                const certificateEl = doc.querySelector('.certificate');
                const styleEls = doc.querySelectorAll('style, link');
                
                if (!certificateEl) {
                    throw new Error('Certificate layout not found in public viewer.');
                }
                
                // Style for perfect A4 page fit in background download
                certificateEl.style.boxShadow = 'none';
                certificateEl.style.margin = '0';
                certificateEl.style.padding = '2.5rem';
                
                const tempDiv = document.createElement('div');
                tempDiv.style.position = 'absolute';
                tempDiv.style.left = '-9999px';
                tempDiv.style.top = '-9999px';
                tempDiv.style.width = '800px';
                
                styleEls.forEach(el => tempDiv.appendChild(el.cloneNode(true)));
                tempDiv.appendChild(certificateEl);
                document.body.appendChild(tempDiv);
                
                const opt = {
                    margin:       15,
                    filename:     filename,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                setTimeout(() => {
                    html2pdf().set(opt).from(tempDiv).save().then(() => {
                        document.body.removeChild(tempDiv);
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                    }).catch(err => {
                        document.body.removeChild(tempDiv);
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                        alert('PDF generation failed: ' + err.message);
                    });
                }, 500);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                alert('Failed to download certificate: ' + err.message);
            });
    }
    </script>
</body>
</html>
