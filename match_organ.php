<?php
include 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$msg = "";

if (isset($_POST['match'])) {
    $conn->query("DELETE FROM matches WHERE status='suggested'");
    
    $requests = $conn->query("SELECT * FROM requests WHERE status='pending'");
    if ($requests->num_rows > 0) {
        while($req = $requests->fetch_assoc()) {
            $blood_group = $req['blood_group'];
            $organ_needed = $req['organ_needed'];
            $urgency = 'medium';
            
            $donors = $conn->query("SELECT d.*, u.name as donor_name FROM donors d 
                                   JOIN users u ON d.user_id = u.id 
                                   WHERE blood_group = '$blood_group' AND is_available = 1");
            
            while($donor = $donors->fetch_assoc()) {
                $score = 70;
                if (strpos($donor['organ_willing'], $organ_needed) !== false) $score += 20;
                if ($donor['age'] >= 18 && $donor['age'] <= 60) $score += 5;
                if ($urgency == 'critical') $score += 10;
                elseif ($urgency == 'high') $score += 5;
                if ($donor['verified'] == 1) $score += 5;
                
                $conn->query("INSERT INTO matches (request_id, donor_id, match_score) 
                              VALUES ({$req['id']}, {$donor['id']}, $score)");
            }
        }
        $msg = "✅ Matching Algorithm Completed Successfully!";
    } else {
        $msg = "⚠️ No pending requests found. Please add a patient request first.";
    }
}

$matches = $conn->query("SELECT 
    m.*,
    u1.name as donor_name,
    u1.phone as donor_phone,
    d.blood_group as donor_blood,
    d.organ_willing,
    u2.name as patient_name,
    r.organ_needed,
    r.blood_group as patient_blood
FROM matches m
JOIN donors d ON m.donor_id = d.id
JOIN users u1 ON d.user_id = u1.id
JOIN requests r ON m.request_id = r.id
JOIN recipients rec ON r.recipient_id = rec.id
JOIN users u2 ON rec.user_id = u2.id
WHERE m.status = 'suggested'
ORDER BY m.match_score DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Auto-Match - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="top-nav">
    <div class="logo-box"><span class="icon">🧬</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge"><span class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'],0,1)); ?></span> <?php echo $_SESSION['user_name']; ?></span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>🧬 Organ Matching System</h1>
    <p class="subtitle">🩸 Find compatible donors for each patient based on blood group and organ type.</p>

    <?php if($msg): ?>
        <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:10px; margin-bottom:15px;">
            <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <button type="submit" name="match" style="background:linear-gradient(135deg, #0d47a1, #00bcd4);">
            <i class="fas fa-sync-alt"></i> Run Matching Algorithm
        </button>
    </form>

    <hr style="margin:25px 0;">

    <?php if($matches && $matches->num_rows > 0): ?>
        <h2>📋 Potential Donor-Patient Matches</h2>
        <div style="overflow-x:auto;">
            <table>
                <tr>
                    <th>Donor</th>
                    <th>Blood</th>
                    <th>Organ</th>
                    <th>Patient</th>
                    <th>Patient Blood</th>
                    <th>Score</th>
                    <th>Action</th>
                </tr>
                <?php while($row = $matches->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?php echo $row['donor_name']; ?></strong><br>
                        <small style="color:#64748b;"><?php echo $row['donor_phone']; ?></small>
                    </td>
                    <td><span class="badge badge-info"><?php echo $row['donor_blood']; ?></span></td>
                    <td><?php echo $row['organ_willing']; ?></td>
                    <td>
                        <strong><?php echo $row['patient_name']; ?></strong><br>
                        <small style="color:#64748b;">Needs: <?php echo $row['organ_needed']; ?></small>
                    </td>
                    <td><span class="badge badge-info"><?php echo $row['patient_blood']; ?></span></td>
                    <td>
                        <span style="font-size:20px; font-weight:800; color:<?php echo $row['match_score'] >= 90 ? '#2e7d32' : ($row['match_score'] >= 70 ? '#f57c00' : '#c62828'); ?>;">
                            <?php echo $row['match_score']; ?>%
                        </span>
                    </td>
                    <td>
                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <a href="procurement.php?start=<?php echo $row['id']; ?>" style="background:#0d47a1; color:white; padding:6px 14px; border-radius:8px; text-decoration:none; font-size:12px; display:inline-block;">
                                Start Procurement
                            </a>
                        <?php else: ?>
                            <span style="color:#64748b;">Admin only</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        
        <div style="background:#e8f0fe; padding:15px; border-radius:10px; margin-top:20px; border-left:5px solid #0d47a1;">
            <p style="margin:0;"><strong>How it works:</strong> Score is calculated based on:
            <br>Blood group match (70%) + Organ compatibility (+20%) + Age (+5%) + Urgency (+5-10%) + Verified donor (+5%)</p>
        </div>
    <?php else: ?>
        <div style="background:#f0f0f0; padding:20px; border-radius:10px; text-align:center;">
            <p>No matches found. Please make sure you have registered donors and recipients with matching blood groups.</p>
            <p style="font-size:12px; color:#64748b;">Tip: Register a Donor and a Recipient with the same blood group, then run match again.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>