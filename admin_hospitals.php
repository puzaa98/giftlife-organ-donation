<?php
include 'config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// DELETE HOSPITAL
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM hospitals WHERE id=$id");
    header('Location: admin_hospitals.php');
    exit();
}

// VERIFY HOSPITAL
if (isset($_GET['verify'])) {
    $id = $_GET['verify'];
    $conn->query("UPDATE hospitals SET verified=1 WHERE id=$id");
    header('Location: admin_hospitals.php');
    exit();
}

// FETCH ALL HOSPITALS
$hospitals = $conn->query("SELECT h.*, u.name, u.email, u.phone, u.location 
                           FROM hospitals h 
                           JOIN users u ON h.user_id = u.id 
                           ORDER BY h.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Hospitals - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filter-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; }
        .filter-btn { padding: 8px 20px; border-radius: 8px; text-decoration: none; color: white; font-weight: 600; font-size: 14px; display: inline-block; background: #64748b; }
        .filter-btn:hover { opacity: 0.8; }
        .btn-verify { background: #2e7d32; color: white; padding: 4px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; }
        .btn-verify:hover { background: #1b5e20; }
        .btn-delete { background: #c62828; color: white; padding: 4px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; }
        .btn-delete:hover { background: #b71c1c; }
    </style>
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">🏨</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge">Admin</span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>🏨 Manage Hospitals</h1>
    <p class="subtitle">All registered hospitals in the GiftLife system.</p>

    <?php if($hospitals && $hospitals->num_rows > 0): ?>
    <div style="overflow-x:auto;">
        <table>
            <tr>
                <th>ID</th>
                <th>Hospital Name</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>License</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $hospitals->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><strong><?php echo $row['hospital_name']; ?></strong></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['location']; ?></td>
                <td><?php echo $row['license_number'] ?? '-'; ?></td>
                <td>
                    <?php if($row['verified'] == 1): ?>
                        <span class="badge badge-success">✅ Verified</span>
                    <?php else: ?>
                        <span class="badge badge-warning">⏳ Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row['verified'] == 0): ?>
                        <a href="admin_hospitals.php?verify=<?php echo $row['id']; ?>" class="btn-verify" onclick="return confirm('Verify this hospital?')">Verify</a>
                    <?php endif; ?>
                    <a href="admin_hospitals.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this hospital?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php else: ?>
    <div style="background:#f0f4f8; padding:30px; border-radius:10px; text-align:center;">
        <span style="font-size:50px;">🏨</span>
        <p style="color:#64748b; margin-top:10px;">No hospitals registered yet.</p>
    </div>
    <?php endif; ?>
</div>
</body>
</html>