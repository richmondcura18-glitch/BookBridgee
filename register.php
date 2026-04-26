<?php
session_start();
include "config.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? ''); // Added confirm check
    $birthday = trim($_POST['birthday'] ?? '');
    $secret_question = trim($_POST['secret_question'] ?? '');
    $secret_answer = trim($_POST['secret_answer'] ?? '');

    if ($firstname && $lastname && $email && $password && $confirm && $birthday && $secret_question && $secret_answer) {
        if (!str_ends_with($email, '@gmail.com')) {
            $message = "Only @gmail.com addresses are allowed!";
        } else if ($password !== $confirm) {
            $message = "Passwords do not match!";
        } else if (!preg_match('@[A-Z]@', $password) || !preg_match('@[0-9]@', $password) || !preg_match('@[^\w]@', $password) || strlen($password) < 8) {
            $message = "Password is too weak!";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if($stmt->num_rows > 0){
                $message = "Email already registered!";
            } else {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $answer_hashed = password_hash($secret_answer, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, birthday, secret_question, secret_answer, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
                $stmt->bind_param("sssssss", $firstname, $lastname, $email, $password_hashed, $birthday, $secret_question, $answer_hashed);
                
                if($stmt->execute()){
                    header("Location: login.php");
                    exit();
                } else {
                    $message = "Registration failed.";
                }
            }
        }
    } else {
        $message = "Please fill all required fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - BookBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        /* Visual feedback for password matching */
        .match-mismatch { border-color: #ef4444 !important; }
        .match-success { border-color: #2ecc71 !important; }
    </style>
</head>
<body class="register-bg">

<div class="form-container" style="max-width: 550px;">
    <div style="text-align: center; margin-bottom: 25px;">
        <h2>Join <span class="highlight-word">Book</span>Bridge</h2>
        <p style="color: var(--gray);">Create your student account</p>
    </div>

    <?php if($message): ?>
        <div class="error"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="name-row">
            <div>
                <label>First Name</label>
                <input type="text" name="firstname" placeholder="Juan" required>
            </div>
            <div>
                <label>Last Name</label>
                <input type="text" name="lastname" placeholder="Dela Cruz" required>
            </div>
        </div>
        
        <label>Email Address</label>
        <input type="email" name="email" placeholder="student@gmail.com" required>
        
        <label>Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="regPass" placeholder="Min. 8 chars, 1 Capital, 1 Symbol" required>
            <i class="fa-solid fa-eye toggle-password" onclick="togglePass('regPass', this)"></i>
        </div>

        <label>Confirm Password</label>
        <div class="password-wrapper">
            <input type="password" name="confirm_password" id="confPass" placeholder="Re-type password" required>
            <i class="fa-solid fa-eye toggle-password" onclick="togglePass('confPass', this)"></i>
        </div>
        
        <label>Birthday</label>
        <input type="date" name="birthday" required>

        <label>Security Question</label>
        <select name="secret_question" required>
            <option value="" disabled selected>Select a Question</option>
            <option value="What is your first pet's name?">What is your first pet's name?</option>
            <option value="What was the name of your first school?">What was the name of your first school?</option>
            <option value="What is your favorite color?">What is your favorite color?</option>
            <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
            <option value="What is your favorite game?">What is your favorite game?</option>
            <option value="What is your hobby?">What is your hobby?</option>
        </select>
        <input type="text" name="secret_answer" placeholder="Your Secret Answer" required style="margin-top:10px;">

        <button type="submit" name="register" class="btn-main">Create Account</button>
        
        <div class="links" style="text-align:center; margin-top:15px;">
            <p>Already have an account? <a href="login.php" style="color:var(--ucc-green); font-weight:bold; text-decoration:none;">Login here</a></p>
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

// Real-time Confirm Password Validation
const regPass = document.getElementById('regPass');
const confPass = document.getElementById('confPass');

confPass.addEventListener('keyup', function() {
    if (confPass.value === "") {
        confPass.classList.remove('match-success', 'match-mismatch');
    } else if (regPass.value === confPass.value) {
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