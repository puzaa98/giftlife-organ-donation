<?php
include 'config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recipient') {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$msg = "";
$rec = $conn->query("SELECT id FROM recipients WHERE user_id=$user_id")->fetch_assoc();
$rid = $rec['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $organ = $_POST['organ_needed'];
    $bg = $_POST['blood_group'];
    $urgency = $_POST['urgency'];
    $bp = $_POST['blood_pressure'];
    $hr = $_POST['heart_rate'];
    $oxygen = $_POST['oxygen_level'];
    $dialysis = $_POST['dialysis_status'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_phone = $_POST['emergency_phone'];

    $conn->query("UPDATE recipients SET blood_group='$bg', urgency_level='$urgency', blood_pressure='$bp', heart_rate=$hr, oxygen_level=$oxygen, dialysis_status='$dialysis', emergency_contact_name='$emergency_name', emergency_contact_phone='$emergency_phone' WHERE user_id=$user_id");
    $conn->query("INSERT INTO requests (recipient_id, organ_needed, blood_group, urgency_level, request_date, status) 
                  VALUES ($rid, '$organ', '$bg', '$urgency', CURDATE(), 'pending')");
    $msg = "✅ Organ request submitted successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Organ - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">🏥</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge"><span class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'],0,1)); ?></span> <?php echo $_SESSION['user_name']; ?></span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>🆕 Organ Request</h1>
    <p class="subtitle">🏥 Provide your medical details to find a matching donor</p>

    <?php if($msg): ?>
        <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:10px; margin-bottom:15px;">
            <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <h2>🩸 Blood & Organ Details</h2>
        <div class="form-group">
            <label><i class="fas fa-tint" style="color:#0d47a1;"></i> Your Blood Group</label>
            <select name="blood_group" required>
                <option value="">Select Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-hand-holding-heart" style="color:#0d47a1;"></i> Organ Needed</label>
            <input type="text" name="organ_needed" placeholder="e.g., Kidney, Liver, Heart" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-exclamation-triangle" style="color:#0d47a1;"></i> Urgency Level</label>
            <select name="urgency">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical (Immediate)</option>
            </select>
        </div>

        <hr style="margin: 20px 0;">

        <h2>🏥 Patient Vitals</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
            <div class="form-group">
                <label><i class="fas fa-heartbeat" style="color:#0d47a1;"></i> Blood Pressure</label>
                <input type="text" name="blood_pressure" placeholder="e.g., 120/80">
            </div>
            <div class="form-group">
                <label><i class="fas fa-pulse" style="color:#0d47a1;"></i> Heart Rate</label>
                <input type="number" name="heart_rate" placeholder="e.g., 72">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lungs" style="color:#0d47a1;"></i> Oxygen Level %</label>
                <input type="number" name="oxygen_level" placeholder="e.g., 98">
            </div>
            <div class="form-group">
                <label><i class="fas fa-procedures" style="color:#0d47a1;"></i> Dialysis</label>
                <select name="dialysis_status">
                    <option value="No">No</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>
        </div>

        <hr style="margin: 20px 0;">

        <h2>📞 Emergency Contact</h2>
        <div class="form-group">
            <label><i class="fas fa-user" style="color:#0d47a1;"></i> Emergency Contact Name</label>
            <input type="text" name="emergency_name" placeholder="Full name">
        </div>
        <div class="form-group">
            <label><i class="fas fa-phone" style="color:#0d47a1;"></i> Emergency Contact Phone</label>
            <input type="text" name="emergency_phone" placeholder="Phone number">
        </div>

        <button type="submit" style="background:linear-gradient(135deg, #0d47a1, #00bcd4);">
            <i class="fas fa-paper-plane"></i> Submit Request
        </button>
    </form>
</div>
</body>
</html>