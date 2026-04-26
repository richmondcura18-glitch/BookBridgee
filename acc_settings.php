<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

if(isset($_POST['change'])){
    $current = trim($_POST['current_password']);
    $new = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if(!$user) {
        $message = "User account not found.";
        $message_type = "error";
    } elseif(!password_verify($current, $user['password'])){
        $message = "Current password is incorrect!";
        $message_type = "error";
    } elseif($new !== $confirm){
        $message = "New passwords do not match!";
        $message_type = "error";
    } elseif (!preg_match('@[A-Z]@', $new) || !preg_match('@[0-9]@', $new) || !preg_match('@[^\w]@', $new) || strlen($new) < 8) {
        $message = "New password must be at least 8 characters and include 1 uppercase letter, 1 number, and 1 special character.";
        $message_type = "error";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param("si", $hashed, $user_id);
        
        if($update->execute()) {
            $message = "Password updated successfully!";
            $message_type = "success";
        } else {
            $message = "Database error.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings - BookBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="settings-page">

<nav>
    <div class="logo">
        <a href="index.php" style="text-decoration:none; display:flex; align-items:center;">
            <img src="images/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
            <span style="font-weight:bold; font-size:1.5rem; color:#2C3E50;">
                <span style="color:var(--ucc-green);">BOOK</span><span style="color:var(--ucc-orange);">Bridge</span>
            </span>
        </a>
    </div>
    <div class="nav-links">
        <a href="index.php" class="nav-link-item"><i class="fa-solid fa-house"></i> <span>Library</span></a>
    </div>
</nav>

<div class="content-wrapper">
    <div class="outside-text">
        <h1><span class="highlight-word">Account</span> Settings</h1>
        <p>Manage your security preferences</p>
    </div>

    <div class="settings-card">
        <?php if($message): ?>
            <div class="<?php echo $message_type === 'error' ? 'error' : 'success-msg'; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-section">Change Password</div>
            
            <label>Current Password</label>
            <div class="password-wrapper">
                <input type="password" name="current_password" id="currPass" placeholder="••••••••" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('currPass', this)"></i>
            </div>

            <label>New Password</label>
            <div class="password-wrapper">
                <input type="password" name="new_password" id="newPass" placeholder="Min. 8 chars" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('newPass', this)"></i>
            </div>

            <label>Confirm Password</label>
            <div class="password-wrapper">
                <input type="password" name="confirm_password" id="confPass" placeholder="Confirm new password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('confPass', this)"></i>
            </div>

            <button type="submit" name="change" class="btn-main">Update Security</button>
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
</script>
</body>
</html>