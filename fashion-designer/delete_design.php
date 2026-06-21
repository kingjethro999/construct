<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id   = $_SESSION['user_id'];
$design_id = intval($_POST['id'] ?? 0);

if (!$design_id) {
    http_response_code(400);
    exit('Invalid design ID');
}

// Only delete if it belongs to this user
$stmt = $conn->prepare("DELETE FROM designs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $design_id, $user_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    echo "deleted";
} else {
    http_response_code(404);
    echo "Design not found";
}
?>
