<?php
/**
 * Admin Dashboard
 * Manage all user accounts across platforms
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDbConnection();
$success = '';
$error = '';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        if (deleteUser($user_id)) {
            $success = "User deleted successfully";
        } else {
            $error = "Failed to delete user";
        }
    } elseif (isset($_POST['delete_all_platform'])) {
        $platform = $conn->real_escape_string($_POST['platform']);
        $sql = "DELETE FROM users WHERE platform = '$platform'";
        if ($conn->query($sql)) {
            $success = "All $platform accounts deleted successfully";
        } else {
            $error = "Failed to delete accounts";
        }
    }
}

// Get statistics
$stats = [];
$platforms = ['twitter', 'facebook', 'instagram'];
foreach ($platforms as $platform) {
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE platform = '$platform'");
    $stats[$platform] = $result->fetch_assoc()['count'];
}
$stats['total'] = array_sum($stats);

// Get filter parameters
$filter_platform = isset($_GET['platform']) ? $_GET['platform'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = [];
if ($filter_platform !== 'all') {
    $where[] = "platform = '" . $conn->real_escape_string($filter_platform) . "'";
}
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $where[] = "(email LIKE '%$search_term%' OR username LIKE '%$search_term%' OR full_name LIKE '%$search_term%')";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
$users = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Social Login Training</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-dashboard">
        <nav class="admin-nav">
            <div class="nav-left">
                <h1>🔐 Admin Panel</h1>
                <span class="nav-subtitle">Social Login Training</span>
            </div>
            <div class="nav-right">
                <span>👤 <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                <a href="activity-log.php" class="btn btn-secondary btn-sm">Activity Log</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </nav>

        <div class="dashboard-content">
            <?php if ($success): ?>
                <div class="success-box">✓ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card twitter">
                    <div class="stat-icon">𝕏</div>
                    <div class="stat-value"><?= $stats['twitter'] ?></div>
                    <div class="stat-label">Twitter/X</div>
                </div>
                <div class="stat-card facebook">
                    <div class="stat-icon">f</div>
                    <div class="stat-value"><?= $stats['facebook'] ?></div>
                    <div class="stat-label">Facebook</div>
                </div>
                <div class="stat-card instagram">
                    <div class="stat-icon">📷</div>
                    <div class="stat-value"><?= $stats['instagram'] ?></div>
                    <div class="stat-label">Instagram</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label>Platform:</label>
                        <select name="platform" onchange="this.form.submit()">
                            <option value="all" <?= $filter_platform === 'all' ? 'selected' : '' ?>>All Platforms</option>
                            <option value="twitter" <?= $filter_platform === 'twitter' ? 'selected' : '' ?>>Twitter/X</option>
                            <option value="facebook" <?= $filter_platform === 'facebook' ? 'selected' : '' ?>>Facebook</option>
                            <option value="instagram" <?= $filter_platform === 'instagram' ? 'selected' : '' ?>>Instagram</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                               placeholder="Search by email, username, or name...">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    </div>

                    <?php if ($filter_platform !== 'all' || !empty($search)): ?>
                        <a href="dashboard.php" class="btn btn-secondary btn-sm">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Bulk Actions -->
            <?php if ($filter_platform !== 'all' && $stats[$filter_platform] > 0): ?>
                <div class="bulk-actions">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete ALL <?= strtoupper($filter_platform) ?> accounts? This cannot be undone!');">
                        <input type="hidden" name="platform" value="<?= htmlspecialchars($filter_platform) ?>">
                        <button type="submit" name="delete_all_platform" class="btn btn-danger">
                            Delete All <?= ucfirst($filter_platform) ?> Accounts (<?= $stats[$filter_platform] ?>)
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Platform</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <span class="platform-badge platform-<?= $user['platform'] ?>">
                                            <?= ucfirst($user['platform']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= $user['username'] ? '@' . htmlspecialchars($user['username']) : '-' ?></td>
                                    <td><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td><?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-xs">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-data">
                                    No users found. <?php if (!empty($search) || $filter_platform !== 'all'): ?>
                                        Try clearing filters.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
