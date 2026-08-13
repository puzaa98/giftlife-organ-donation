<?php
include 'config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}
$reqs = $conn->query("SELECT r.*, u.name as rname, u.phone, d.name as doctor_name 
                      FROM requests r 
                      JOIN recipients rec ON r.recipient_id = rec.id 
                      JOIN users u ON rec.user_id = u.id 
                      LEFT JOIN doctors d ON r.doctor_id = d.id
                      ORDER BY r.request_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Requests - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">📋</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge">Admin</span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>📋 All Organ Requests</h1>
    <p class="subtitle">Admin view of all patient organ requests.</p>

    <div style="overflow-x:auto;">
        <table>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Organ</th>
                <th>Blood</th>
                <th>Urgency</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php if($reqs->num_rows > 0): ?>
            <?php while($row = $reqs->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><strong><?php echo $row['rname']; ?></strong><br><small><?php echo $row['phone']; ?></small></td>
                <td><?php echo $row['organ_needed']; ?></td>
                <td><span class="badge badge-info"><?php echo $row['blood_group']; ?></span></td>
                <td>
                    <?php 
                    $urgency = isset($row['urgency_level']) ? $row['urgency_level'] : 'medium';
                    if($urgency == 'critical'): ?>
                        <span class="badge badge-danger">Critical</span>
                    <?php elseif($urgency == 'high'): ?>
                        <span class="badge badge-warning">High</span>
                    <?php else: ?>
                        <span class="badge badge-success"><?php echo ucfirst($urgency); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo $row['doctor_name'] ?? 'Not assigned'; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['request_date']; ?></td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No requests found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</body>
</html>