<?php
session_start();
include "config.php";

// 1. Clear variables
session_unset();

// 2. Destroy session
session_destroy();

// 3. Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out | BookBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        body {
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .logout-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
    </style>
</head>
<body>

<div class="logout-card">
    <div class="logo" style="display: flex; justify-content: center; margin-bottom: 20px;">
        <img src="images/logo.png" alt="BookBridge" style="height: 60px;">
    </div>
    
    <h2 style="color: #2C3E50; margin-bottom: 10px;">Logged Out</h2>
    <p style="color: #64748b; margin-bottom: 30px;">Thank you for using BookBridge. We hope to see you again soon!</p>
    
    <a href="login.php" class="btn-main" style="display: block; text-decoration: none; margin-bottom: 15px;">Login Again</a>
    <a href="index.php" style="color: var(--ucc-orange); font-weight: 600; text-decoration: none;">Return to Home</a>
</div>

<script>
    // Prevent back-button from accessing authenticated pages from here
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
</body>
</html>