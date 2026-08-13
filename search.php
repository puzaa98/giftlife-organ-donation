<?php
include 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$results = [];
$search_term = "";
$count = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_term = $_POST['search'];
    $filter = $_POST['filter_by'];
    
    if ($filter == 'blood') {
        $results = $conn->query("SELECT u.name, d.blood_group, d.organ_willing, u.phone, u.location, 'Donor' as type 
                                FROM donors d JOIN users u ON d.user_id = u.id 
                                WHERE blood_group LIKE '%$search_term%'");
    } elseif ($filter == 'organ') {
        $results = $conn->query("SELECT u.name, d.blood_group, d.organ_willing, u.phone, u.location, 'Donor' as type 
                                FROM donors d JOIN users u ON d.user_id = u.id 
                                WHERE organ_willing LIKE '%$search_term%' 
                                UNION 
                                SELECT u.name, r.blood_group, r.organ_needed as organ_willing, u.phone, u.location, 'Recipient' as type 
                                FROM recipients r JOIN users u ON r.user_id = u.id 
                                WHERE organ_needed LIKE '%$search_term%'");
    } elseif ($filter == 'location') {
        $results = $conn->query("SELECT u.name, u.role, u.phone, u.location, '' as organ_willing, u.role as type 
                                FROM users u 
                                WHERE location LIKE '%$search_term%'");
    } else {
        $results = $conn->query("SELECT u.name, u.role, u.phone, u.location, '' as organ_willing, u.role as type 
                                FROM users u 
                                WHERE name LIKE '%$search_term%' OR email LIKE '%$search_term%'");
    }
    if ($results) $count = $results->num_rows;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="top-nav">
    <div class="logo-box"><span class="icon">🔍</span><span class="brand">Gift<span>Life</span></span></div>
    <div class="nav-links">
        <span class="user-badge"><span class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'],0,1)); ?></span> <?php echo $_SESSION['user_name']; ?></span>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="dashboard-box">
    <a href="dashboard.php" style="color:#0d47a1; font-weight:600;">← Back to Dashboard</a>
    <h1>🔍 Search & Filter</h1>
    <p class="subtitle">Find donors, recipients, or hospitals.</p>

    <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
        <input type="text" name="search" placeholder="Enter keyword..." required style="flex:1; min-width:200px;">
        <select name="filter_by" style="flex:1; min-width:150px;">
            <option value="name">Name / Email</option>
            <option value="blood">Blood Group</option>
            <option value="organ">Organ Type</option>
            <option value="location">Location</option>
        </select>
        <button type="submit" style="width:auto; padding:14px 30px; flex:0 0 auto;">Search</button>
    </form>

    <?php if($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <hr style="margin:20px 0;">
        <h2>Results for "<?php echo htmlspecialchars($search_term); ?>" (<?php echo $count; ?> found)</h2>
        
        <?php if($results && $count > 0): ?>
        <div style="overflow-x:auto;">
            <table>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Blood / Organ</th>
                    <th>Phone</th>
                    <th>Location</th>
                </tr>
                <?php while($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo $row['name']; ?></strong></td>
                    <td><span class="badge badge-info"><?php echo ucfirst($row['type'] ?? 'User'); ?></span></td>
                    <td><?php echo $row['organ_willing'] ?? $row['role'] ?? '-'; ?></td>
                    <td><?php echo $row['phone'] ?? '-'; ?></td>
                    <td><?php echo $row['location'] ?? '-'; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php else: ?>
            <p style="color:#64748b;">No results found.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>