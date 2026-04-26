<?php
session_start();
include "config.php";

// Admin-only access
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: admin.php");
    exit();
}

$id = $_GET['id'];
$error = '';

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM resources WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resource = $stmt->get_result()->fetch_assoc();

if(!$resource) { header("Location: admin.php"); exit(); }

if(isset($_POST['update_file'])){
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $video_url = trim($_POST['video_url']);
    $file_path = $resource['file_path'];

    // If a new file is uploaded
    if(!empty($_FILES['resource_file']['name'])){
        $file = $_FILES['resource_file'];
        $max_size = 15728640; // Updated to match your 15MB admin limit

        if($file['size'] > $max_size){
            $error = "Update failed: New file exceeds 15MB limit.";
        } else {
            // Delete old file if it exists
            if(!empty($file_path) && file_exists($file_path)) unlink($file_path);
            
            $filename = time().'_'.preg_replace("/[^a-zA-Z0-9.]/", "_", $file['name']);
            $file_path = 'uploads/'.$filename;
            move_uploaded_file($file['tmp_name'], $file_path);
        }
    }

    if(empty($error)){
        $update = $conn->prepare("UPDATE resources SET title=?, category=?, subject=?, description=?, file_path=?, video_url=? WHERE id=?");
        $update->bind_param("ssssssi", $title, $category, $subject, $description, $file_path, $video_url, $id);
        $update->execute();
        header("Location: admin.php?msg=updated");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Resource - BookBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="admin-page"> <nav>
    <div class="logo">
        <a href="index.php" style="text-decoration:none; display:flex; align-items:center;">
            <img src="images/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
            <span style="font-weight:bold; font-size:1.5rem; color:#2C3E50;">
                <span style="color:var(--ucc-green);">BOOK</span><span style="color:var(--ucc-orange);">Bridge</span>
            </span>
        </a>
    </div>
    <div class="nav-links">
        <a href="admin.php" class="nav-link-item">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Panel</span>
        </a>
    </div>
</nav>

<div class="content-wrapper">
    <div class="outside-text">
        <h1>Edit <span class="highlight-word">Resource</span></h1>
        <p>Update information for: <strong><?php echo htmlspecialchars($resource['title']); ?></strong></p>
    </div>

    <div class="form-container">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-section">Resource Details</div>
            
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($resource['title']); ?>" required>
            
            <div class="name-row">
                <div style="flex:1;">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php 
                        $cat_query = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                        while($cat = $cat_query->fetch_assoc()): 
                            // Match against the $resource data we fetched earlier
                            $selected = ($cat['name'] == $resource['category']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex:1;">
                    <label>Subject</label>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($resource['subject']); ?>" required>
                </div>
            </div>

            <label>Description</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($resource['description']); ?></textarea>

            <div class="form-section">Media Management</div>
            
            <label>Video URL (Optional)</label>
            <input type="url" name="video_url" value="<?php echo htmlspecialchars($resource['video_url']); ?>" placeholder="YouTube or Drive Link">

            <label>Current File</label>
            <div style="margin-bottom: 15px; font-size: 0.9rem;">
                <?php if(!empty($resource['file_path'])): ?>
                    <i class="fa-solid fa-file-pdf" style="color: var(--danger);"></i> 
                    <a href="<?php echo $resource['file_path']; ?>" target="_blank" style="color: var(--ucc-green); font-weight: 600;">View Existing File</a>
                <?php else: ?>
                    <span style="color: var(--gray);">No file uploaded</span>
                <?php endif; ?>
            </div>

            <label>Replace File (Leave empty to keep current)</label>
            <input type="file" name="resource_file" id="editFile" accept=".pdf,.doc,.docx,.txt">

            <div class="form-section">Admin Verification</div>
            <label>Confirm Changes with Password</label>
            <div class="password-wrapper">
                <input type="password" id="adminPass" placeholder="Enter password to save">
                <i class="fa-solid fa-eye toggle-password" onclick="togglePass('adminPass', this)"></i>
            </div>

            <button type="submit" name="update_file" class="btn-main" style="margin-top:20px;">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </form>
    </div>
</div>

<script>
    // File Size Guard
    document.getElementById('editFile').addEventListener('change', function() {
        if(this.files[0] && this.files[0].size > 15728640) {
            alert("New file is too large! Max 15MB.");
            this.value = "";
        }
    });

    // Universal Password Toggle Function
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