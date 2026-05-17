<?php
include '../includes/config.php';

if (!isLoggedIn() || (!isAdmin() && !isRegistrar())) {
    header("Location: ../login.php");
    exit;
}

// Fetch all pending birth applications
$pending_births = $conn->query("SELECT b.*, u.fullname as staff_name FROM birth_applications b JOIN users u ON b.registered_by = u.id WHERE b.status='pending' ORDER BY b.created_at ASC");
// Fetch all pending death applications
$pending_deaths = $conn->query("SELECT d.*, u.fullname as staff_name FROM death_applications d JOIN users u ON d.registered_by = u.id WHERE d.status='pending' ORDER BY d.created_at ASC");

// Fetch all approved birth applications
$approved_births = $conn->query("SELECT b.*, u.fullname as staff_name FROM birth_applications b JOIN users u ON b.registered_by = u.id WHERE b.status='approved' ORDER BY b.created_at DESC");
// Fetch all approved death applications
$approved_deaths = $conn->query("SELECT d.*, u.fullname as staff_name FROM death_applications d JOIN users u ON d.registered_by = u.id WHERE d.status='approved' ORDER BY d.created_at DESC");

// Stats
$total_staff = $conn->query("SELECT count(*) as count FROM users WHERE role='hospital_staff'")->fetch_assoc()['count'];
$total_births = $conn->query("SELECT count(*) as count FROM birth_applications")->fetch_assoc()['count'];
$total_deaths = $conn->query("SELECT count(*) as count FROM death_applications")->fetch_assoc()['count'];

// Sub-Stats for dashboard statistics cards
$approved_births_count = $conn->query("SELECT count(*) as count FROM birth_applications WHERE status='approved'")->fetch_assoc()['count'];
$pending_births_count = $conn->query("SELECT count(*) as count FROM birth_applications WHERE status='pending'")->fetch_assoc()['count'];
$approved_deaths_count = $conn->query("SELECT count(*) as count FROM death_applications WHERE status='approved'")->fetch_assoc()['count'];
$pending_deaths_count = $conn->query("SELECT count(*) as count FROM death_applications WHERE status='pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
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
        .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.8rem;
            text-decoration: none;
            font-weight: 600;
            margin-right: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .btn-view {
            background: #3498db;
            color: white;
        }
        .btn-view:hover {
            background: #2980b9;
        }
        .btn-rereview {
            background: #7f8c8d;
            color: white;
        }
        .btn-rereview:hover {
            background: #95a5a6;
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
            <div class="content-body">
                <h1 style="color: #2c3e50; margin-bottom: 0.5rem;">Administrative Overview</h1>
                <p style="color: #7f8c8d; margin-bottom: 2rem;">System statistics and certificate status management.</p>
                
                <!-- Sleek Statistics Bar -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;"><i class="fas fa-users-cog"></i></div>
                        <div class="stat-info">
                            <h4>Total Staff</h4>
                            <p class="stat-val"><?php echo $total_staff; ?></p>
                            <span class="stat-sub">Active hospital workers</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(52, 152, 219, 0.1); color: #3498db;"><i class="fas fa-baby"></i></div>
                        <div class="stat-info">
                            <h4>Birth Certificates</h4>
                            <p class="stat-val"><?php echo $total_births; ?></p>
                            <span class="stat-sub"><?php echo $approved_births_count; ?> Approved | <?php echo $pending_births_count; ?> Pending</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;"><i class="fas fa-heart-broken"></i></div>
                        <div class="stat-info">
                            <h4>Death Certificates</h4>
                            <p class="stat-val"><?php echo $total_deaths; ?></p>
                            <span class="stat-sub"><?php echo $approved_deaths_count; ?> Approved | <?php echo $pending_deaths_count; ?> Pending</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Birth Registrations -->
                <section>
                    <h2><i class="fas fa-clock" style="color: #f39c12;"></i> Pending Birth Registrations</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Registered By</th>
                                <th>Child Name</th>
                                <th>DOB</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($pending_births->num_rows > 0): ?>
                                <?php while($row = $pending_births->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['staff_name']; ?></td>
                                        <td><?php echo $row['child_fullname']; ?></td>
                                        <td><?php echo $row['date_of_birth']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="view_birth.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view"><i class="fas fa-check-double"></i> Review</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align:center;">No pending birth registrations.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Pending Death Registrations -->
                <section>
                    <h2><i class="fas fa-clock" style="color: #f39c12;"></i> Pending Death Registrations</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Registered By</th>
                                <th>Deceased Name</th>
                                <th>DOD</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($pending_deaths->num_rows > 0): ?>
                                <?php while($row = $pending_deaths->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['staff_name']; ?></td>
                                        <td><?php echo $row['deceased_fullname']; ?></td>
                                        <td><?php echo $row['date_of_death']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="view_death.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view"><i class="fas fa-check-double"></i> Review</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align:center;">No pending death registrations.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Approved Birth Certificates -->
                <section>
                    <h2><i class="fas fa-check-circle" style="color: #2ecc71;"></i> Approved Birth Certificates</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Registered By</th>
                                <th>Child Name</th>
                                <th>DOB</th>
                                <th>Certificate #</th>
                                <th>Approved On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($approved_births->num_rows > 0): ?>
                                <?php while($row = $approved_births->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['staff_name']; ?></td>
                                        <td><?php echo $row['child_fullname']; ?></td>
                                        <td><?php echo $row['date_of_birth']; ?></td>
                                        <td><strong><?php echo $row['certificate_number']; ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($row['updated_at'] ?? $row['created_at'])); ?></td>
                                        <td>
                                            <a href="../staff/view_certificate.php?type=birth&id=<?php echo $row['id']; ?>" class="action-btn btn-view"><i class="fas fa-eye"></i> View</a>
                                            <a href="view_birth.php?id=<?php echo $row['id']; ?>" class="action-btn btn-rereview"><i class="fas fa-edit"></i> Re-Review</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No approved birth certificates.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Approved Death Certificates -->
                <section>
                    <h2><i class="fas fa-check-circle" style="color: #2ecc71;"></i> Approved Death Certificates</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Registered By</th>
                                <th>Deceased Name</th>
                                <th>DOD</th>
                                <th>Certificate #</th>
                                <th>Approved On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($approved_deaths->num_rows > 0): ?>
                                <?php while($row = $approved_deaths->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['staff_name']; ?></td>
                                        <td><?php echo $row['deceased_fullname']; ?></td>
                                        <td><?php echo $row['date_of_death']; ?></td>
                                        <td><strong><?php echo $row['certificate_number']; ?></strong></td>
                                        <td><?php echo date('M d, Y', strtotime($row['updated_at'] ?? $row['created_at'])); ?></td>
                                        <td>
                                            <a href="../staff/view_certificate.php?type=death&id=<?php echo $row['id']; ?>" class="action-btn btn-view"><i class="fas fa-eye"></i> View</a>
                                            <a href="view_death.php?id=<?php echo $row['id']; ?>" class="action-btn btn-rereview"><i class="fas fa-edit"></i> Re-Review</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No approved death certificates.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>

            <footer style="margin-left: -3rem; margin-right: -3rem; margin-bottom: -2rem;">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> Admin Panel. | Disigned by Natash Banda</p>
            </footer>
        </main>
    </div>

</body>
</html>
