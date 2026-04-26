<?php
session_start();
include "config.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            $name_from_db = $user['firstname'] ?? $user['fullname'] ?? 'Student';
            $_SESSION['fullname'] = $name_from_db;

            if($user['is_admin'] == 1){
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $message = "Invalid email or password!";
        }
    } else {
        $message = "Please enter both email and password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BookBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="auth-bg">

<div class="form-container">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1>
            <span class="highlight-word">Book</span>Bridge
        </h1>
        <p style="color: var(--gray); margin-top: 10px;">Welcome back, Student!</p>
    </div>

    <?php if($message): ?>
        <div class="error"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="e.g. name@gmail.com" required>
        
        <label>Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="loginPass" placeholder="••••••••" required>
            <i class="fa-solid fa-eye toggle-password" onclick="togglePass('loginPass', this)"></i>
        </div>
        
        <button type="submit" name="login" class="btn-main">Login to Library</button>
        
        <div style="margin-top: 25px; text-align: center;">
            <p style="color: var(--gray); font-size: 0.9rem; margin-bottom: 10px;">Just looking around?</p>
            <a href="index.php" style="color: var(--ucc-green); text-decoration: none; font-weight: bold; border: 2px solid var(--ucc-green); padding: 8px 20px; border-radius: 50px; display: inline-block;">
                Browse as Guest
            </a>
        </div>
        
        <div class="links" style="text-align:center; margin-top:25px; border-top: 1px solid #eee; padding-top: 20px;">
            <span style="color: var(--gray);">New here?</span> 
            <a href="register.php" style="color:var(--ucc-orange); text-decoration:none; font-weight:bold;">Create Account</a> 
            <div style="margin-top: 10px;">
                <a href="forgot_password.php" style="color:var(--gray); text-decoration:none; font-size: 0.8rem;">Forgot Password?</a>
            </div>
        </div>
    </form>
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