<?php
include '../includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

$birth_conditions = ["b.registered_by=$user_id"];
$death_conditions = ["d.registered_by=$user_id"];

if ($status !== 'all') {
    $birth_conditions[] = "b.status='$status'";
    $death_conditions[] = "d.status='$status'";
}
if (!empty($from_date)) {
    $birth_conditions[] = "b.created_at >= '$from_date 00:00:00'";
    $death_conditions[] = "d.created_at >= '$from_date 00:00:00'";
}
if (!empty($to_date)) {
    $birth_conditions[] = "b.created_at <= '$to_date 23:59:59'";
    $death_conditions[] = "d.created_at <= '$to_date 23:59:59'";
}

$birth_sql = "SELECT b.id, 'Birth' as cert_type, b.certificate_number, b.child_fullname as subject_name, b.status, b.fee_amount, b.payment_status, b.created_at 
              FROM birth_applications b";
if (!empty($birth_conditions)) {
    $birth_sql .= " WHERE " . implode(" AND ", $birth_conditions);
}

$death_sql = "SELECT d.id, 'Death' as cert_type, d.certificate_number, d.deceased_fullname as subject_name, d.status, d.fee_amount, d.payment_status, d.created_at 
              FROM death_applications d";
if (!empty($death_conditions)) {
    $death_sql .= " WHERE " . implode(" AND ", $death_conditions);
}

if ($type === 'birth') {
    $final_sql = $birth_sql . " ORDER BY b.created_at DESC";
} elseif ($type === 'death') {
    $final_sql = $death_sql . " ORDER BY d.created_at DESC";
} else {
    $final_sql = "($birth_sql) UNION ($death_sql) ORDER BY created_at DESC";
}

$report_records = $conn->query($final_sql);

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Staff_Registration_Report_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['Certificate Number', 'Type', 'Subject Name', 'Status', 'Fee (ZMW)', 'Payment Status', 'Date Registered']);
    
    while ($row = $report_records->fetch_assoc()) {
        fputcsv($output, [
            $row['certificate_number'],
            $row['cert_type'],
            $row['subject_name'],
            ucfirst($row['status']),
            $row['fee_amount'],
            ucfirst($row['payment_status']),
            date('Y-m-d H:i:s', strtotime($row['created_at']))
        ]);
    }
    fclose($output);
    exit;
}

// Compute Statistics on the Matched Report Set
$total_matched = 0;
$total_approved = 0;
$total_pending = 0;
$total_revenue = 0.00;

