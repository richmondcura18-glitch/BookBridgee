<?php
session_start();
include "config.php";

// Admin-only access
if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if(isset($_GET['msg']) && $_GET['msg'] == 'uploaded'){
    $success = "Resource added successfully!";
}

// --- Handle Adding New Category ---
if(isset($_POST['add_new_cat'])){
    $new_cat = trim($_POST['new_category_name']);
    if(!empty($new_cat)){
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $new_cat);
        if($stmt->execute()){
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo "success"; exit;
            }
            header("Location: admin.php");
            exit();
        }
    }
}

// --- Handle Deleting Category ---
if(isset($_POST['delete_cat'])){
    $cat_id = $_POST['cat_id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $cat_id);
    if($stmt->execute()){
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo "success"; exit;
        }
        header("Location: admin.php");
        exit();
    }
}

// --- Handle resource upload ---
if(isset($_POST['upload_file'])){
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $video_url = trim($_POST['video_url']);
    $file = $_FILES['resource_file'];

    $filepath = ''; 
    $upload_ok = true;

    if(empty($video_url) && $file['error'] === UPLOAD_ERR_NO_FILE){
        $error = "Please provide either a PDF file OR a Video Link.";
        $upload_ok = false;
    }

    if($upload_ok && $file['error'] === UPLOAD_ERR_OK) {
        $max_size = 15728640; 
        $allowed_ext = ['pdf', 'doc', 'docx', 'txt'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if($file['size'] > $max_size){
            $error = "File is too large! Maximum 15MB.";
            $upload_ok = false;
        } elseif(!in_array($file_ext, $allowed_ext)){
            $error = "Invalid file type.";
            $upload_ok = false;
        } else {
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            $filename = time().'_'.preg_replace("/[^a-zA-Z0-9.]/", "_", $file['name']);
            $filepath = 'uploads/'.$filename;
            if(!move_uploaded_file($file['tmp_name'], $filepath)){
                $error = "Failed to save file.";
                $upload_ok = false;
            }
        }
    }

    if($upload_ok) {
        $stmt = $conn->prepare("INSERT INTO resources (title, category, subject, description, file_path, video_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $category, $subject, $description, $filepath, $video_url);
        if($stmt->execute()){
            $stmt->close();
            header("Location: admin.php?msg=uploaded");
            exit();
        } else {
            $error = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

$res_result = $conn->query("SELECT * FROM resources ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">      
    <title>Admin Panel - BookBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="admin-page">

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
        <a href="index.php" class="nav-link-item">
            <i class="fa-solid fa-house"></i>
            <span>Library</span>
        </a>
        <a href="logout.php" class="nav-link-item" style="color:var(--danger);">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>

<div class="content-wrapper">
    <div class="outside-text">
        <h1>Admin <span class="highlight-word">Panel</span></h1>
        <p>Manage digital resources and student categories</p>
    </div>

    <div class="admin-card">
        <?php if($success) echo "<div class='success-msg'>$success</div>"; ?>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>

        <div class="form-section">Upload Resource</div>
        
        <form method="POST" enctype="multipart/form-data" id="mainUploadForm">
            <label>Resource Title</label>
            <input type="text" name="title" placeholder="Enter title" required>
            
            <div class="name-row">
                <div style="flex: 1;">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php 
                        $cat_dropdown = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                        while($c = $cat_dropdown->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($c['name']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="e.g. IT-101" required>
                </div>
            </div>

            <label>Description</label>
            <textarea name="description" placeholder="Brief description of the material..." rows="3"></textarea>
            
            <div class="name-row">
                <div style="flex: 1;">
                    <label><i class="fa-solid fa-file-arrow-up"></i> PDF/Doc File</label>
                    <input type="file" name="resource_file" id="fileInput" accept=".pdf,.doc,.docx,.txt">
                </div>
                <div style="flex: 1;">
                    <label><i class="fa-solid fa-link"></i> Video Link</label>
                    <input type="url" name="video_url" id="video_url" placeholder="YouTube/Drive URL">
                </div>
            </div>
            
            <button type="submit" name="upload_file" class="btn-main">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Resource
            </button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <div class="category-manager" style="padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
            <label style="margin-bottom: 10px; display: block;">Manage Categories</label>
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <input type="text" id="new_cat_input" placeholder="New Category Name..." style="margin:0;">
                <button type="button" onclick="addNewCategory()" class="btn-main" style="width: auto; padding: 0 20px;">Add</button>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php 
                $cat_manager = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                while($cm = $cat_manager->fetch_assoc()): ?>
                    <span style="background: white; padding: 5px 12px; border-radius: 50px; border: 1px solid #cbd5e1; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600;">
                        <?php echo htmlspecialchars($cm['name']); ?>
                        <i class="fa-solid fa-circle-xmark" onclick="deleteCategory(<?php echo $cm['id']; ?>)" style="color: var(--danger); cursor: pointer;"></i>
                    </span>
                <?php endwhile; ?>
            </div>
        </div>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <div class="form-section">Resource Inventory</div>
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res_result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: bold;"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><span class="tag"><?php echo $row['category']; ?></span></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td style="text-align: center;">
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-edit"><i class="fa-solid fa-pen-to-square"></i></a>
                            <span style="margin: 0 8px; color: #ddd;">|</span>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="action-delete" onclick="return confirm('Delete this?')"><i class="fa-solid fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // File Size Guard
    document.getElementById('fileInput').addEventListener('change', function() {
        if(this.files[0] && this.files[0].size > 15728640) {
            alert("File is too large (Max 15MB).");
            this.value = "";
        }
    });

    // Form Guard
    document.getElementById('mainUploadForm').onsubmit = function(e) {
        const file = document.getElementById('fileInput').files.length;
        const video = document.getElementById('video_url').value.trim();
        if (file === 0 && video === "") {
            e.preventDefault();
            alert("Please upload a file or provide a video link.");
        }
    };

    // Category AJAX
    function addNewCategory() {
        const catName = document.getElementById('new_cat_input').value.trim();
        if (catName === "") { alert("Enter a name!"); return; }
        const formData = new FormData();
        formData.append('add_new_cat', true);
        formData.append('new_category_name', catName);
        fetch('admin.php', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(() => window.location.reload());
    }

    function deleteCategory(id) {
        if(!confirm("Remove this category?")) return;
        const formData = new FormData();
        formData.append('delete_cat', true);
        formData.append('cat_id', id);
        fetch('admin.php', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
        .then(() => window.location.reload());
    }

    // Show/Hide Password Universal Logic
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