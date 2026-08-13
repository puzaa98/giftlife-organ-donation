<?php
include 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM requests WHERE id=" . $_GET['delete']);
    header('Location: view_requests.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $conn->query("UPDATE requests SET organ_needed='{$_POST['organ_needed']}', blood_group='{$_POST['blood_group']}', urgency_level='{$_POST['urgency']}', status='{$_POST['status']}' WHERE id=$id");
    header('Location: view_requests.php');
    exit();
}

if ($role == 'recipient') {
    $rec = $conn->query("SELECT id FROM recipients WHERE user_id=$user_id")->fetch_assoc();
    $rid = $rec['id'];
    $result = $conn->query("SELECT * FROM requests WHERE recipient_id=$rid ORDER BY request_date DESC");
} else {
    $result = $conn->query("SELECT * FROM requests ORDER BY request_date DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Requests - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">📋</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge"><span class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'],0,1)); ?></span> <?php echo $_SESSION['user_name']; ?></span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>📋 Organ Requests</h1>
    <p class="subtitle">View, edit, or delete your requests.</p>

    <div style="overflow-x:auto;">
        <table>
            <tr>
                <th>ID</th>
                <th>Organ</th>
                <th>Blood</th>
                <th>Urgency</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
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
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['request_date']; ?></td>
                <td>
                    <a href="#" onclick="document.getElementById('edit-<?php echo $row['id']; ?>').style.display='block'" style="color:#0d47a1;">✏️ Edit</a>
                    <a href="view_requests.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this request?')" style="color:#c62828;">🗑️ Delete</a>
                    <div id="edit-<?php echo $row['id']; ?>" style="display:none; background:#f0f4f8; padding:15px; border-radius:10px; margin-top:10px;">
                        <form method="POST">
                            <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                            <input type="text" name="organ_needed" value="<?php echo $row['organ_needed']; ?>" style="width:25%; display:inline-block;">
                            <input type="text" name="blood_group" value="<?php echo $row['blood_group']; ?>" style="width:15%; display:inline-block;">
                            <select name="urgency" style="width:18%; display:inline-block;">
                                <option value="low" <?php if(isset($row['urgency_level']) && $row['urgency_level']=='low') echo 'selected'; ?>>Low</option>
                                <option value="medium" <?php if(isset($row['urgency_level']) && $row['urgency_level']=='medium') echo 'selected'; ?>>Medium</option>
                                <option value="high" <?php if(isset($row['urgency_level']) && $row['urgency_level']=='high') echo 'selected'; ?>>High</option>
                                <option value="critical" <?php if(isset($row['urgency_level']) && $row['urgency_level']=='critical') echo 'selected'; ?>>Critical</option>
                            </select>
                            <select name="status" style="width:18%; display:inline-block;">
                                <option value="pending" <?php if($row['status']=='pending') echo 'selected'; ?>>Pending</option>
                                <option value="matched" <?php if($row['status']=='matched') echo 'selected'; ?>>Matched</option>
                                <option value="transplanted" <?php if($row['status']=='transplanted') echo 'selected'; ?>>Transplanted</option>
                                <option value="cancelled" <?php if($row['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <button type="submit" style="width:auto; display:inline-block; padding:10px 20px;">Update</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No requests found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>
</body>
</html>