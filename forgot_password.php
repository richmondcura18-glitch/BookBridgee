<?php
session_start();
include "config.php";

$message = '';
$step = 1; 
$question = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // STEP 1: Verify Email
    if (isset($_POST['verify_email'])) {
        $email = trim($_POST['email']);
        $stmt = $conn->prepare("SELECT id, secret_question FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $_SESSION['reset_user_id'] = $user['id']; 
            $question = $user['secret_question'];
            $step = 2; 
        } else {
            $message = "Account not found with that email.";
        }
    } 
    // STEP 2: Verify Answer and Redirect to Reset
    elseif (isset($_POST['verify_answer'])) {
        $answer = trim($_POST['secret_answer']);
        $user_id = $_SESSION['reset_user_id'] ?? null;

        if ($user_id) {
            $stmt = $conn->prepare("SELECT secret_answer, secret_question FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            // CRITICAL: Check the answer against the hash
            if (password_verify($answer, $user['secret_answer'])) {
                // REDIRECT TO RESET PAGE UPON SUCCESS
                header("Location: reset_password.php");
                exit(); 
            } else {
                $message = "Incorrect answer. Please try again.";
                $step = 2;
                $question = $user['secret_question'];
            }
        } else {
            // If the session was lost, we go back to step 1
            $message = "Session timeout. Please enter your email again.";
            $step = 1;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Account - BookBridge</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('images/Ucc.png') no-repeat center center fixed !important;
            background-size: cover !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .highlight-word {
            background: linear-gradient(to right, #2ecc71, #27ae60);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            text-transform: uppercase;
            filter: drop-shadow(2px 2px 2px rgba(0,0,0,0.5));
            display: inline-block;
            font-size: 2.5rem;
        }
        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
            text-transform: uppercase;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div style="text-align: center; margin-bottom: 20px;">
        <h1>
            <span class="highlight-word">Verify</span> 
            <span class="highlight-word">Account</span>
        </h1>
    </div>

    <div class="form-container">
        <?php if($message) echo "<div style='color:#ef4444; text-align:center; margin-bottom:15px; font-weight:bold;'>$message</div>"; ?>

        <?php if ($step == 1): ?>
            <p style="text-align:center; color:#64748b; margin-bottom:20px;">Enter your email to retrieve your question.</p>
            <form method="POST">
                <input type="email" name="email" placeholder="Email Address" required style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">
                <button type="submit" name="verify_email" class="btn-verify">Find Account</button>
            </form>
        <?php else: ?>
            <p style="text-align:center; color:#64748b; margin-bottom:20px;">Please answer your security question.</p>
            <form method="POST">
                <div style="background: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 15px; text-align: center; border: 1px solid #e2e8f0;">
                    <strong style="color: #27ae60;">Question:</strong><br>
                    <?php echo htmlspecialchars($question); ?>
                </div>
                <input type="text" name="secret_answer" placeholder="Your Secret Answer" required autofocus style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;">
                <button type="submit" name="verify_answer" class="btn-verify">Verify Answer</button>
            </form>
        <?php endif; ?>

        <div class="links">
            <a href="login.php" style="text-decoration:none; color:#2ecc71; font-weight:bold; display:block; text-align:center; margin-top:20px;">← Back to Login</a>
        </div>
    </div>
</body>
</html>`