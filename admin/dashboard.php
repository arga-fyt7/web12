<?php
// Start session
session_start();

// Include configuration
require_once '../config/main.php';
require_once '../config/auth.php';

// Require admin access
requireAdmin();

// Get dashboard statistics
try {
    $pdo = getDBConnection();

    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch()['total'];

    // Active users
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM users WHERE status = 'active'");
    $activeUsers = $stmt->fetch()['active'];

    // New users this month
    $stmt = $pdo->query("SELECT COUNT(*) as new FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $newUsers = $stmt->fetch()['new'];

    // Total activities this week
    $stmt = $pdo->query("SELECT COUNT(*) as activities FROM activity_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $totalActivities = $stmt->fetch()['activities'];

    // Recent activities
    $stmt = $pdo->query("SELECT a.*, u.username FROM activity_log a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 10");
    $recentActivities = $stmt->fetchAll();

    // System info
    $systemInfo = [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'database_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
        'disk_usage' => function_exists('disk_free_space') ? round(disk_free_space('/') / 1024 / 1024 / 1024, 2) . ' GB free' : 'Unknown'
    ];

} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalUsers = $activeUsers = $newUsers = $totalActivities = 0;
    $recentActivities = [];
    $systemInfo = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin: 0.25rem 0;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
        }

        .main-content {
            background: #f8fafc;
            min-height: 100vh;
        }

        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
        }

        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-left: 4px solid;
        }

        .stat-card.primary { border-left-color: #3b82f6; }
        .stat-card.success { border-left-color: #10b981; }
        .stat-card.warning { border-left-color: #f59e0b; }
        .stat-card.info { border-left-color: #06b6d4; }

        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .activity-icon.login { background: #dbeafe; color: #3b82f6; }
        .activity-icon.register { background: #d1fae5; color: #10b981; }
        .activity-icon.update { background: #fef3c7; color: #f59e0b; }
        .activity-icon.logout { background: #fee2e2; color: #ef4444; }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .navbar-brand {
            font-weight: 600;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h5 class="mb-4">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Admin Panel
                    </h5>

                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a class="nav-link" href="content.php">
                            <i class="fas fa-file-alt"></i> Content
                        </a>
                        <a class="nav-link" href="analytics.php">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-external-link-alt"></i> View Site
                        </a>
                        <a class="nav-link" href="../index.php?action=logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                            <i class="fas fa-bars"></i>
                        </button>

                        <div class="navbar-brand d-none d-md-block">
                            Dashboard Overview
                        </div>

                        <div class="ms-auto d-flex align-items-center">
                            <div class="user-info me-3">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
                                <span class="badge bg-primary ms-2">Admin</span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="settings.php">
                                        <i class="fas fa-wrench me-2"></i>Settings
                                    </a></li>
                                    <li><a class="dropdown-item" href="../index.php?action=logout">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <div class="container-fluid p-4">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card primary dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Total Users</h6>
                                            <h3 class="mb-0"><?php echo number_format($totalUsers); ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card stat-card success dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Active Users</h6>
                                            <h3 class="mb-0"><?php echo number_format($activeUsers); ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-check fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card stat-card warning dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">New This Month</h6>
                                            <h3 class="mb-0"><?php echo number_format($newUsers); ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-user-plus fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card stat-card info dashboard-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title text-muted">Activities (7d)</h6>
                                            <h3 class="mb-0"><?php echo number_format($totalActivities); ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-chart-line fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- User Growth Chart -->
                        <div class="col-md-8 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-area me-2"></i>
                                        User Growth
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="userGrowthChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-server me-2"></i>
                                        System Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>PHP Version</span>
                                            <span class="badge bg-success"><?php echo $systemInfo['php_version'] ?? 'Unknown'; ?></span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Server</span>
                                            <span class="badge bg-success"><?php echo $systemInfo['server_software'] ?? 'Unknown'; ?></span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Database</span>
                                            <span class="badge bg-success">Connected</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Memory Usage</span>
                                            <span class="badge bg-warning"><?php echo $systemInfo['memory_usage'] ?? 'Unknown'; ?></span>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between">
                                            <span>Disk Space</span>
                                            <span class="badge bg-success"><?php echo $systemInfo['disk_usage'] ?? 'Unknown'; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Recent Activities -->
                        <div class="col-md-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        Recent Activities
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php if (!empty($recentActivities)): ?>
                                            <?php foreach ($recentActivities as $activity): ?>
                                                <div class="activity-item d-flex align-items-center px-3">
                                                    <div class="activity-icon <?php
                                                        echo match($activity['action']) {
                                                            'user_login' => 'login',
                                                            'user_registered' => 'register',
                                                            'profile_updated', 'password_changed' => 'update',
                                                            'user_logout' => 'logout',
                                                            default => 'update'
                                                        };
                                                    ?>">
                                                        <i class="fas fa-<?php
                                                            echo match($activity['action']) {
                                                                'user_login' => 'sign-in-alt',
                                                                'user_registered' => 'user-plus',
                                                                'profile_updated', 'password_changed' => 'edit',
                                                                'user_logout' => 'sign-out-alt',
                                                                default => 'info-circle'
                                                            };
                                                        ?>"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></strong>
                                                                <span class="text-muted ms-2"><?php echo htmlspecialchars($activity['action']); ?></span>
                                                            </div>
                                                            <small class="text-muted">
                                                                <?php echo date('M j, H:i', strtotime($activity['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                        <?php if (!empty($activity['description'])): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars($activity['description']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="p-3 text-center text-muted">
                                                No recent activities
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-md-6 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt me-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="users.php" class="btn btn-primary">
                                            <i class="fas fa-users me-2"></i>Manage Users
                                        </a>
                                        <a href="content.php" class="btn btn-success">
                                            <i class="fas fa-file-alt me-2"></i>Manage Content
                                        </a>
                                        <a href="analytics.php" class="btn btn-warning">
                                            <i class="fas fa-chart-bar me-2"></i>View Analytics
                                        </a>
                                        <a href="settings.php" class="btn btn-info">
                                            <i class="fas fa-cog me-2"></i>System Settings
                                        </a>
                                        <button class="btn btn-secondary" onclick="clearCache()">
                                            <i class="fas fa-broom me-2"></i>Clear Cache
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        // User Growth Chart
        const ctx = document.getElementById('userGrowthChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'New Users',
                        data: [12, 19, 15, 25, 22, 30],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Clear cache function
        function clearCache() {
            if (confirm('Are you sure you want to clear the system cache?')) {
                fetch('../api/clear-cache.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Cache cleared successfully!');
                    } else {
                        alert('Failed to clear cache: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to clear cache');
                });
            }
        }

        // Auto-refresh dashboard data
        setInterval(function() {
            fetch('../api/dashboard-stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update statistics
                        document.querySelector('.stat-card.primary h3').textContent = data.totalUsers;
                        document.querySelector('.stat-card.success h3').textContent = data.activeUsers;
                        document.querySelector('.stat-card.warning h3').textContent = data.newUsers;
                        document.querySelector('.stat-card.info h3').textContent = data.totalActivities;
                    }
                })
                .catch(error => console.log('Dashboard refresh failed:', error));
        }, 30000); // Refresh every 30 seconds
    </script>
</body>
</html>
