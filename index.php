<?php
// Start session
session_start();

// Include configuration files
require_once 'config/main.php';
require_once 'config/auth.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['user_role'] : null;
$username = $isLoggedIn ? $_SESSION['username'] : null;

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Get page content based on user status
$pageTitle = "Home";
$content = "";

if ($isLoggedIn) {
    if ($userRole === 'admin') {
        $content = getAdminDashboard();
    } else {
        $content = getUserDashboard();
    }
} else {
    $content = getHomePage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - My Website</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-home me-2"></i>My Website
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>

                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=profile">
                                <i class="fas fa-user me-1"></i>Profile
                            </a>
                        </li>

                        <?php if ($userRole === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Admin Dashboard
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=settings">
                                <i class="fas fa-cog me-1"></i>Settings
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($username); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="index.php?action=profile">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="index.php?action=settings">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?action=logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mt-4">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>My Website</h5>
                    <p>A modern web application built with PHP and Bootstrap.</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">Home</a></li>
                        <li><a href="login.php" class="text-light">Login</a></li>
                        <li><a href="register.php" class="text-light">Register</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Follow Us</h6>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="row">
                <div class="col-12 text-center">
                    <p>&copy; <?php echo date('Y'); ?> My Website. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <!-- Page-specific scripts -->
    <?php if ($isLoggedIn): ?>
    <script>
        // Auto-refresh user session
        setInterval(function() {
            fetch('api/session-check.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => console.log('Session check failed:', error));
        }, 60000); // Check every minute
    </script>
    <?php endif; ?>
</body>
</html>

<?php
// Helper functions
function getHomePage() {
    return '
        <div class="row">
            <div class="col-12">
                <div class="jumbotron bg-light p-5 rounded">
                    <h1 class="display-4">Welcome to My Website</h1>
                    <p class="lead">A modern web application built with PHP, featuring user authentication, admin panel, and more.</p>
                    <hr class="my-4">
                    <p>Get started by creating an account or logging in to access all features.</p>
                    <a class="btn btn-primary btn-lg me-2" href="register.php" role="button">
                        <i class="fas fa-user-plus me-2"></i>Register Now
                    </a>
                    <a class="btn btn-outline-primary btn-lg" href="login.php" role="button">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Secure</h5>
                        <p class="card-text">Built with security best practices and modern authentication systems.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-tachometer-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Admin Panel</h5>
                        <p class="card-text">Complete admin dashboard for managing users and content.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-mobile-alt fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Responsive</h5>
                        <p class="card-text">Fully responsive design that works on all devices.</p>
                    </div>
                </div>
            </div>
        </div>';
}

function getUserDashboard() {
    return '
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">Welcome back!</h4>
                    <p>You are successfully logged in. Here\'s your personal dashboard.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Activity Overview</h5>
                    </div>
                    <div class="card-body">
                        <p>Your recent activity and statistics will be displayed here.</p>
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="text-primary">0</h3>
                                <p class="text-muted">Posts</p>
                            </div>
                            <div class="col-4">
                                <h3 class="text-success">0</h3>
                                <p class="text-muted">Comments</p>
                            </div>
                            <div class="col-4">
                                <h3 class="text-info">0</h3>
                                <p class="text-muted">Likes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-cog me-2"></i>Account</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php?action=profile" class="btn btn-outline-primary">
                                <i class="fas fa-user me-2"></i>View Profile
                            </a>
                            <a href="index.php?action=settings" class="btn btn-outline-secondary">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
}

function getAdminDashboard() {
    return '
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h4>
                    <p>Welcome to the admin panel. Manage your website from here.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-3"></i>
                        <h5 class="card-title">Users</h5>
                        <p class="card-text">Manage user accounts</p>
                        <a href="admin/users.php" class="btn btn-primary">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-file-alt fa-2x text-success mb-3"></i>
                        <h5 class="card-title">Content</h5>
                        <p class="card-text">Manage site content</p>
                        <a href="admin/content.php" class="btn btn-success">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-2x text-warning mb-3"></i>
                        <h5 class="card-title">Analytics</h5>
                        <p class="card-text">View site statistics</p>
                        <a href="admin/analytics.php" class="btn btn-warning">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-cog fa-2x text-info mb-3"></i>
                        <h5 class="card-title">Settings</h5>
                        <p class="card-text">System configuration</p>
                        <a href="admin/settings.php" class="btn btn-info">Configure</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                New user registration
                                <span class="badge bg-primary">2 min ago</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                Content updated
                                <span class="badge bg-success">1 hour ago</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                System backup completed
                                <span class="badge bg-info">3 hours ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-server me-2"></i>System Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Database</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>File System</span>
                                <span class="badge bg-success">OK</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Memory Usage</span>
                                <span class="badge bg-warning">65%</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Disk Space</span>
                                <span class="badge bg-success">42%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
}
?>
