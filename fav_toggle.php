<?php
session_start();
include "config.php";

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$resource_id = $_POST['resource_id'] ?? 0;

if (!$user_id || !$resource_id) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in or missing ID']);
    exit;
}

try {
    // Check if it exists
    $stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND resource_id = ?");
    $stmt->bind_param("ii", $user_id, $resource_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Remove it
        $del = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND resource_id = ?");
        $del->bind_param("ii", $user_id, $resource_id);
        $del->execute();
        echo json_encode(['status' => 'removed']);
    } else {
        // Add it
        $ins = $conn->prepare("INSERT INTO favorites (user_id, resource_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $resource_id);
        if ($ins->execute()) {
            echo json_encode(['status' => 'added']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>