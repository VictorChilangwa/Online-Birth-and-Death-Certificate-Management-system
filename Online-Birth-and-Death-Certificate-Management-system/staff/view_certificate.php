<?php
include '../includes/config.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'birth';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($type === 'birth') {
    $result = $conn->query("SELECT b.*, u.fullname as staff_name FROM birth_applications b JOIN users u ON b.registered_by = u.id WHERE b.id=$id");
    $title = "Birth Certificate";
} else {
    $result = $conn->query("SELECT d.*, u.fullname as staff_name FROM death_applications d JOIN users u ON d.registered_by = u.id WHERE d.id=$id");
    $title = "Death Certificate";
}

if ($result->num_rows != 1) {
    echo "Certificate not found or pending approval.";
    exit;
}

$data = $result->fetch_assoc();

// Ensure the user has permission to view this certificate
// (Admin and Registrar can view all; Hospital Staff can only view their own)
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'registrar' && $data['registered_by'] != $_SESSION['user_id']) {
    echo "Access denied.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - <?php echo $data['certificate_number']; ?></title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Font for Signature -->
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --bg-light: #f4f7f6;
            --white: #ffffff;
            --text-dark: #333333;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        body { font-family: 'Times New Roman', serif; background: #f0f0f0; }
        
        /* Persistent Sidebar Styles */
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
            font-family: 'Inter', sans-serif;
        }
        .sidebar-header p {
            font-size: 0.85rem;
            color: #bdc3c7;
            font-family: 'Inter', sans-serif;
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
            font-family: 'Inter', sans-serif;
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

        /* Certificate Styles */
        .certificate {
            background: white;
            width: 800px;
            margin: 0 auto;
            padding: 3rem;
            border: 10px double #2c3e50;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            font-family: 'Times New Roman', serif;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
            width: 380px;
        }
        .watermark img {
            width: 100%;
            height: auto;
        }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 1rem; margin-bottom: 2rem; position: relative; z-index: 1; }
        .cert-logo { width: 90px; height: auto; margin-bottom: 0.5rem; }
        .republic-title { font-size: 1.3rem; font-weight: bold; color: #2c3e50; letter-spacing: 2px; margin-bottom: 0.3rem; }
        .header h1 { margin: 0; color: #2c3e50; font-family: 'Times New Roman', serif; font-size: 2.2rem; }
        .header h2 { margin: 0.5rem 0 0 0; color: #2c3e50; font-family: 'Times New Roman', serif; font-size: 1.5rem; }
        .cert-no { position: absolute; top: 20px; right: 20px; font-weight: bold; font-family: 'Times New Roman', serif; position: relative; z-index: 1; }
        .content { font-size: 1.2rem; line-height: 2; text-align: center; font-family: 'Times New Roman', serif; position: relative; z-index: 1; }
        .seal { margin-top: 3rem; text-align: right; font-family: 'Times New Roman', serif; position: relative; z-index: 1; }
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
        .print-btn, .download-btn {
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
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .print-btn { background: #34495e; }
        .print-btn:hover { background: #2c3e50; }
        .download-btn { background: #27ae60; }
        .download-btn:hover { background: #219653; }
        
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
            .sidebar { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; background: transparent !important; }
            .dashboard-wrapper { display: block !important; }
            .actions-container { display: none !important; }
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

    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><?php echo SITE_NAME; ?></h3>
                <p><?php echo $_SESSION['role'] === 'admin' ? 'Super Admin' : ($_SESSION['role'] === 'registrar' ? 'Registrar' : 'Hospital Staff'); ?></p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar'): ?>
                    <li><a href="../admin/dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                    <li><a href="../admin/create_worker.php"><i class="fas fa-user-plus"></i> Create Worker</a></li>
                    <li><a href="../admin/reports.php"><i class="fas fa-file-invoice"></i> Reports</a></li>
                <?php else: ?>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                    <li><a href="apply_birth.php"><i class="fas fa-baby"></i> Register Birth</a></li>
                    <li><a href="apply_death.php"><i class="fas fa-book-dead"></i> Register Death</a></li>
                    <li><a href="reports.php"><i class="fas fa-file-invoice"></i> Reports</a></li>
                <?php endif; ?>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="main-content">
            <div class="actions-container">
                <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Certificate</button>
                <button class="download-btn" onclick="downloadPDF()"><i class="fas fa-download"></i> Download PDF</button>
            </div>

            <div class="certificate">
                <!-- Centered Zambian Coat of Arms watermark -->
                <div class="watermark">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Coat_of_arms_of_Zambia.svg/450px-Coat_of_arms_of_Zambia.svg.png" alt="Zambia Coat of Arms">
                </div>
                <div class="cert-no">No: <?php echo $data['certificate_number']; ?></div>
                <div class="header">
                    <img src="../assets/images/Coat_of_arms_of_Zambia.svg.png" alt="Zambia Coat of Arms" class="cert-logo">
                    <div class="republic-title">REPUBLIC OF ZAMBIA</div>
                    <h1>Republic of Civil Registration</h1>
                    <h2>OFFICIAL <?php echo strtoupper($title); ?></h2>
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
        </main>
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