$display_rows = [];
while ($row = $report_records->fetch_assoc()) {
    $display_rows[] = $row;
    $total_matched++;
    if ($row['status'] === 'approved') $total_approved++;
    if ($row['status'] === 'pending') $total_pending++;
    if ($row['payment_status'] === 'paid') $total_revenue += (float)$row['fee_amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Work Reports - <?php echo SITE_NAME; ?></title>
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
            display: flex;
            flex-direction: column;
        }
        .content-body {
            flex-grow: 1;
        }
        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
            gap: 1rem;
            align-items: end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        .filter-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #2c3e50;
        }
        .filter-group select, .filter-group input {
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .mini-stat {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            border-left: 4px solid #3498db;
        }
        .mini-stat.success { border-left-color: #2ecc71; }
        .mini-stat.warning { border-left-color: #f1c40f; }
        .mini-stat.info { border-left-color: #9b59b6; }
        .mini-stat p { font-size: 0.85rem; color: #7f8c8d; text-transform: uppercase; margin: 0; }
        .mini-stat h3 { font-size: 1.5rem; color: #2c3e50; margin: 0.2rem 0 0 0; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        th, td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }
        th {
            background: var(--primary-color);
            color: white;
        }
        .status-badge {
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-rejected { background: #f8d7da; color: #721c24; }
        
        .action-links {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            color: white;
        }
        .btn-csv { background: #27ae60; }
        .btn-csv:hover { background: #219653; }
        .btn-print { background: #34495e; }
        .btn-print:hover { background: #2c3e50; }
        
        @media print {
            .sidebar { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; background: white !important; }
            .filter-card, .action-links, footer { display: none !important; }
            .mini-stat { box-shadow: none; border: 1px solid #ccc; }
            table { box-shadow: none; border: 1px solid #ccc; }
            th { background: #ddd !important; color: black !important; }
            h1 { text-align: center; margin-bottom: 1.5rem; }
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
                <li><a href="apply_death.php"><i class="fas fa-book-dead"></i> Register Death</a></li>
                <li><a href="reports.php" class="active"><i class="fas fa-file-invoice"></i> Reports</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-body">
                <h1 style="color: #2c3e50; margin-bottom: 0.5rem;"><i class="fas fa-file-contract"></i> My Registry Reports</h1>
                <p style="color: #7f8c8d; margin-bottom: 2rem;" class="no-print">Analyze, print, and export certificates registered under your account.</p>
                
                <!-- Filter Section -->
                <div class="filter-card">
                    <form method="GET" class="filter-form">
                        <div class="filter-group">
                            <label>Certificate Type</label>
                            <select name="type">
                                <option value="all" <?php echo $type==='all'?'selected':''; ?>>All Types</option>
                                <option value="birth" <?php echo $type==='birth'?'selected':''; ?>>Births</option>
                                <option value="death" <?php echo $type==='death'?'selected':''; ?>>Deaths</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="all" <?php echo $status==='all'?'selected':''; ?>>All Statuses</option>
                                <option value="approved" <?php echo $status==='approved'?'selected':''; ?>>Approved</option>
                                <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
                                <option value="rejected" <?php echo $status==='rejected'?'selected':''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>From Date</label>
                            <input type="date" name="from_date" value="<?php echo $from_date; ?>">
                        </div>
                        <div class="filter-group">
                            <label>To Date</label>
                            <input type="date" name="to_date" value="<?php echo $to_date; ?>">
                        </div>
                        <button type="submit" class="btn" style="width: auto; padding: 0.6rem 1.5rem; height: 38px; display: inline-flex; align-items: center; gap: 0.3rem;"><i class="fas fa-filter"></i> Filter</button>
                    </form>
                </div>

                <!-- Report Summary Stats -->
                <div class="stats-summary">
                    <div class="mini-stat">
                        <p>Matched Records</p>
                        <h3><?php echo $total_matched; ?></h3>
                    </div>
                    <div class="mini-stat success">
                        <p>Approved Certificates</p>
                        <h3><?php echo $total_approved; ?></h3>
                    </div>
                    <div class="mini-stat warning">
                        <p>Pending Approvals</p>
                        <h3><?php echo $total_pending; ?></h3>
                    </div>
                    <div class="mini-stat info">
                        <p>Fees Collected</p>
                        <h3>K<?php echo number_format($total_revenue, 2); ?></h3>
                    </div>
                </div>

                <!-- Export & Print Actions -->
                <div class="action-links">
                    <a href="reports.php?type=<?php echo $type; ?>&status=<?php echo $status; ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&export=csv" class="btn-action btn-csv"><i class="fas fa-file-excel"></i> Export CSV</a>
                    <button onclick="window.print()" class="btn-action btn-print"><i class="fas fa-print"></i> Print Report</button>
                </div>

                <!-- Report Records Table -->
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Cert Code</th>
                                <th>Type</th>
                                <th>Subject Name</th>
                                <th>Status</th>
                                <th>Fee</th>
                                <th>Payment</th>
                                <th>Date Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($display_rows) > 0): ?>
                                <?php foreach ($display_rows as $row): ?>
                                    <tr>
                                        <td><strong><?php echo $row['certificate_number'] ?: 'N/A'; ?></strong></td>
                                        <td><?php echo $row['cert_type']; ?></td>
                                        <td><?php echo $row['subject_name']; ?></td>
                                        <td><span class="status-badge badge-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                                        <td>K<?php echo number_format($row['fee_amount'], 2); ?></td>
                                        <td><?php echo ucfirst($row['payment_status']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No registry records match the selected filters.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
            
            <footer style="margin-left: -3rem; margin-right: -3rem; margin-bottom: -2rem;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. My Reports | Designed by Natasha Banda</p>
            </footer>
        </main>
    </div>

</body>
</html>
