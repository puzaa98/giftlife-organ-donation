<?php
include 'config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

if (isset($_GET['start'])) {
    $match_id = $_GET['start'];
    
    // FIRST CHECK: Does this match already have a procurement?
    $check = $conn->query("SELECT id FROM procurements WHERE match_id = $match_id");
    if ($check->num_rows > 0) {
        // Procurement already exists, redirect to procurement page
        header('Location: procurement.php?msg=already_exists');
        exit();
    }
    
    // Get match details with correct column names
    $m = $conn->query("SELECT 
                        m.*, 
                        d.id as donor_table_id,
                        rec.id as recipient_table_id,
                        d.user_id as donor_user_id,
                        rec.user_id as recipient_user_id,
                        req.organ_needed,
                        u1.name as donor_name,
                        u2.name as recipient_name
                        FROM matches m 
                        JOIN donors d ON m.donor_id = d.id 
                        JOIN users u1 ON d.user_id = u1.id
                        JOIN requests req ON m.request_id = req.id 
                        JOIN recipients rec ON req.recipient_id = rec.id 
                        JOIN users u2 ON rec.user_id = u2.id
                        WHERE m.id = $match_id")->fetch_assoc();
    
    // Insert into procurements using correct IDs
    $conn->query("INSERT INTO procurements (match_id, donor_id, recipient_id, organ_name, status, retrieval_date, transport_mode) 
                  VALUES ($match_id, {$m['donor_table_id']}, {$m['recipient_table_id']}, '{$m['organ_needed']}', 'Scheduled', CURDATE(), 'Ambulance')");
    
    $conn->query("UPDATE matches SET procurement_status='scheduled' WHERE id=$match_id");
    header('Location: procurement.php');
    exit();
}

if (isset($_POST['update_transport'])) {
    $proc_id = $_POST['proc_id'];
    $status = $_POST['transport_status'];
    $mode = $_POST['transport_mode'];
    $doctor = $_POST['assigned_doctor'];
    $nurse = $_POST['assigned_nurse'];
    $conn->query("UPDATE procurements SET transport_status='$status', transport_mode='$mode', assigned_doctor='$doctor', assigned_nurse='$nurse' WHERE id=$proc_id");
    header('Location: procurement.php');
    exit();
}

if (isset($_POST['update_status'])) {
    $proc_id = $_POST['proc_id'];
    $status = $_POST['status'];
    $conn->query("UPDATE procurements SET status='$status' WHERE id=$proc_id");
    header('Location: procurement.php');
    exit();
}

// Check if msg exists
$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'already_exists') {
    $msg = '⚠️ Procurement already exists for this match!';
}

$procs = $conn->query("SELECT p.*, u1.name as donor_name, u2.name as recipient_name, h.hospital_name 
                        FROM procurements p
                        JOIN donors d ON p.donor_id = d.id
                        JOIN users u1 ON d.user_id = u1.id
                        JOIN recipients rec ON p.recipient_id = rec.id
                        JOIN users u2 ON rec.user_id = u2.id
                        LEFT JOIN hospitals h ON p.hospital_id = h.id
                        ORDER BY p.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Procurement - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">🚑</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge">Admin</span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>🚑 Procurement Pipeline</h1>
    <p class="subtitle">Tracking organ retrieval, ambulance transport, and transplant logistics.</p>

    <?php if($msg): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:10px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <?php if ($procs->num_rows == 0): ?>
        <div style="background:#f0f0f0; padding:20px; border-radius:10px; margin:20px 0;">
            <p>No active procurements. Go to <a href="match_organ.php" style="color:#0d47a1; font-weight:bold;">Auto-Match</a> and click "Start Procurement".</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Donor</th>
                    <th>Patient</th>
                    <th>Organ</th>
                    <th>Ambulance</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
                <?php while($row = $procs->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td><?php echo $row['donor_name']; ?></td>
                    <td><?php echo $row['recipient_name']; ?></td>
                    <td><strong><?php echo $row['organ_name']; ?></strong></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="proc_id" value="<?php echo $row['id']; ?>">
                            <select name="transport_mode" onchange="this.form.submit()" style="padding:5px; width:auto; display:inline-block;">
                                <option value="Ambulance" <?php if($row['transport_mode']=='Ambulance') echo 'selected'; ?>>🚑 Ambulance</option>
                                <option value="Air Ambulance" <?php if($row['transport_mode']=='Air Ambulance') echo 'selected'; ?>>🚁 Air Ambulance</option>
                                <option value="Private Vehicle" <?php if($row['transport_mode']=='Private Vehicle') echo 'selected'; ?>>🚗 Private Vehicle</option>
                            </select>
                            <select name="transport_status" onchange="this.form.submit()" style="padding:5px; width:auto; display:inline-block;">
                                <option value="Not Started" <?php if($row['transport_status']=='Not Started') echo 'selected'; ?>>Not Started</option>
                                <option value="In Transit" <?php if($row['transport_status']=='In Transit') echo 'selected'; ?>>In Transit</option>
                                <option value="Arrived" <?php if($row['transport_status']=='Arrived') echo 'selected'; ?>>Arrived</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="proc_id" value="<?php echo $row['id']; ?>">
                            <input type="text" name="assigned_doctor" placeholder="Doctor" value="<?php echo $row['assigned_doctor']; ?>" style="width:100px; display:inline-block; padding:5px;">
                            <input type="text" name="assigned_nurse" placeholder="Nurse" value="<?php echo $row['assigned_nurse']; ?>" style="width:100px; display:inline-block; padding:5px;">
                            <button type="submit" name="update_transport" style="width:auto; display:inline-block; padding:5px 10px; font-size:12px;">Assign</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="proc_id" value="<?php echo $row['id']; ?>">
                            <select name="status" onchange="this.form.submit()" style="padding:5px; width:auto; display:inline-block;">
                                <option value="Scheduled" <?php if($row['status']=='Scheduled') echo 'selected'; ?>>Scheduled</option>
                                <option value="In Progress" <?php if($row['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Arrived" <?php if($row['status']=='Arrived') echo 'selected'; ?>>Arrived</option>
                                <option value="Transplanted" <?php if($row['status']=='Transplanted') echo 'selected'; ?>>Transplanted</option>
                                <option value="Completed" <?php if($row['status']=='Completed') echo 'selected'; ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>

    <hr style="margin: 30px 0;">
    <a href="match_organ.php" class="btn" style="background:#0a1e3c; width:auto;"><i class="fas fa-arrow-left"></i> Back to Auto-Match</a>
</div>
</body>
</html>