<?php
session_start();
include __DIR__ . "/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=1">

<div class="container-auth">
    <h2>Welcome, <?php echo $_SESSION['fullname']; ?> 🎉</h2>
    <p>Now you can integrate your BookBridge library here.</p>
    <a href="logout.php">Logout</a>
</div>