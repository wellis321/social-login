<?php
/**
 * Admin Activity Log
 * View all user activity across platforms
 */

session_start();
require_once __DIR__ . '/../../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDbConnection();

// Get filter parameters
$filter_platform = isset($_GET['platform']) ? $_GET['platform'] : 'all';
$filter_action = isset($_GET['action']) ? $_GET['action'] : 'all';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

// Build query
$where = [];
if ($filter_platform !== 'all') {
    $where[] = "platform = '" . $conn->real_escape_string($filter_platform) . "'";
}
if ($filter_action !== 'all') {
    $where[] = "action = '" . $conn->real_escape_string($filter_action) . "'";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT activity_log.*, users.email, users.username
        FROM activity_log
        LEFT JOIN users ON activity_log.user_id = users.id
        $where_clause
        ORDER BY activity_log.created_at DESC
        LIMIT $limit";
$activities = $conn->query($sql);

// Get action types
$actions = $conn->query("SELECT DISTINCT action FROM activity_log ORDER BY action");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-dashboard">
        <nav class="admin-nav">
            <div class="nav-left">
                <h1>📊 Activity Log</h1>
                <span class="nav-subtitle">User Activity Tracking</span>
            </div>
            <div class="nav-right">
                <a href="dashboard.php" class="btn btn-secondary btn-sm">← Back to Dashboard</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
            </div>
        </nav>

        <div class="dashboard-content">
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
                        <label>Action:</label>
                        <select name="action" onchange="this.form.submit()">
                            <option value="all" <?= $filter_action === 'all' ? 'selected' : '' ?>>All Actions</option>
                            <?php while ($action = $actions->fetch_assoc()): ?>
                                <option value="<?= $action['action'] ?>" <?= $filter_action === $action['action'] ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $action['action'])) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Limit:</label>
                        <select name="limit" onchange="this.form.submit()">
                            <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100</option>
                            <option value="200" <?= $limit === 200 ? 'selected' : '' ?>>200</option>
                            <option value="500" <?= $limit === 500 ? 'selected' : '' ?>>500</option>
                        </select>
                    </div>

                    <?php if ($filter_platform !== 'all' || $filter_action !== 'all' || $limit !== 50): ?>
                        <a href="activity-log.php" class="btn btn-secondary btn-sm">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Activity Table -->
            <div class="table-container">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Platform</th>
                            <th>Action</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($activities->num_rows > 0): ?>
                            <?php while ($activity = $activities->fetch_assoc()): ?>
                                <tr>
                                    <td class="timestamp"><?= date('M j, Y g:i:s A', strtotime($activity['created_at'])) ?></td>
                                    <td>
                                        <span class="platform-badge platform-<?= $activity['platform'] ?>">
                                            <?= ucfirst($activity['platform']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="action-badge action-<?= $activity['action'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $activity['action'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $activity['username'] ? '@' . htmlspecialchars($activity['username']) : '-' ?></td>
                                    <td><?= htmlspecialchars($activity['email'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($activity['ip_address'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($activity['details'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-data">No activity found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="info-box">
                <p><strong>Showing:</strong> Last <?= $activities->num_rows ?> activities (limit: <?= $limit ?>)</p>
            </div>
        </div>
    </div>
</body>
</html>
