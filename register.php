<?php
include 'config/database.php';
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $emergency = $_POST['emergency_contact'];

    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $sql = "INSERT INTO users (name, email, password, role, phone, location, gender, dob, emergency_contact) 
                VALUES ('$name', '$email', '$password', '$role', '$phone', '$location', '$gender', '$dob', '$emergency')";
        
        if ($conn->query($sql) === TRUE) {
            $user_id = $conn->insert_id;
            if ($role == 'donor') $conn->query("INSERT INTO donors (user_id) VALUES ($user_id)");
            elseif ($role == 'recipient') $conn->query("INSERT INTO recipients (user_id) VALUES ($user_id)");
            elseif ($role == 'hospital') $conn->query("INSERT INTO hospitals (user_id) VALUES ($user_id)");
            $success = "Registration successful! <a href='login.php' style='color:#0d47a1; font-weight:bold;'>Login here</a>";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - GiftLife</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div style="text-align:center; margin-bottom:25px; padding:10px; background:linear-gradient(135deg, #f8faff, #e8f0fe); border-radius:16px;">
        <div style="display:flex; align-items:center; justify-content:center; gap:15px; flex-wrap:wrap;">
            <span style="font-size:48px; background:linear-gradient(135deg, #0a1e3c, #0d47a1); padding:12px; border-radius:100px; box-shadow:0 10px 30px rgba(13,71,161,0.3);">🧑‍⚕️</span>
            <span style="font-weight:800; font-size:36px; color:#0d47a1; letter-spacing:-1px;">Gift<span style="color:#00bcd4;">Life</span></span>
            <span style="font-size:30px;">🫀</span>
        </div>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Connecting Donors to Recipients</p>
    </div>
    
    <?php if($error): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:10px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div style="background:#dcfce7; color:#166534; padding:12px; border-radius:10px; margin-bottom:15px;">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" id="registerForm">
        <div class="form-group">
            <label><i class="fas fa-user" style="color:#0d47a1;"></i> Full Name</label>
            <input type="text" name="name" placeholder="Enter your full name" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-envelope" style="color:#0d47a1;"></i> Email Address</label>
            <input type="email" name="email" placeholder="your@email.com" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-lock" style="color:#0d47a1;"></i> Password</label>
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="Min 6 characters" required onkeyup="checkStrength(this.value)">
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </button>
            </div>
            <div class="strength-meter" id="strengthMeter"></div>
            <div class="strength-text" id="strengthText">Enter a strong password</div>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-phone" style="color:#0d47a1;"></i> Phone Number</label>
            <input type="text" name="phone" placeholder="017xxxxxxxx" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-map-marker-alt" style="color:#0d47a1;"></i> Location</label>
            <input type="text" name="location" placeholder="City / District" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-venus-mars" style="color:#0d47a1;"></i> Gender</label>
            <select name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-calendar-alt" style="color:#0d47a1;"></i> Date of Birth</label>
            <input type="date" name="dob" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-phone" style="color:#0d47a1;"></i> Emergency Contact</label>
            <input type="text" name="emergency_contact" placeholder="Emergency number" required>
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-user-tag" style="color:#0d47a1;"></i> I am a</label>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="donor">Donor (Want to donate organs)</option>
                <option value="recipient">Recipient (Need an organ)</option>
                <option value="hospital">Hospital (Medical facility)</option>
                <option value="admin">Admin (System manager)</option>
            </select>
        </div>
        
        <button type="submit" style="margin-top:20px; background:linear-gradient(135deg, #0d47a1, #00bcd4);">
            <i class="fas fa-user-plus"></i> Register Now
        </button>
    </form>
    
    <p style="text-align:center; margin-top:20px; color:#64748b;">
        Already have account? <a href="login.php" style="color:#0d47a1; font-weight:600;">Login</a>
    </p>
</div>

<script>
function togglePassword() {
    var pass = document.getElementById('password');
    var icon = document.getElementById('toggleIcon');
    if (pass.type === 'password') { pass.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { pass.type = 'password'; icon.className = 'fas fa-eye'; }
}
function checkStrength(password) {
    var meter = document.getElementById('strengthMeter');
    var text = document.getElementById('strengthText');
    if (password.length === 0) { meter.className = 'strength-meter'; text.className = 'strength-text'; text.textContent = 'Enter a strong password'; return; }
    var strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    if (strength <= 2) { meter.className = 'strength-meter weak'; text.className = 'strength-text weak'; text.textContent = 'Weak password - Add uppercase, numbers, special characters'; }
    else if (strength <= 4) { meter.className = 'strength-meter medium'; text.className = 'strength-text medium'; text.textContent = 'Medium password'; }
    else { meter.className = 'strength-meter strong'; text.className = 'strength-text strong'; text.textContent = 'Strong password!'; }
}
</script>

</body>
</html>