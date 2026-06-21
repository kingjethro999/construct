<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$email || !$password) {
    http_response_code(400);
    exit('All fields are required');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Invalid email address');
}
if (strlen($password) < 8) {
    http_response_code(400);
    exit('Password must be at least 8 characters');
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['username'] = $username;
    echo "success";
} else {
    if ($conn->errno === 1062) {
        http_response_code(409);
        echo "Email already registered";
    } else {
        http_response_code(500);
        echo "Registration failed, please try again";
    }
}
$stmt->close();
?>
