<?php
session_start();
include "config.php";

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? 0;
$is_admin = $_SESSION['is_admin'] ?? 0;

// Load favorites for this user
$favorites = [];
if($user_id){
    $stmt = $conn->prepare("SELECT resource_id FROM favorites WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $favorites[] = $row['resource_id'];
    }
}

// Load all resources
$resources_stmt = $conn->prepare("SELECT * FROM resources ORDER BY created_at DESC");
$resources_stmt->execute();
$resources_result = $resources_stmt->get_result();
$resources = [];
while($row = $resources_result->fetch_assoc()){
    $resources[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BookBridge | Student Library</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #2C3E50;
    --accent: #4CAF50;
    --accent-hover: #45a049;
    --bg: #F8FAFC;
    --white: #ffffff;
    --text: #334155;
    --gray: #64748b;
    --card-bg: #ffffff;
    --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    --highlight: #fef08a;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: var(--bg);
    margin: 0;
    color: var(--text);
    line-height: 1.5;
    transition: background 0.3s, color 0.3s;
}

nav {
    background: var(--white);
    padding: 1rem 5%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow);
    position: sticky;
    top: 0;
    z-index: 100;
}

.logo { font-weight: 700; font-size: 1.5rem; color: var(--primary); }
nav a { margin-left: 15px; color: var(--primary); text-decoration: none; font-weight:600; }
nav a:hover { color: var(--accent); }

.hero { text-align:center; padding:50px 20px; background: linear-gradient(135deg,#2C3E50,#4ca1af); color:#fff; }
.search-container { max-width:500px; margin:20px auto; }
#searchInput { width:100%; padding:15px 20px; border-radius:30px; border:none; font-size:1rem; }

.filter-group { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin:30px 0; }
.filter-btn { padding:10px 22px; border:none; border-radius:25px; background:#fff; color:var(--gray); cursor:pointer; font-weight:600; }
.filter-btn.active { background: var(--accent); color:#fff; }

.container { padding:0 5% 50px; min-height:400px; }
.grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:25px; }

.card { background: var(--card-bg); padding:24px; border-radius:16px; position:relative; box-shadow:var(--shadow); display:flex; flex-direction:column; border:1px solid rgba(0,0,0,0.05); }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.2); }
.card h3 { margin:0 0 8px 0; font-size:1.25rem; }
.card p { color: var(--gray); font-size:0.9rem; flex-grow:1; }

.card .tag { display:inline-block; background: var(--accent); padding:4px 12px; border-radius:12px; font-size:0.75rem; font-weight:700; text-transform:uppercase; color:white; margin-bottom:12px; align-self:flex-start; }

.bookmark-btn { position:absolute; right:20px; top:20px; border:none; background:rgba(0,0,0,0.05); width:35px; height:35px; border-radius:50%; cursor:pointer; font-size:1.2rem; color:#cbd5e1; transition:0.2s; }
.bookmark-btn.active { color:#f59e0b; background:#fffbeb; }

.btn-main { background: var(--accent); color:white !important; padding:12px; text-align:center; border-radius:10px; text-decoration:none; font-weight:600; margin-top:15px; transition:0.3s; display:inline-block; }
.btn-main:hover { opacity:0.9; transform: scale(1.02); }

mark { background: var(--highlight); color:black; border-radius:2px; }

#noResults { text-align:center; padding:40px; display:none; color:var(--gray); }
</style>
</head>
<body>

<nav>
    <div class="logo">📚 BookBridge</div>
    <div>
        <?php if($user_id): ?>
            Hello, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?> |
            <a href="logout.php">Logout</a>
            <?php if($is_admin): ?>
                | <a href="admin.php" style="color:red;">Admin Panel</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php">Login</a> | <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <h1>Student Resource Library</h1>
    <div class="search-container">
        <input type="text" id="searchInput" placeholder="Search by title or subject..." onkeyup="runFilters()">
    </div>
</div>

<div class="filter-group">
    <button class="filter-btn active" onclick="filterCategory('all', this)">All Resources</button>
    <button class="filter-btn" onclick="filterCategory('eBook', this)">eBooks</button>
    <button class="filter-btn" onclick="filterCategory('Reviewer', this)">Reviewers</button>
    <button class="filter-btn" onclick="filterCategory('Notes', this)">Notes</button>
    <?php if($user_id): ?>
        <button class="filter-btn" onclick="filterCategory('favorites', this)">⭐ Favorites</button>
    <?php endif; ?>
</div>

<div class="container">
    <div id="noResults">
        <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" width="80" style="opacity:0.5;"><br>
        <p>No resources found. Try a different search term!</p>
    </div>
    <div class="grid" id="resourceGrid"></div>
</div>

<script>
let resources = <?php echo json_encode($resources); ?>;
let favorites = <?php echo json_encode($favorites); ?>;
let currentCategory = 'all';

function renderCards(data){
    const grid = document.getElementById("resourceGrid");
    const noResults = document.getElementById("noResults");
    const searchTerm = document.getElementById("searchInput").value.trim();
    
    if(data.length === 0){
        grid.innerHTML = "";
        noResults.style.display = "block";
        return;
    }
    
    noResults.style.display = "none";
    grid.innerHTML = data.map(item=>{
        const isFav = favorites.includes(item.id);
        let displayTitle = item.title;
        if(searchTerm){
            const regex = new RegExp(`(${searchTerm})`, "gi");
            displayTitle = item.title.replace(regex, "<mark>$1</mark>");
        }
        return `
        <div class="card">
            <button class="bookmark-btn ${isFav?'active':''}" onclick="toggleFav(${item.id})">★</button>
            <div class="tag">${item.category} • ${item.subject}</div>
            <h3>${displayTitle}</h3>
            <p>${item.description}</p>
            <a href="${item.file_path}" class="btn-main" target="_blank">${item.category==='Notes'?'View Notes':'Download PDF'}</a>
        </div>
        `;
    }).join('');
}

function runFilters(){
    const searchTerm = document.getElementById("searchInput").value.toLowerCase();
    const filtered = resources.filter(item=>{
        const matchesSearch = item.title.toLowerCase().includes(searchTerm) || item.subject.toLowerCase().includes(searchTerm);
        let matchesCategory = false;
        if(currentCategory==='all') matchesCategory = true;
        else if(currentCategory==='favorites') matchesCategory = favorites.includes(item.id);
        else matchesCategory = item.category===currentCategory;
        return matchesSearch && matchesCategory;
    });
    renderCards(filtered);
}

function filterCategory(cat, btn){
    currentCategory = cat;
    document.querySelectorAll(".filter-btn").forEach(b=>b.classList.remove("active"));
    btn.classList.add("active");
    runFilters();
}

function toggleFav(id){
    fetch("toggle_fav.php", {
        method:"POST",
        headers:{ "Content-Type":"application/x-www-form-urlencoded" },
        body:"resource_id="+id
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.status==="added"){
            if(!favorites.includes(id)) favorites.push(id);
        } else if(data.status==="removed"){
            favorites = favorites.filter(f=>f!==id);
        }
        runFilters();
    })
    .catch(err=>console.error(err));
}

document.addEventListener("DOMContentLoaded",()=>{ renderCards(resources); });
</script>

</body>
</html>