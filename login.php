<?php
include 'config/database.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Wrong password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div style="text-align:center; margin-bottom:30px;">
        <div style="display:inline-flex; align-items:center; gap:12px; background:linear-gradient(135deg, #0a1e3c, #0d47a1); padding:12px 25px; border-radius:100px; box-shadow:0 10px 30px rgba(13,71,161,0.3);">
            <span style="font-size:35px;">🧑‍⚕️</span>
            <span style="font-weight:800; font-size:28px; color:white; letter-spacing:-0.5px;">Gift<span style="color:#00bcd4;">Life</span></span>
            <span style="font-size:28px;">🫀</span>
        </div>
        <p style="color:#64748b; font-size:14px; margin-top:8px;">Connecting Donors to Recipients</p>
    </div>
    
    <?php if($error): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:10px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-envelope" style="color:#0d47a1;"></i> Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" required>
        </div>
        <div class="form-group">
            <label><i class="fas fa-lock" style="color:#0d47a1;"></i> Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" style="background:linear-gradient(135deg, #0d47a1, #00bcd4);">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>
    
    <p style="text-align:center; margin-top:15px;">
        <a href="register.php" style="color:#0d47a1; font-weight:600;">Register</a> | 
        <a href="forgot.php" style="color:#0d47a1; font-weight:600;">Forgot Password?</a>
    </p>
</div>

</body>
</html>