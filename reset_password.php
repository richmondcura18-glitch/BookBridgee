<?php
session_start();
include "config.php";

// If the user hasn't passed the security question, kick them back to login
if(!isset($_SESSION['reset_user_id'])){
    header("Location: login.php");
    exit();
}

$message = '';
$user_id = $_SESSION['reset_user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if($password && $confirm){
        // Complexity Check
        if (!preg_match('@[A-Z]@', $password) || !preg_match('@[0-9]@', $password) || !preg_match('@[^\w]@', $password) || strlen($password) < 8) {
            $message = "Requires: 8+ chars, 1 Capital, 1 Number, 1 Symbol";
        } elseif($password === $confirm){
            // Passwords match -> Hash and Update
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $password_hashed, $user_id);
            
            if($stmt->execute()){
                // Clear the temporary reset session
                unset($_SESSION['reset_user_id']);
                echo "<script>alert('Password updated successfully!'); window.location='login.php';</script>";
                exit();
            }
        } else {
            $message = "Passwords do not match!";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - BookBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .match-mismatch { border-color: #ef4444 !important; }
        .match-success { border-color: #2ecc71 !important; }
    </style>
</head>
<body class="auth-bg"> 
<div class="content-wrapper">
    <div class="outside-text">
        <h1><span class="highlight-word">New</span> Password</h1>
        <p>Secure your student account</p>
    </div>

    <div class="form-container">
        <?php if($message): ?>
            <div class="error"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-section">Security Update</div>
            
            <label>New Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="resetPass" placeholder="Min. 8 characters" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('resetPass', this)"></i>
            </div>

            <label>Confirm Password</label>
            <div class="password-wrapper">
                <input type="password" name="confirm" id="resetConf" placeholder="Verify password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('resetConf', this)"></i>
            </div>
            
            <button type="submit" name="reset" class="btn-main">Update Password</button>
            
            <div class="links" style="text-align:center; margin-top:20px;">
                <a href="login.php" style="color:var(--ucc-green); text-decoration:none; font-weight:bold;">← Back to Login</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePass(inputId, icon) {
    const field = document.getElementById(inputId);
    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        field.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

// Real-time Confirm Password Validation
const newPass = document.getElementById('resetPass');
const confPass = document.getElementById('resetConf');

confPass.addEventListener('keyup', function() {
    if (confPass.value === "") {
        confPass.classList.remove('match-success', 'match-mismatch');
    } else if (newPass.value === confPass.value) {
        confPass.classList.add('match-success');
        confPass.classList.remove('match-mismatch');
    } else {
        confPass.classList.add('match-mismatch');
        confPass.classList.remove('match-success');
    }
});
</script>
</body>
</html>