<?php
include 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$nots = $conn->query("SELECT * FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC");
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$user_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">🔔</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge"><span class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'],0,1)); ?></span> <?php echo $_SESSION['user_name']; ?></span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>🔔 Notifications</h1>
    <p class="subtitle">Your latest updates and alerts.</p>

    <?php if($nots->num_rows > 0): ?>
        <div style="overflow-x:auto;">
            <table>
                <tr>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
                <?php while($row = $nots->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['message']; ?></td>
                    <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else: ?>
        <div style="background:#f0f4f8; padding:30px; border-radius:10px; text-align:center;">
            <span style="font-size:50px;">🔕</span>
            <p style="color:#64748b; margin-top:10px;">No notifications yet.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>