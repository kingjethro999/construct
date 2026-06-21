<?php
$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$name = getenv('DB_NAME') ?: "fashion_designer";
$port = getenv('DB_PORT') ?: 3306;
$ssl  = getenv('DB_SSL') === 'true';

$conn = mysqli_init();
$flags = $ssl ? MYSQLI_CLIENT_SSL : 0;
$conn->real_connect($host, $user, $pass, $name, $port, null, $flags);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>