    <?php
    session_start();
    include "config.php";

    // Admin-only access
    if(!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1){
        header("Location: login.php");
        exit();
    }

    // Get resource ID
    if(isset($_GET['id'])){
        $id = $_GET['id'];

        // Get file path to delete physical file
        $stmt = $conn->prepare("SELECT file_path FROM resources WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if($res){
            $file_path = $res['file_path'];
            if(file_exists($file_path)) unlink($file_path); // Delete file
        }

        // Delete record from DB
        $del_stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        $del_stmt->execute();

        header("Location: admin.php");
        exit();
    } else {
        header("Location: admin.php");
        exit();
    }