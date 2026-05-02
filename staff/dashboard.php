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
        .container { padding: 2rem 5%; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; background: #white; box-shadow: var(--shadow); border-radius: 8px; overflow: hidden; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: var(--primary-color); color: white; }
        .status { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; text-transform: capitalize; }
        .status-pending { background: #ffeaa7; color: #d35400; }
        .status-approved { background: #55efc4; color: #00b894; }
        .status-rejected { background: #ff7675; color: #d63031; }
        .actions-bar { display: flex; gap: 1rem; margin-bottom: 2rem; }
    </style>
</head>
<body>
    <nav>
        <a href="../index.php" class="logo"><?php echo SITE_NAME; ?></a>
        <ul>
            <li><a href="../index.php">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['fullname']; ?> (Hospital Staff)</h1>
        <p>Manage birth and death certificates registered by you.</p>

        <div class="actions-bar" style="margin-top: 2rem;">
            <a href="apply_birth.php" class="btn"><i class="fas fa-plus"></i> New Birth Registration</a>
            <a href="apply_death.php" class="btn btn-secondary"><i class="fas fa-plus"></i> New Death Registration</a>
        </div>

        <section>
            <h2>Birth Registrations</h2>
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
                                        <a href="view_certificate.php?type=birth&id=<?php echo $row['id']; ?>" class="status status-approved" style="text-decoration:none;"><i class="fas fa-eye"></i> View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No applications found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section style="margin-top: 3rem;">
            <h2>Death Registrations</h2>
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
                                        <a href="view_certificate.php?type=death&id=<?php echo $row['id']; ?>" class="status status-approved" style="text-decoration:none;"><i class="fas fa-eye"></i> View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">No applications found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved. | Designer by Natasha Banda</p>
    </footer>
</body>
</html>
