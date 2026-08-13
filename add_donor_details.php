<?php
include 'config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'donor') {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$msg = "";
$donor = $conn->query("SELECT * FROM donors WHERE user_id=$user_id")->fetch_assoc();

$compatible_patients = 0;
if ($donor && $donor['blood_group']) {
    $bg = $donor['blood_group'];
    $comp = $conn->query("SELECT COUNT(*) as count FROM recipients WHERE blood_group='$bg'");
    $compatible_patients = $comp->fetch_assoc()['count'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bg = $_POST['blood_group'];
    $age = $_POST['age'];
    $organ = $_POST['organ_willing'];
    $history = $_POST['medical_history'];
    $available = isset($_POST['is_available']) ? 1 : 0;
    
    $conn->query("UPDATE donors SET blood_group='$bg', age=$age, organ_willing='$organ', medical_history='$history', is_available=$available WHERE user_id=$user_id");
    $msg = "✅ Donor Profile Updated Successfully!";
    $donor = $conn->query("SELECT * FROM donors WHERE user_id=$user_id")->fetch_assoc();
    
    if ($donor['blood_group']) {
        $comp = $conn->query("SELECT COUNT(*) as count FROM recipients WHERE blood_group='{$donor['blood_group']}'");
        $compatible_patients = $comp->fetch_assoc()['count'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Donor Profile - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="top-nav">
    <div class="logo-box"><span class="icon">🧑‍⚕️</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge"><span class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'],0,1)); ?></span> <?php echo $_SESSION['user_name']; ?></span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>🧑‍⚕️ Donor Profile</h1>
    <p class="subtitle">Complete your donor profile to save lives</p>

    <?php if($msg): ?>
        <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:10px; margin-bottom:15px;">
            <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <?php if($donor && $donor['blood_group']): ?>
    <div style="background:linear-gradient(135deg, #e8f0fe, #d4e4f7); padding:20px; border-radius:12px; margin-bottom:20px; border-left:5px solid #0d47a1;">
        <h3 style="margin:0;">🩸 You can save <strong><?php echo $compatible_patients; ?></strong> patients with your blood group!</h3>
        <p style="color:#64748b; margin:5px 0 0 0;">Your blood group <?php echo $donor['blood_group']; ?> matches <?php echo $compatible_patients; ?> registered recipients.</p>
    </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-tint" style="color:#0d47a1;"></i> Blood Group *</label>
            <select name="blood_group" required>
                <option value="">Select Blood Group</option>
                <option value="A+" <?php if($donor['blood_group']=='A+') echo 'selected'; ?>>A+</option>
                <option value="A-" <?php if($donor['blood_group']=='A-') echo 'selected'; ?>>A-</option>
                <option value="B+" <?php if($donor['blood_group']=='B+') echo 'selected'; ?>>B+</option>
                <option value="B-" <?php if($donor['blood_group']=='B-') echo 'selected'; ?>>B-</option>
                <option value="AB+" <?php if($donor['blood_group']=='AB+') echo 'selected'; ?>>AB+</option>
                <option value="AB-" <?php if($donor['blood_group']=='AB-') echo 'selected'; ?>>AB-</option>
                <option value="O+" <?php if($donor['blood_group']=='O+') echo 'selected'; ?>>O+ (Universal Donor)</option>
                <option value="O-" <?php if($donor['blood_group']=='O-') echo 'selected'; ?>>O- (Universal Donor)</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-calendar-alt" style="color:#0d47a1;"></i> Age</label>
            <input type="number" name="age" placeholder="Your age" value="<?php echo $donor['age']; ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-hand-holding-heart" style="color:#0d47a1;"></i> Organs Willing to Donate</label>
            <input type="text" name="organ_willing" placeholder="e.g., Kidney, Liver, Heart, Lungs" value="<?php echo $donor['organ_willing']; ?>" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-notes-medical" style="color:#0d47a1;"></i> Medical History</label>
            <textarea name="medical_history" placeholder="Any medical conditions or history"><?php echo $donor['medical_history']; ?></textarea>
        </div>

        <div class="form-group">
            <label><i class="fas fa-check-circle" style="color:#0d47a1;"></i> Available for Donation</label>
            <input type="checkbox" name="is_available" value="1" <?php if($donor['is_available']) echo 'checked'; ?>> Yes, I am available
        </div>

        <button type="submit" style="background:linear-gradient(135deg, #0d47a1, #00bcd4);">
            <i class="fas fa-save"></i> Save Donor Profile
        </button>
    </form>
</div>
</body>
</html>