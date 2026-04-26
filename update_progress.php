<?php
session_start();
include "config.php";

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id <= 0) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

if (isset($_POST['resource_id'])) {
    $res_id = (int)$_POST['resource_id'];

    // 1. Check current status
    $stmt = $conn->prepare("SELECT status FROM reading_status WHERE user_id = ? AND resource_id = ?");
    $stmt->bind_param("ii", $user_id, $res_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // 2. Cycle the status logic: not_started -> reading -> completed -> not_started
    $current = $row['status'] ?? 'not_started';
    $new_status = 'not_started';
    $percent = 0;
    $label = "Not Started";

    if ($current == 'not_started') {
        $new_status = 'reading';
        $percent = 50;
        $label = "In Progress";
    } elseif ($current == 'reading') {
        $new_status = 'completed';
        $percent = 100;
        $label = "Completed";
    }

    // 3. Update or Insert into database
    $up_stmt = $conn->prepare("INSERT INTO reading_status (user_id, resource_id, status) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE status = ?");
    $up_stmt->bind_param("iiss", $user_id, $res_id, $new_status, $new_status);
    $up_stmt->execute();

    // 4. Send the new data back to JavaScript
    echo json_encode([
        'status' => $new_status,
        'percent' => $percent,
        'label' => $label
    ]);
}
?>