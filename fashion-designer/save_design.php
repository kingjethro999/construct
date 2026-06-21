<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$user_id   = $_SESSION['user_id'];
$design    = $_POST['design']    ?? '';
$name      = trim($_POST['name'] ?? 'Untitled Design');
$thumbnail = $_POST['thumbnail'] ?? null;
$id        = intval($_POST['id'] ?? 0);

if (!$design) {
    http_response_code(400);
    exit('No design data');
}

if ($id) {
    // Update existing — verify ownership
    $stmt = $conn->prepare("UPDATE designs SET name=?, design_data=?, thumbnail=?, updated_at=NOW() WHERE id=? AND user_id=?");
    $stmt->bind_param("sssii", $name, $design, $thumbnail, $id, $user_id);
    $stmt->execute();
    $stmt->close();
    echo "saved";
} else {
    // Insert new
    $stmt = $conn->prepare("INSERT INTO designs (user_id, name, design_data, thumbnail) VALUES (?,?,?,?)");
    $stmt->bind_param("isss", $user_id, $name, $design, $thumbnail);
    $stmt->execute();
    $new_id = $conn->insert_id;
    $stmt->close();
    echo "saved id:" . $new_id;
}
?>
