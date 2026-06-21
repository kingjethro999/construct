<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id   = $_SESSION['user_id'];
$source_id = intval($_POST['source_id'] ?? 0);
$name      = trim($_POST['name'] ?? 'Copy of Design');

if (!$source_id) {
    http_response_code(400);
    exit('Invalid source ID');
}

// Fetch the original — must belong to this user
$stmt = $conn->prepare("SELECT design_data, thumbnail FROM designs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $source_id, $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    exit('Design not found');
}

$stmt = $conn->prepare("INSERT INTO designs (user_id, name, design_data, thumbnail) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $name, $row['design_data'], $row['thumbnail']);
$stmt->execute();
$new_id = $conn->insert_id;
$stmt->close();

echo "duplicated id:" . $new_id;
?>
