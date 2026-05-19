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

// Generate dynamic verification link
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$verify_url = "$protocol://$host/Online-Birth-and-Death-Certificate-Management-system/view_official_certificate.php?nid=" . urlencode($nid) . "&code=" . urlencode($code);
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official <?php echo $title; ?> - <?php echo $data['certificate_number']; ?></title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts for Premium Certificates -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;800&family=Montserrat:wght@400;500;600;700&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Zambian Colors */
            --zam-green: #198A00;
            --zam-copper: #EF7D1A;
            --zam-red: #DE2010;
            --zam-black: #000000;
        }
        body { font-family: 'Times New Roman', serif; background: #f0f0f0; padding: 2rem; }
        
        /* Certificate Styles */
        .certificate-container {
            width: 820px;
            margin: 0 auto;
            position: relative;
        }
        .certificate {
            background: #FAF8F5 url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" stroke="%23e8e2d5" stroke-width="0.5" fill="none"/><circle cx="50" cy="50" r="30" stroke="%23e8e2d5" stroke-width="0.3" stroke-dasharray="2 2" fill="none"/></svg>') repeat;
            width: 820px;
            padding: 3.5rem;
            border: 8px solid var(--zam-green);
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            font-family: 'EB Garamond', serif;
            overflow: hidden;
        }
        /* Inner Copper Border */
        .certificate::before {
            content: '';
            position: absolute;
            top: 6px;
            left: 6px;
            right: 6px;
            bottom: 6px;
            border: 2px solid var(--zam-copper);
            pointer-events: none;
            z-index: 1;
        }
        /* Corner Security Ornaments */
        .corner-ornament {
            position: absolute;
            width: 25px;
            height: 25px;
            border: 2px solid var(--zam-green);
            z-index: 2;
        }
        .top-left { top: 12px; left: 12px; border-right: none; border-bottom: none; }
        .top-right { top: 12px; right: 12px; border-left: none; border-bottom: none; }
        .bottom-left { bottom: 12px; left: 12px; border-right: none; border-top: none; }
        .bottom-right { bottom: 12px; right: 12px; border-left: none; border-top: none; }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.055;
            z-index: 0;
            pointer-events: none;
            width: 420px;
            user-select: none;
        }
        .watermark img {
            width: 100%;
            height: auto;
        }
        
        /* Government Header */
        .gov-header {
            text-align: center;
            border-bottom: 2px solid var(--zam-green);
            padding-bottom: 1.2rem;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        .cert-logo {
            width: 80px;
            height: auto;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }
        .country-title {
            font-family: 'Cinzel', serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--zam-green);
            letter-spacing: 4px;
            margin-bottom: 0.2rem;
        }
        .ministry-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 1.5px;
            margin-bottom: 0.1rem;
        }
        .dept-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.65rem;
            font-weight: 600;
            color: #555;
            letter-spacing: 1px;
            margin-bottom: 0.8rem;
        }
        .main-cert-title {
            font-family: 'Cinzel', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #111;
            letter-spacing: 2px;
            margin: 0.5rem 0;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.05);
        }
        .act-title {
            font-family: 'EB Garamond', serif;
            font-size: 0.85rem;
            font-style: italic;
            color: #444;
            letter-spacing: 0.5px;
        }
        
        /* Registry Structured Grid */
        .registry-grid {
            border: 1.5px solid var(--zam-green);
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(1px);
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .grid-row {
            display: flex;
            border-bottom: 1px solid #c8bda5;
        }
        .grid-row:last-child {
            border-bottom: none;
        }
        .grid-row.cols-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .grid-cell {
            padding: 8px 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .grid-cell + .grid-cell {
            border-left: 1px solid #c8bda5;
        }
        .cell-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #665b45;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .cell-value {
            font-family: 'EB Garamond', serif;
            font-size: 15.5px;
            font-weight: 500;
            color: #111111;
            min-height: 20px;
        }
        .cell-value.highlight {
            font-family: 'Montserrat', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            color: #c0392b;
        }
        .cell-value.child-name {
            font-family: 'Cinzel', serif;
            font-size: 19px;
            font-weight: 700;
            color: var(--zam-green);
            padding: 2px 0;
        }
        .grid-section-header {
            background: rgba(25, 138, 0, 0.07);
            border-bottom: 1px solid #c8bda5;
            border-top: 1px solid #c8bda5;
            font-family: 'Montserrat', sans-serif;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 5px 14px;
            color: var(--zam-green);
            text-transform: uppercase;
        }
        
        /* Certificate Footer / Signature Section */
        .cert-footer {
            display: grid;
            grid-template-columns: 1.1fr 1fr 1.3fr;
            gap: 15px;
            margin-top: 1.5rem;
            align-items: end;
            position: relative;
            z-index: 1;
        }
        
        /* QR Code Styling */
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .qr-box {
            background: white;
            padding: 6px;
            border: 1px solid #c8bda5;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: inline-block;
        }
        .qr-box img {
            width: 80px;
            height: 80px;
            display: block;
        }
        .qr-caption {
            font-family: 'Montserrat', sans-serif;
            font-size: 7.5px;
            font-weight: 500;
            color: #7f8c8d;
            margin-top: 5px;
            line-height: 1.3;
        }
        .qr-caption a {
            color: var(--zam-green);
            text-decoration: none;
            font-weight: 600;
        }

        /* SVG State Seal */
        .seal-section {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .official-seal {
            width: 95px;
            height: 95px;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,0.08));
        }

        /* Signature Section & Stamp */
        .signature-section {
            text-align: right;
            position: relative;
        }
        .certified-note {
            font-family: 'EB Garamond', serif;
            font-style: italic;
            font-size: 11px;
            color: #555;
            margin-bottom: 0.8rem;
        }
        .sig-wrap {
            position: relative;
            height: 45px;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
        }
        .signature-text {
            font-family: 'Great Vibes', cursive;
            font-size: 2.2rem;
            color: #1a2a3a;
            margin: 0;
            line-height: 1;
            transform: rotate(-3deg);
            z-index: 2;
        }
        .sig-line {
            border-bottom: 1.5px dashed #2c3e50;
            width: 200px;
            margin: 4px 0;
            display: inline-block;
        }
        .sig-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 9px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 0.5px;
        }
        .sig-date {
            font-family: 'Montserrat', sans-serif;
            font-size: 8px;
            color: #7f8c8d;
            margin-top: 2px;
        }
        
        /* Circular Approved Stamp */
        .registrar-stamp {
            position: absolute;
            width: 75px;
            height: 75px;
            border: 3px double rgba(0, 100, 255, 0.4);
            border-radius: 50%;
            color: rgba(0, 100, 255, 0.65);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 8px;
            font-weight: 700;
            transform: rotate(-12deg);
            bottom: 5px;
            left: 20px;
            pointer-events: none;
            letter-spacing: 0.5px;
            background: rgba(250, 248, 245, 0.65);
            z-index: 1;
            box-shadow: inset 0 0 4px rgba(0, 100, 255, 0.05);
        }
        .registrar-stamp span:first-child {
            border-bottom: 1px solid rgba(0, 100, 255, 0.3);
            padding-bottom: 1px;
            margin-bottom: 2px;
            font-size: 8px;
        }
        .registrar-stamp .stamp-date {
            font-size: 7.5px;
        }

        /* Zambian Flag Accent Ribbon */
        .flag-ribbon {
            height: 6px;
            width: calc(100% + 7rem);
            margin-left: -3.5rem;
            margin-bottom: -3.5rem;
            margin-top: 2rem;
            background: linear-gradient(to right, 
                var(--zam-green) 0%, 
                var(--zam-green) 65%, 
                var(--zam-red) 65%, 
                var(--zam-red) 76.6%, 
                var(--zam-black) 76.6%, 
                var(--zam-black) 88.3%, 
                var(--zam-copper) 88.3%, 
                var(--zam-copper) 100%
            );
            position: relative;
            z-index: 1;
        }
        
        /* Actions Styling */
        .actions-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 0 auto 2rem auto;
            max-width: 820px;
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
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
                margin: 10mm 15mm;
            }
            .actions-container { display: none; }
            body { background: white; padding: 0; margin: 0; }
            .certificate-container {
                width: 100%;
            }
            .certificate {
                box-shadow: none;
                border: 8px solid var(--zam-green);
                width: 100%;
                box-sizing: border-box;
                margin: 0;
                padding: 3rem;
                page-break-inside: avoid;
                background: #FAF8F5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .certificate::before {
                border: 2px solid var(--zam-copper) !important;
            }
            .flag-ribbon {
                width: calc(100% + 6rem) !important;
                margin-left: -3rem !important;
                margin-bottom: -3rem !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .grid-section-header {
                background: rgba(25, 138, 0, 0.07) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .registrar-stamp {
                background: rgba(250, 248, 245, 0.8) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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

    <div class="certificate-container">
        <div class="certificate">
            <!-- Corner Security Ornaments -->
            <div class="corner-ornament top-left"></div>
            <div class="corner-ornament top-right"></div>
            <div class="corner-ornament bottom-left"></div>
            <div class="corner-ornament bottom-right"></div>

            <!-- Centered Zambian Coat of Arms watermark -->
            <div class="watermark">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Coat_of_arms_of_Zambia.svg/450px-Coat_of_arms_of_Zambia.svg.png" alt="Zambia Coat of Arms">
            </div>
            
            <!-- Government Header -->
            <div class="gov-header">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Coat_of_arms_of_Zambia.svg/450px-Coat_of_arms_of_Zambia.svg.png" alt="Zambia Coat of Arms" class="cert-logo">
                <div class="country-title">REPUBLIC OF ZAMBIA</div>
                <div class="ministry-title">MINISTRY OF HOME AFFAIRS AND INTERNAL SECURITY</div>
                <div class="dept-title">DEPARTMENT OF NATIONAL REGISTRATION, PASSPORT AND CITIZENSHIP</div>
                <h1 class="main-cert-title"><?php echo strtoupper($title); ?></h1>
                <p class="act-title">ISSUED UNDER THE BIRTHS AND DEATHS REGISTRATION ACT (CAP. 51 OF THE LAWS OF ZAMBIA)</p>
            </div>

            <!-- Birth Certificate Details -->
            <?php if($type === 'birth'): ?>
                <div class="registry-grid">
                    <!-- Registration District & Cert No -->
                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Registration District</span>
                            <span class="cell-value">LUSAKA DISTRICT</span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">Certificate Code / Number</span>
                            <span class="cell-value highlight"><?php echo htmlspecialchars($data['certificate_number']); ?></span>
                        </div>
                    </div>
                    
                    <div class="grid-section-header">Subject Information (Child)</div>
                    
                    <!-- Child's Full Name -->
                    <div class="grid-row">
                        <div class="grid-cell">
                            <span class="cell-label">Full Name of Child</span>
                            <span class="cell-value child-name"><?php echo htmlspecialchars(strtoupper($data['child_fullname'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- Date of Birth & Gender -->
                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Date of Birth</span>
                            <span class="cell-value"><?php echo date('jS F, Y', strtotime($data['date_of_birth'])); ?></span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">Sex / Gender</span>
                            <span class="cell-value"><?php echo strtoupper($data['gender']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Place of Birth -->
                    <div class="grid-row">
                        <div class="grid-cell">
                            <span class="cell-label">Place of Birth (Hospital, Clinic, or Village)</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['place_of_birth']); ?></span>
                        </div>
                    </div>
                    
                    <div class="grid-section-header">Parental Information</div>
                    
                    <!-- Father's Name -->
                    <div class="grid-row">
                        <div class="grid-cell">
                            <span class="cell-label">Full Name of Father</span>
                            <span class="cell-value"><?php echo !empty($data['father_fullname']) ? htmlspecialchars(strtoupper($data['father_fullname'])) : 'NOT RECORDED'; ?></span>
                        </div>
                    </div>
                    
                    <!-- Mother's Name -->
                    <div class="grid-row">
                        <div class="grid-cell">
                            <span class="cell-label">Full Name of Mother (including maiden name if applicable)</span>
                            <span class="cell-value"><?php echo htmlspecialchars(strtoupper($data['mother_fullname'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="grid-section-header">Registrant & Informant Details</div>
                    
                    <!-- Informant Details -->
                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Name of Informant (Declarant)</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_name']); ?></span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">National Registration Card (NRC) No / ID</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_nid']); ?></span>
                        </div>
                    </div>

                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Relationship to Subject</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_relation']); ?></span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">Contact Reference</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_contact']); ?></span>
                        </div>
                    </div>
                </div>

            <!-- Death Certificate Details -->
            <?php else: ?>
                <div class="registry-grid">
                    <!-- Registration District & Cert No -->
                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Registration District</span>
                            <span class="cell-value">LUSAKA DISTRICT</span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">Certificate Code / Number</span>
                            <span class="cell-value highlight"><?php echo htmlspecialchars($data['certificate_number']); ?></span>
                        </div>
                    </div>
                    
                    <div class="grid-section-header">Deceased Subject Information</div>
                    
                    <!-- Deceased's Full Name -->
                    <div class="grid-row">
                        <div class="grid-cell">
                            <span class="cell-label">Full Name of Deceased</span>
                            <span class="cell-value child-name"><?php echo htmlspecialchars(strtoupper($data['deceased_fullname'])); ?></span>
                        </div>
                    </div>
                    
                    <!-- Date of Death & Gender/Age -->
                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Date of Death</span>
                            <span class="cell-value"><?php echo date('jS F, Y', strtotime($data['date_of_death'])); ?></span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">Gender / Age at Death</span>
                            <span class="cell-value"><?php echo strtoupper($data['gender']); ?> / <?php echo htmlspecialchars($data['age_at_death']); ?> Years</span>
                        </div>
                    </div>
                    
                    <!-- Place of Death -->
                    <div class="grid-row">
                        <div class="grid-cell">
                            <span class="cell-label">Place of Death (Hospital, Facility, or Location)</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['place_of_death']); ?></span>
                        </div>
                    </div>

                    <!-- Cause of Death -->
                    <div class="grid-row">
                        <div class="grid-cell">
                            <span class="cell-label">Certified Cause of Death</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['cause_of_death']); ?></span>
                        </div>
                    </div>
                    
                    <div class="grid-section-header">Registrant & Informant Details</div>
                    
                    <!-- Informant Details -->
                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Name of Informant (Declarant)</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_name']); ?></span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">National Registration Card (NRC) No / ID</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_nid']); ?></span>
                        </div>
                    </div>

                    <div class="grid-row cols-2">
                        <div class="grid-cell">
                            <span class="cell-label">Relationship to Deceased</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_relation']); ?></span>
                        </div>
                        <div class="grid-cell">
                            <span class="cell-label">Contact Reference</span>
                            <span class="cell-value"><?php echo htmlspecialchars($data['third_party_contact']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Certificate Footer (QR Code, CSS Seal, Signature) -->
            <div class="cert-footer">
                <!-- Dynamic Security QR Code -->
                <div class="qr-section">
                    <div class="qr-box">
                        <img src="<?php echo $qr_api_url; ?>" alt="Verification QR Code">
                    </div>
                    <div class="qr-caption">
                        Scan to verify authenticity online or visit<br>
                        <a href="<?php echo htmlspecialchars($verify_url); ?>" target="_blank">Civil Registry Verification Portal</a>
                    </div>
                </div>

                <!-- CSS SVG State Seal -->
                <div class="seal-section">
                    <div class="official-seal">
                        <svg viewBox="0 0 100 100" width="100%" height="100%">
                            <circle cx="50" cy="50" r="46" fill="none" stroke="#198A00" stroke-width="2"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#EF7D1A" stroke-width="1" stroke-dasharray="2 2"/>
                            <path id="seal-text-path" fill="none" d="M 12 50 A 38 38 0 1 1 88 50 A 38 38 0 1 1 12 50"/>
                            <text fill="#198A00" font-size="6.2" font-family="'Montserrat', sans-serif" font-weight="bold">
                                <textPath href="#seal-text-path" startOffset="0%">REPUBLIC OF ZAMBIA • CIVIL REGISTRATION OFFICE • </textPath>
                            </text>
                            <circle cx="50" cy="50" r="23" fill="#FAF8F5" stroke="#198A00" stroke-width="1.5"/>
                            <!-- Embedded Coat of Arms -->
                            <image href="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Coat_of_arms_of_Zambia.svg/450px-Coat_of_arms_of_Zambia.svg.png" x="37" y="37" height="26" width="26"/>
                        </svg>
                    </div>
                </div>

                <!-- Signature Block with Registrar Stamp -->
                <div class="signature-section">
                    <p class="certified-note">Certified True Extract from the Register</p>
                    <div class="sig-wrap">
                        <span class="signature-text">Natasha Banda</span>
                        <!-- Rotated Registrar Stamp -->
                        <div class="registrar-stamp">
                            <span>APPROVED</span>
                            <span>CIVIL REGISTRY</span>
                            <span class="stamp-date"><?php echo date('d-m-Y'); ?></span>
                        </div>
                    </div>
                    <div class="sig-line"></div>
                    <p class="sig-title">REGISTRAR GENERAL</p>
                    <p class="sig-date">Issued on: <?php echo date('d M Y'); ?></p>
                </div>
            </div>

            <!-- Vibrant Zambian Flag Ribbon Accent -->
            <div class="flag-ribbon"></div>
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
            margin:       10,
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
