<?php
include 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$name = $_SESSION['user_name'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$first_letter = strtoupper(substr($name, 0, 1));

if ($role == 'admin') {
    $total_donors = $conn->query("SELECT COUNT(*) as count FROM donors")->fetch_assoc()['count'];
    $total_recipients = $conn->query("SELECT COUNT(*) as count FROM recipients")->fetch_assoc()['count'];
    $total_matches = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status='suggested'")->fetch_assoc()['count'];
    $total_requests = $conn->query("SELECT COUNT(*) as count FROM requests")->fetch_assoc()['count'];
    $total_procurements = $conn->query("SELECT COUNT(*) as count FROM procurements")->fetch_assoc()['count'];
    $total_doctors = $conn->query("SELECT COUNT(*) as count FROM doctors")->fetch_assoc()['count'];
    $total_hospitals = $conn->query("SELECT COUNT(*) as count FROM hospitals")->fetch_assoc()['count'];
    $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
} 
elseif ($role == 'donor') {
    $donor_data = $conn->query("SELECT * FROM donors WHERE user_id=$user_id")->fetch_assoc();
    $compatible_patients = 0;
    if ($donor_data && $donor_data['blood_group']) {
        $bg = $donor_data['blood_group'];
        $comp = $conn->query("SELECT COUNT(*) as count FROM recipients WHERE blood_group='$bg'");
        $compatible_patients = $comp->fetch_assoc()['count'];
    }
    $donor_matches = $conn->query("SELECT COUNT(*) as count FROM matches m JOIN donors d ON m.donor_id = d.id WHERE d.user_id = $user_id")->fetch_assoc()['count'];
    $total_donors = 1;
    $total_recipients = $compatible_patients;
    $total_matches = $donor_matches;
    $total_requests = 0;
    $total_procurements = 0;
    $total_doctors = 0;
    $total_hospitals = 0;
    $total_users = 0;
} 
elseif ($role == 'recipient') {
    $recipient_requests = $conn->query("SELECT COUNT(*) as count FROM requests r JOIN recipients rec ON r.recipient_id = rec.id WHERE rec.user_id = $user_id")->fetch_assoc()['count'];
    $recipient_matches = $conn->query("SELECT COUNT(*) as count FROM matches m JOIN requests r ON m.request_id = r.id JOIN recipients rec ON r.recipient_id = rec.id WHERE rec.user_id = $user_id")->fetch_assoc()['count'];
    $total_donors = 0;
    $total_recipients = 1;
    $total_matches = $recipient_matches;
    $total_requests = $recipient_requests;
    $total_procurements = 0;
    $total_doctors = 0;
    $total_hospitals = 0;
    $total_users = 0;
} 
elseif ($role == 'hospital') {
    $total_donors = $conn->query("SELECT COUNT(*) as count FROM donors")->fetch_assoc()['count'];
    $total_recipients = $conn->query("SELECT COUNT(*) as count FROM recipients")->fetch_assoc()['count'];
    $total_requests = $conn->query("SELECT COUNT(*) as count FROM requests")->fetch_assoc()['count'];
    $total_matches = $conn->query("SELECT COUNT(*) as count FROM matches WHERE status='suggested'")->fetch_assoc()['count'];
    $total_procurements = 0;
    $total_doctors = $conn->query("SELECT COUNT(*) as count FROM doctors")->fetch_assoc()['count'];
    $total_hospitals = 1;
    $total_users = 0;
}

if (!isset($total_donors)) $total_donors = 0;
if (!isset($total_recipients)) $total_recipients = 0;
if (!isset($total_matches)) $total_matches = 0;
if (!isset($total_requests)) $total_requests = 0;
if (!isset($total_procurements)) $total_procurements = 0;
if (!isset($total_doctors)) $total_doctors = 0;
if (!isset($total_hospitals)) $total_hospitals = 0;
if (!isset($total_users)) $total_users = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stat-card a { text-decoration: none; color: inherit; display: block; }
        .stat-card a:hover { opacity: 0.8; }
        .stat-card { transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="logo-box">
        <span class="icon">🧑‍⚕️</span>
        <span class="brand">Gift<span>Life</span></span>
    </div>
    <div class="nav-links">
        <span class="user-badge">
            <span class="user-avatar"><?php echo $first_letter; ?></span>
            <?php echo $name; ?>
        </span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <?php if($role == 'admin'): ?>
            <a href="procurement.php"><i class="fas fa-truck"></i> Procurement</a>
        <?php endif; ?>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">

    <div style="background:linear-gradient(135deg, #0a1e3c, #0d47a1); padding:30px; border-radius:16px; margin-bottom:25px; display:flex; align-items:center; gap:25px; flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <div style="font-size:48px; margin-bottom:5px;">🧑‍⚕️</div>
            <h2 style="color:white; font-size:28px; margin:0;">Welcome to <span style="color:#00bcd4;">GiftLife</span></h2>
            <p style="color:rgba(255,255,255,0.8);">Saving lives through organ donation and procurement.</p>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.15); padding:4px 14px; border-radius:20px; color:white; font-size:12px;">🧑‍⚕️ Donors</span>
                <span style="background:rgba(255,255,255,0.15); padding:4px 14px; border-radius:20px; color:white; font-size:12px;">🏥 Recipients</span>
                <span style="background:rgba(255,255,255,0.15); padding:4px 14px; border-radius:20px; color:white; font-size:12px;">🚑 Ambulance</span>
                <span style="background:rgba(255,255,255,0.15); padding:4px 14px; border-radius:20px; color:white; font-size:12px;">🏨 Hospitals</span>
            </div>
        </div>
        <div style="flex:1; text-align:center;">
            <span style="font-size:80px;">🫀</span>
            <p style="color:rgba(255,255,255,0.6); font-size:12px;">Gift of Life</p>
        </div>
    </div>

    <h1>Dashboard</h1>
    <p class="subtitle">Welcome back, <strong><?php echo $name; ?></strong>! 
    <?php 
    if($role == 'admin') echo "Here's an overview of the entire GiftLife Network.";
    elseif($role == 'donor') echo "Here's your donor impact summary.";
    elseif($role == 'recipient') echo "Here's your request status.";
    elseif($role == 'hospital') echo "Here's the hospital overview.";
    ?>
    </p>

    <div class="stats-grid">
        
        <div class="stat-card">
            <a href="<?php 
                if($role == 'admin') echo 'admin_users.php?filter=donor';
                elseif($role == 'donor') echo 'add_donor_details.php';
                else echo '#';
            ?>">
                <div class="stat-icon">🧑‍⚕️</div>
                <h3><?php echo $total_donors; ?></h3>
                <p>
                    <?php 
                    if($role == 'admin') echo 'Donors';
                    elseif($role == 'donor') echo 'Your Profile';
                    else echo 'Donors';
                    ?>
                </p>
                <?php if($role == 'admin'): ?>
                <small style="color:#0d47a1;">Click to view</small>
                <?php elseif($role == 'donor'): ?>
                <small style="color:#0d47a1;">Click to update</small>
                <?php endif; ?>
            </a>
        </div>

        <div class="stat-card">
            <a href="<?php 
                if($role == 'admin') echo 'admin_users.php?filter=recipient';
                elseif($role == 'recipient') echo 'add_request.php';
                else echo '#';
            ?>">
                <div class="stat-icon">🏥</div>
                <h3><?php echo $total_recipients; ?></h3>
                <p>
                    <?php 
                    if($role == 'admin') echo 'Recipients';
                    elseif($role == 'recipient') echo 'Your Request';
                    else echo 'Recipients';
                    ?>
                </p>
                <?php if($role == 'admin'): ?>
                <small style="color:#0d47a1;">Click to view</small>
                <?php elseif($role == 'recipient'): ?>
                <small style="color:#0d47a1;">Click to request</small>
                <?php endif; ?>
            </a>
        </div>

        <div class="stat-card">
            <a href="<?php 
                if($role == 'admin') echo 'admin_requests.php';
                elseif($role == 'recipient') echo 'view_requests.php';
                elseif($role == 'hospital') echo 'view_requests.php';
                else echo '#';
            ?>">
                <div class="stat-icon">📋</div>
                <h3><?php echo $total_requests; ?></h3>
                <p>
                    <?php 
                    if($role == 'admin') echo 'Requests';
                    elseif($role == 'recipient') echo 'Your Requests';
                    elseif($role == 'hospital') echo 'All Requests';
                    else echo 'Requests';
                    ?>
                </p>
                <small style="color:#0d47a1;">Click to view</small>
            </a>
        </div>

        <div class="stat-card">
            <a href="<?php 
                if($role == 'admin') echo 'match_organ.php';
                elseif($role == 'hospital') echo 'match_organ.php';
                elseif($role == 'recipient') echo 'match_organ.php';
                else echo '#';
            ?>">
                <div class="stat-icon">🧬</div>
                <h3><?php echo $total_matches; ?></h3>
                <p>
                    <?php 
                    if($role == 'admin') echo 'Matches';
                    elseif($role == 'recipient') echo 'Your Matches';
                    else echo 'Matches';
                    ?>
                </p>
                <small style="color:#0d47a1;">Click to view</small>
            </a>
        </div>

        <?php if($role == 'admin'): ?>
        <div class="stat-card">
            <a href="procurement.php">
                <div class="stat-icon">🚑</div>
                <h3><?php echo $total_procurements; ?></h3>
                <p>Procurements</p>
                <small style="color:#0d47a1;">Click to track</small>
            </a>
        </div>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
        <div class="stat-card">
            <a href="admin_users.php?filter=doctor">
                <div class="stat-icon">🩺</div>
                <h3><?php echo $total_doctors; ?></h3>
                <p>Doctors</p>
                <small style="color:#0d47a1;">Click to view</small>
            </a>
        </div>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
        <div class="stat-card">
            <a href="admin_hospitals.php">
                <div class="stat-icon">🏨</div>
                <h3><?php echo $total_hospitals; ?></h3>
                <p>Hospitals</p>
                <small style="color:#0d47a1;">Click to view</small>
            </a>
        </div>
        <?php endif; ?>

        <?php if($role == 'admin'): ?>
        <div class="stat-card">
            <a href="admin_users.php">
                <div class="stat-icon">👥</div>
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
                <small style="color:#0d47a1;">Click to manage</small>
            </a>
        </div>
        <?php endif; ?>

    </div>

    <hr style="margin: 30px 0;">

    <h2>Quick Actions</h2>
    <div class="menu-grid">
        <?php if($role == 'admin'): ?>
            <a href="admin_users.php" class="menu-card"><span class="menu-icon">👥</span> Manage Users</a>
            <a href="admin_hospitals.php" class="menu-card"><span class="menu-icon">🏨</span> Hospitals</a>
            <a href="admin_requests.php" class="menu-card"><span class="menu-icon">📋</span> All Requests</a>
            <a href="match_organ.php" class="menu-card"><span class="menu-icon">🧬</span> Auto-Match</a>
            <a href="procurement.php" class="menu-card" style="background:#0d47a1; color:white;"><span class="menu-icon" style="color:white;">🚑</span> Procurement</a>
            <a href="search.php" class="menu-card"><span class="menu-icon">🔍</span> Search</a>

        <?php elseif($role == 'donor'): ?>
            <a href="add_donor_details.php" class="menu-card"><span class="menu-icon">🧑‍⚕️</span> My Profile</a>
            <a href="view_requests.php" class="menu-card"><span class="menu-icon">📋</span> View Requests</a>
            <a href="search.php" class="menu-card"><span class="menu-icon">🔍</span> Search</a>
            <a href="notifications.php" class="menu-card"><span class="menu-icon">🔔</span> Notifications</a>

        <?php elseif($role == 'recipient'): ?>
            <a href="add_request.php" class="menu-card"><span class="menu-icon">🆕</span> Request Organ</a>
            <a href="view_requests.php" class="menu-card"><span class="menu-icon">📋</span> My Requests</a>
            <a href="match_organ.php" class="menu-card"><span class="menu-icon">🧬</span> Find Match</a>
            <a href="search.php" class="menu-card"><span class="menu-icon">🔍</span> Search</a>

        <?php elseif($role == 'hospital'): ?>
            <a href="view_requests.php" class="menu-card"><span class="menu-icon">📋</span> All Requests</a>
            <a href="search.php" class="menu-card"><span class="menu-icon">🔍</span> Find Donors</a>
            <a href="match_organ.php" class="menu-card"><span class="menu-icon">🧬</span> Auto-Match</a>
            <a href="notifications.php" class="menu-card"><span class="menu-icon">🔔</span> Notifications</a>

        <?php endif; ?>
    </div>

    <div class="footer">
        <p>🫀 GiftLife - Organ Donation & Procurement Network System © 2025</p>
        <p style="font-size:12px; color:#94a3b8;">🧑‍⚕️ Saving lives through technology 🫀</p>
    </div>
</div>

</body>
</html>