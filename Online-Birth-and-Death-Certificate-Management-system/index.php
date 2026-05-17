<?php include 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Secure & Efficient Civil Registration Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body> 
 
    <nav>
        <a href="index.php" class="logo"><i class="fas fa-file-invoice"></i> <?php echo SITE_NAME; ?></a>
        <ul>
            <li><a href="index.php">Home</a></li>
            <?php if(isLoggedIn()): ?>
                <li><a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'staff/dashboard.php'; ?>">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="verify.php">Verify</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="login.php">Staff Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <section class="hero">
        <h1>Welcome to <?php echo SITE_NAME; ?></h1>
        <p>A secure digital portal for official birth and death certificate verification.</p>
        <div style="margin-top: 2rem;">
            <?php if(!isLoggedIn()): ?>
                <a href="verify.php" class="btn">Verify Certificate</a>
                <a href="search.php" class="btn" style="background-color: transparent; border: 2px solid white;">Search Records</a>
            <?php else: ?>
                <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'staff/dashboard.php'; ?>" class="btn">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <div class="dashboard-grid">
        <div class="card">
            <i class="fas fa-check-circle"></i>
            <h3>Verification</h3>
            <p>Instantly verify the authenticity of birth and death certificates using the unique code.</p>
        </div>
        <div class="card">
            <i class="fas fa-search"></i>
            <h3>Record Search</h3>
            <p>Retrieve certificate records using the registering third-party's National ID.</p>
        </div>
        <div class="card">
            <i class="fas fa-hospital-user"></i>
            <h3>Hospital Staff</h3>
            <p>Authorized personnel can securely register new birth and death events.</p>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved. | Disigned by Natash Banda</p>
    </footer>

</body>
</html>
