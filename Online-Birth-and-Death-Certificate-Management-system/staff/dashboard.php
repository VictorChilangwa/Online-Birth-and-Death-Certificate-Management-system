<?php
include '../includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch birth applications
$births = $conn->query("SELECT * FROM birth_applications WHERE registered_by=$user_id ORDER BY created_at DESC");
// Fetch death applications
$deaths = $conn->query("SELECT * FROM death_applications WHERE registered_by=$user_id ORDER BY created_at DESC");

// Stats for active hospital staff
$total_my_births = $conn->query("SELECT count(*) as count FROM birth_applications WHERE registered_by=$user_id")->fetch_assoc()['count'];
$approved_my_births = $conn->query("SELECT count(*) as count FROM birth_applications WHERE registered_by=$user_id AND status='approved'")->fetch_assoc()['count'];
$pending_my_births = $conn->query("SELECT count(*) as count FROM birth_applications WHERE registered_by=$user_id AND status='pending'")->fetch_assoc()['count'];

$total_my_deaths = $conn->query("SELECT count(*) as count FROM death_applications WHERE registered_by=$user_id")->fetch_assoc()['count'];
$approved_my_deaths = $conn->query("SELECT count(*) as count FROM death_applications WHERE registered_by=$user_id AND status='approved'")->fetch_assoc()['count'];
$pending_my_deaths = $conn->query("SELECT count(*) as count FROM death_applications WHERE registered_by=$user_id AND status='pending'")->fetch_assoc()['count'];

$total_my_registrations = $total_my_births + $total_my_deaths;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - <?php echo SITE_NAME; ?></title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        .stat-info {
            flex-grow: 1;
        }
        .stat-info h4 {
            font-size: 0.95rem;
            color: #7f8c8d;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-val {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
        }
        .stat-sub {
            font-size: 0.8rem;
            color: #95a5a6;
            display: block;
            margin-top: 0.2rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: white;
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 3rem;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: var(--primary-color);
            color: white;
        }
        .status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
        }
        .status-pending { background: #ffeaa7; color: #d35400; }
        .status-approved { background: #55efc4; color: #00b894; }
        .status-rejected { background: #ff7675; color: #d63031; }
        .actions-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .btn-new-reg {
            max-width: 250px;
        }
        h2 {
            margin-bottom: 1rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="apply_birth.php"><i class="fas fa-baby"></i> Register Birth</a></li>
                <li><a href="apply_death.php"><i class="fas fa-book-dead"></i> Register Death</a></li>
                <li><a href="reports.php"><i class="fas fa-file-invoice"></i> Reports</a></li>
                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-body">
                <h1 style="color: #2c3e50; margin-bottom: 0.5rem;">Welcome, <?php echo $_SESSION['fullname']; ?></h1>
                <p style="color: #7f8c8d; margin-bottom: 2rem;">Manage birth and death certificates registered by you.</p>

                <!-- Sleek Statistics Bar -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;"><i class="fas fa-folder-open"></i></div>
                        <div class="stat-info">
                            <h4>My Total Work</h4>
                            <p class="stat-val"><?php echo $total_my_registrations; ?></p>
                            <span class="stat-sub">Total submissions</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;"><i class="fas fa-baby"></i></div>
                        <div class="stat-info">
                            <h4>My Birth Registrations</h4>
                            <p class="stat-val"><?php echo $total_my_births; ?></p>
                            <span class="stat-sub"><?php echo $approved_my_births; ?> Approved | <?php echo $pending_my_births; ?> Pending</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;"><i class="fas fa-heart-broken"></i></div>
                        <div class="stat-info">
                            <h4>My Death Registrations</h4>
                            <p class="stat-val"><?php echo $total_my_deaths; ?></p>
                            <span class="stat-sub"><?php echo $approved_my_deaths; ?> Approved | <?php echo $pending_my_deaths; ?> Pending</span>
                        </div>
                    </div>
                </div>

                <div class="actions-bar">
                    <a href="apply_birth.php" class="btn btn-new-reg"><i class="fas fa-plus"></i> New Birth Registration</a>
                    <a href="apply_death.php" class="btn btn-secondary btn-new-reg"><i class="fas fa-plus"></i> New Death Registration</a>
                </div>

                <!-- Birth Registrations Table -->
                <section>
                    <h2><i class="fas fa-baby" style="color: #3498db;"></i> Birth Registrations</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Child Name</th>
                                <th>DOB</th>
                                <th>Status</th>
                                <th>Certificate #</th>
                                <th>Date Applied</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($births->num_rows > 0): ?>
                                <?php while($row = $births->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['child_fullname']; ?></td>
                                        <td><?php echo $row['date_of_birth']; ?></td>
                                        <td><span class="status status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                                        <td><?php echo $row['certificate_number'] ?? 'N/A'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <?php if($row['status'] === 'approved'): ?>
                                                <a href="view_certificate.php?type=birth&id=<?php echo $row['id']; ?>" class="status status-approved" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem;"><i class="fas fa-eye"></i> View</a>
                                            <?php else: ?>
                                                <span style="color: #95a5a6; font-size: 0.85rem;">Pending Approval</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No birth applications found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Death Registrations Table -->
                <section style="margin-top: 3rem;">
                    <h2><i class="fas fa-heart-broken" style="color: #e74c3c;"></i> Death Registrations</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Deceased Name</th>
                                <th>Date of Death</th>
                                <th>Status</th>
                                <th>Certificate #</th>
                                <th>Date Applied</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($deaths->num_rows > 0): ?>
                                <?php while($row = $deaths->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['deceased_fullname']; ?></td>
                                        <td><?php echo $row['date_of_death']; ?></td>
                                        <td><span class="status status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                                        <td><?php echo $row['certificate_number'] ?? 'N/A'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <?php if($row['status'] === 'approved'): ?>
                                                <a href="view_certificate.php?type=death&id=<?php echo $row['id']; ?>" class="status status-approved" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem;"><i class="fas fa-eye"></i> View</a>
                                            <?php else: ?>
                                                <span style="color: #95a5a6; font-size: 0.85rem;">Pending Approval</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No death applications found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>

            <footer style="margin-left: -3rem; margin-right: -3rem; margin-bottom: -2rem;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved. | Disigned by Natash Banda</p>
            </footer>
        </main>
    </div>

</body>
</html>
