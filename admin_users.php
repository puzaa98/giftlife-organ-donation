<?php
include 'config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
if ($filter == 'donor') {
    $users = $conn->query("SELECT u.* FROM users u JOIN donors d ON u.id = d.user_id ORDER BY u.created_at DESC");
    $title = "Donors";
    $subtitle = "All registered donors in the GiftLife system.";
} elseif ($filter == 'recipient') {
    $users = $conn->query("SELECT u.* FROM users u JOIN recipients r ON u.id = r.user_id ORDER BY u.created_at DESC");
    $title = "Recipients";
    $subtitle = "All registered recipients in the GiftLife system.";
} elseif ($filter == 'doctor') {
    $users = $conn->query("SELECT * FROM doctors ORDER BY name ASC");
    $title = "Doctors";
    $subtitle = "All registered doctors in the GiftLife system.";
} else {
    $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    $title = "All Users";
    $subtitle = "All registered users in the GiftLife system.";
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header('Location: admin_users.php' . ($filter != 'all' ? '?filter=' . $filter : ''));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $role = $_POST['role'];
    $conn->query("UPDATE users SET role='$role' WHERE id=$id");
    header('Location: admin_users.php' . ($filter != 'all' ? '?filter=' . $filter : ''));
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage <?php echo $title; ?> - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filter-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; }
        .filter-btn { padding: 8px 20px; border-radius: 8px; text-decoration: none; color: white; font-weight: 600; font-size: 14px; display: inline-block; }
        .filter-btn.active { background: #0d47a1; }
        .filter-btn.inactive { background: #64748b; }
        .filter-btn:hover { opacity: 0.8; }
    </style>
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">👥</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge">Admin</span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>👥 Manage <?php echo $title; ?></h1>
    <p class="subtitle"><?php echo $subtitle; ?></p>

    <div class="filter-buttons">
        <a href="admin_users.php?filter=all" class="filter-btn <?php echo ($filter=='all') ? 'active' : 'inactive'; ?>">All Users</a>
        <a href="admin_users.php?filter=donor" class="filter-btn <?php echo ($filter=='donor') ? 'active' : 'inactive'; ?>">Donors</a>
        <a href="admin_users.php?filter=recipient" class="filter-btn <?php echo ($filter=='recipient') ? 'active' : 'inactive'; ?>">Recipients</a>
        <a href="admin_users.php?filter=doctor" class="filter-btn <?php echo ($filter=='doctor') ? 'active' : 'inactive'; ?>">Doctors</a>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Location</th>
                <?php if($filter != 'doctor'): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
            <?php if($users && $users->num_rows > 0): ?>
            <?php while($row = $users->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><strong><?php echo $row['name']; ?></strong></td>
                <td><?php echo $row['email'] ?? '-'; ?></td>
                <td>
                    <?php if($filter != 'doctor'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                        <select name="role" onchange="this.form.submit()" style="padding:3px; border-radius:5px;">
                            <option value="admin" <?php if($row['role']=='admin') echo 'selected'; ?>>Admin</option>
                            <option value="donor" <?php if($row['role']=='donor') echo 'selected'; ?>>Donor</option>
                            <option value="recipient" <?php if($row['role']=='recipient') echo 'selected'; ?>>Recipient</option>
                            <option value="hospital" <?php if($row['role']=='hospital') echo 'selected'; ?>>Hospital</option>
                        </select>
                    </form>
                    <?php else: ?>
                    <span class="badge badge-info">Doctor</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $row['phone'] ?? '-'; ?></td>
                <td><?php echo $row['location'] ?? '-'; ?></td>
                <?php if($filter != 'doctor'): ?>
                <td>
                    <a href="admin_users.php?delete=<?php echo $row['id']; ?><?php echo ($filter != 'all' ? '&filter=' . $filter : ''); ?>" onclick="return confirm('Delete this user?')" style="color:#c62828;">Delete</a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No <?php echo strtolower($title); ?> found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</body>
</html>