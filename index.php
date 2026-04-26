<?php
// --- SECURITY HEADERS: PREVENT BACK BUTTON ACCESS ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "config.php";

// Strict Session Check: Redirect to login if session is not active
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// User Session Data
$user_id = $_SESSION['user_id'] ?? 0;
$is_admin = $_SESSION['is_admin'] ?? 0;
$display_name = "student"; 

// --- DYNAMIC NAME FETCH ---
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $full = trim($row['firstname'] . " " . $row['lastname']);
        if (!empty($full)) {
            $display_name = $full;
        }
        $_SESSION['fullname'] = $display_name;
    }
    $stmt->close();
}

// --- PAGINATION & FILTER LOGIC ---
$limit = 6; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$currentCategory = $_GET['category'] ?? 'all';
$searchTerm = $_GET['search'] ?? '';
$view = $_GET['view'] ?? 'library'; 

$conditions = [];

if ($view === 'progress' && $user_id) {
    $conditions[] = "id IN (SELECT resource_id FROM reading_status WHERE user_id = $user_id AND status IN ('reading', 'completed'))";
} 
elseif ($currentCategory !== 'all') {
    if ($currentCategory === 'favorites' && $user_id) {
        $conditions[] = "id IN (SELECT resource_id FROM favorites WHERE user_id = $user_id)";
    } else {
        $conditions[] = "category = '" . $conn->real_escape_string($currentCategory) . "'";
    }
}

if (!empty($searchTerm)) {
    $s = $conn->real_escape_string($searchTerm);
    $conditions[] = "(title LIKE '%$s%' OR subject LIKE '%$s%')";
}

$whereSQL = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

