<?php
include 'config/database.php';
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $message = "Password reset link sent (Demo).";
    } else {
        $message = "Email not found!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <div style="text-align:center; margin-bottom:20px;">
        <span style="font-size:40px;">🔑</span>
        <h1 style="font-size:28px; margin:0;">Gift<span style="color:#00bcd4;">Life</span></h1>
    </div>
    <?php if($message): ?><p style="color:blue;"><?php echo $message; ?></p><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Reset Link</button>
    </form>
    <p style="text-align:center; margin-top:15px;"><a href="login.php" style="color:#0d47a1;">Back to Login</a></p>
</div>
</body>
</html>