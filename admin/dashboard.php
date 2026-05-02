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

// Stats
$total_staff = $conn->query("SELECT count(*) as count FROM users WHERE role='hospital_staff'")->fetch_assoc()['count'];
$total_births = $conn->query("SELECT count(*) as count FROM birth_applications")->fetch_assoc()['count'];
$total_deaths = $conn->query("SELECT count(*) as count FROM death_applications")->fetch_assoc()['count'];
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
        .container { padding: 2rem 5%; }
        .stats-bar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: var(--shadow); text-align: center; }
        .stat-card h3 { font-size: 2rem; color: var(--secondary-color); }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow); margin-bottom: 2rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: var(--primary-color); color: white; }
        .action-btn { padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.8rem; text-decoration: none; font-weight: 600; margin-right: 0.5rem; }
        .btn-view { background: #3498db; color: white; }
        .btn-approve { background: #2ecc71; color: white; }
        .btn-reject { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <nav>
        <a href="../index.php" class="logo"><?php echo SITE_NAME; ?> (<?php echo isAdmin() ? 'Admin' : 'Registrar'; ?>)</a>
        <ul>
            <li><a href="../index.php">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Administrative Overview</h1>
        
        <div class="stats-bar">
            <div class="stat-card">
                <p>Total Staff</p>
                <h3><?php echo $total_staff; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Births</p>
                <h3><?php echo $total_births; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Deaths</p>
                <h3><?php echo $total_deaths; ?></h3>
            </div>
        </div>

        <section>
            <h2>Pending Birth Registrations</h2>
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
                                    <a href="view_birth.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view">Review</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No pending birth registrations.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section>
            <h2>Pending Death Registrations</h2>
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
                                    <a href="view_death.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view">Review</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No pending death registrations.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> Admin Panel. | Designer by Natasha Banda</p>
    </footer>
</body>
</html>