$countQuery = $conn->query("SELECT COUNT(*) as total FROM resources $whereSQL");
$totalRows = $countQuery->fetch_assoc()['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT * FROM resources $whereSQL ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$resResult = $conn->query($sql);
$resources = [];
if ($resResult) {
    while($row = $resResult->fetch_assoc()) {
        $resources[] = $row;
    }
}

$favorites = [];
if($user_id){
    $f_stmt = $conn->prepare("SELECT resource_id FROM favorites WHERE user_id=?");
    $f_stmt->bind_param("i", $user_id);
    $f_stmt->execute();
    $f_res = $f_stmt->get_result();
    while($f_row = $f_res->fetch_assoc()){
        $favorites[] = (int)$f_row['resource_id'];
    }
    $f_stmt->close();
}

$progress_data = [];
if($user_id){
    $p_stmt = $conn->prepare("SELECT resource_id, status FROM reading_status WHERE user_id=?");
    $p_stmt->bind_param("i", $user_id);
    $p_stmt->execute();
    $p_res = $p_stmt->get_result();
    while($p_row = $p_res->fetch_assoc()){
        $progress_data[$p_row['resource_id']] = $p_row['status'];
    }
    $p_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookBridge | Student Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        /* FULL NAV & CATEGORY DESIGN RESTORED */
        .nav-link-item {
            text-decoration: none;
            color: #2C3E50;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
        }

        .nav-link-item i { transition: transform 0.3s ease, color 0.3s ease; }

        .nav-link-item:hover {
            background-color: #ffffff;
            color: var(--ucc-green);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }

        .nav-link-item:hover i { transform: rotate(10deg); color: var(--ucc-orange); }
        .nav-link-item b { color: var(--ucc-green); transition: color 0.3s ease; }
        .nav-link-item:hover b { color: var(--ucc-orange); }

        .progress-link:hover {
            color: var(--ucc-orange) !important;
            background-color: #fff9f0;
        }
        .progress-link.active-view {
            background: #fff4e6;
            color: var(--ucc-orange);
            border-color: #ffd8a8;
        }

        /* CATEGORY FILTERS */
        .filter-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .filter-btn {
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 50px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .filter-btn:hover { background: #e2e8f0; transform: translateY(-2px); }

        .filter-btn.active {
            background: var(--ucc-green);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 104, 56, 0.2);
        }

        .filter-btn.fav.active {
            background: var(--ucc-orange);
            color: white;
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.2);
        }

        .dropdown-content { 
            display: none; 
            position: absolute; 
            right: 0; 
            top: 45px; 
            background: white; 
            min-width: 160px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            border-radius: 8px; 
            z-index: 10002; 
            border: 1px solid #ddd;
        }
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 0.9rem;
        }
        .dropdown-content a:hover { background: #f1f5f9; color: #2ecc71; }
        .show-menu { display: block !important; }
        .gear-active { transform: rotate(90deg); color: var(--ucc-orange) !important; }

        /* PROGRESS TRACKING */
        .progress-section {
            margin: 15px 0;
            padding: 12px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .progress-bar-container {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: var(--ucc-green);
            width: 0%;
            transition: width 0.5s ease;
        }
        .status-badge {
            cursor: pointer;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            transition: 0.3s;
            border: 1px solid transparent;
        }
        .status-not_started { background: #f1f5f9; color: #64748b; }
        .status-reading { background: #fff4e6; color: #e67e22; border-color: #ffd8a8; }
        .status-completed { background: #ecfdf5; color: #10b981; border-color: #a7f3d0; }

        .resource-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card { position: relative; background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; transition: 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .bookmark-btn {
            position: absolute; top: 15px; right: 15px;
            background: #f1f5f9; border: none; border-radius: 50%;
            width: 35px; height: 35px; cursor: pointer !important;
            font-size: 1.2rem; color: #cbd5e1; z-index: 50;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease;
        }
        .bookmark-btn.active { color: var(--ucc-orange); background: #fff4e6; }
        .pagination { display: flex; justify-content: center; gap: 10px; margin: 40px 0; }
        .page-num { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; background: #f1f5f9; color: #475569; }
        .page-num.active { background: var(--ucc-green); color: white; }
    </style>
</head>
<body>

<nav>
    <div class="logo">
        <a href="index.php" style="text-decoration:none; display:flex; align-items:center;">
            <img src="images/logo.png" alt="Logo" style="height:40px; margin-right:10px;">
            <span style="font-weight:bold; font-size:1.5rem; color:#2C3E50;">
                <span style="color:var(--ucc-green);">BOOK</span><span style="color:var(--ucc-orange);">Bridge</span>
            </span>
        </a>
    </div>

    <div class="nav-links" style="position: relative; z-index: 10000; display: flex; align-items: center; gap: 10px;">
        <?php if($user_id): ?>
            <a href="acc_settings.php" class="nav-link-item">
                <i class="fa-solid fa-circle-user"></i>
                <span>Greetings, <b><?php echo htmlspecialchars($display_name); ?></b></span>
            </a>

            <a href="index.php?view=progress" class="nav-link-item progress-link <?php echo ($view === 'progress') ? 'active-view' : ''; ?>">
                <i class="fa-solid fa-list-check"></i>
                <span>My Progress</span>
            </a>
            
            <?php if($is_admin == 1): ?>
                <a href="admin.php" class="nav-link-item admin-link">
                    <i class="fa-solid fa-shield-halved"></i>
                    Admin Panel
                </a>
            <?php endif; ?>

            <div class="dropdown" style="position: relative; display: inline-block;">
                <i class="fa-solid fa-gear gear-btn" 
                   id="gearIcon" 
                   style="cursor: pointer; position: relative; z-index: 10001; padding: 10px; transition: 0.3s;" 
                   onclick="toggleSettings(event)"></i>
                
                <div class="dropdown-content" id="myDropdown">
                    <a href="acc_settings.php"><i class="fa-solid fa-user-gear"></i> Settings</a>
                    <hr style="border:0; border-top:1px solid #eee; margin:0;">
                    <a href="logout.php" style="color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="login.php" class="nav-link-item">Login</a>
            <a href="register.php" class="btn-main" style="width:auto; padding:8px 20px;">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero" style="background: linear-gradient(135deg, var(--ucc-green-dark), #002d18); padding: 80px 20px; text-align: center; color: white;">
    <h1 style="font-size: 3rem; margin-bottom: 20px;">
        <?php echo ($view === 'progress') ? 'My <span style="color:var(--ucc-orange);">Learning</span> Progress' : 'UCC <span style="color:var(--ucc-orange);">Resource</span> Library'; ?>
    </h1>
    <p style="margin-bottom:30px; opacity:0.9;">
        <?php echo ($view === 'progress') ? 'Track your active learning materials and goals' : 'Exclusive for BSCS Students'; ?>
    </p>
    <div class="search-container" style="max-width: 600px; margin: 0 auto;">
        <form action="index.php" method="GET">
            <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($currentCategory); ?>">
            <input type="text" name="search" style="width:100%; padding:18px 30px; border-radius:50px; border:none; font-size:1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);" placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        </form>
    </div>
</div>

<div class="filter-group">
    <a href="index.php?category=all&search=<?php echo urlencode($searchTerm); ?>" 
       class="filter-btn <?php echo ($currentCategory == 'all' && $view != 'progress') ? 'active' : ''; ?>">
       All Resources
    </a>

    <?php 
    $cat_query = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    while($cat = $cat_query->fetch_assoc()): ?>
        <a href="index.php?category=<?php echo urlencode($cat['name']); ?>&search=<?php echo urlencode($searchTerm); ?>" 
           class="filter-btn <?php echo $currentCategory == $cat['name'] ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($cat['name']); ?>
        </a>
    <?php endwhile; ?>

    <?php if($user_id): ?>
        <a href="index.php?category=favorites&search=<?php echo urlencode($searchTerm); ?>" 
           class="filter-btn fav <?php echo $currentCategory == 'favorites' ? 'active' : ''; ?>">
            ⭐ Favorites
        </a>
    <?php endif; ?>
</div>

<div class="resource-grid">
    <?php if (empty($resources)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: #64748b;">
            <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 10px;"></i>
            <p>No resources found here yet.</p>
        </div>
    <?php else: ?>
        <?php foreach($resources as $item): ?>
            <div class="card">
                <button class="bookmark-btn <?php echo in_array($item['id'], $favorites) ? 'active' : ''; ?>" 
                        onclick="toggleFav(<?php echo $item['id']; ?>, this)">
                    <i class="fa-solid fa-star"></i>
                </button>
                
                <span style="background: var(--ucc-green-dark); color: white; padding: 4px 10px; border-radius: 5px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">
                    <?php echo htmlspecialchars($item['category']); ?>
                </span>
                
                <h3 style="margin: 15px 0 5px 0; color: #1e293b;"><?php echo htmlspecialchars($item['title']); ?></h3>
                <p style="color: var(--ucc-orange); font-weight: bold; font-size: 0.85rem; margin-bottom: 10px;">Subject: <?php echo htmlspecialchars($item['subject']); ?></p>
                
                <?php if($user_id): ?>
                <div class="progress-section">
                    <?php 
                        $status = $progress_data[$item['id']] ?? 'not_started';
                        $percent = 0; $label = "Not Started";
                        if($status == 'reading') { $percent = 50; $label = "In Progress"; }
                        if($status == 'completed') { $percent = 100; $label = "Completed"; }
                    ?>
                    <div class="progress-header">
                        <span class="status-badge status-<?php echo $status; ?>" onclick="cycleStatus(<?php echo $item['id']; ?>, this)">
                            <?php echo $label; ?>
                        </span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card-actions" style="margin-top: 15px;">
                    <?php if($user_id > 0): ?>
                        <?php if (!empty($item['file_path'])): ?>
                            <a href="<?php echo htmlspecialchars($item['file_path']); ?>" 
                               class="btn-main" target="_blank" 
                               style="display: block; text-align: center; background: var(--ucc-green); color: white; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-bottom: 10px;">
                               View PDF
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 10px; background: #f8fafc; border-radius: 10px;">
                            <a href="login.php" style="color: var(--ucc-green); font-weight: bold; text-decoration: none;">Login to View</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&view=<?php echo $view; ?>&category=<?php echo urlencode($currentCategory); ?>&search=<?php echo urlencode($searchTerm); ?>" 
               class="page-num <?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<script>
function cycleStatus(resourceId, badge) {
    const card = badge.closest('.card');
    const bar = card.querySelector('.progress-fill');
    
    fetch("update_progress.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `resource_id=${resourceId}`
    })
    .then(res => res.json())
    .then(data => {
        badge.className = `status-badge status-${data.status}`;
        badge.innerText = data.label;
        bar.style.width = data.percent + '%';
        
        if(window.location.search.includes('view=progress') && data.status === 'not_started') {
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);
        }
    });
}

function toggleSettings(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById("myDropdown").classList.toggle("show-menu");
    document.getElementById("gearIcon").classList.toggle("gear-active");
}

window.onclick = function(e) {
    if (!e.target.matches('.gear-btn')) {
        var dropdowns = document.getElementsByClassName("dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show-menu')) {
                dropdowns[i].classList.remove('show-menu');
                document.getElementById("gearIcon").classList.remove('gear-active');
            }
        }
    }
}

function toggleFav(id, btn) {
    <?php if(!$user_id): ?>
        if (confirm("Login to save favorites?")) { window.location.href = "login.php"; }
        return;
    <?php endif; ?>

    const data = new URLSearchParams();
    data.append('resource_id', id);

    fetch("fav_toggle.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: data
    })
    .then(res => res.json())
    .then(response => {
        if(response.status === "added") btn.classList.add("active");
        else btn.classList.remove("active");
    });
}
</script>
</body>
</html>