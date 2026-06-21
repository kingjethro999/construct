<?php
$host = "mysql-95ad972-thisiskingjethro-3be3.b.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS__QSThIrRML7Pl85aWhR";
$name = "defaultdb";
$port = 21723;
$ssl  = true;

$conn = mysqli_init();
$flags = $ssl ? MYSQLI_CLIENT_SSL : 0;
$conn->real_connect($host, $user, $pass, $name, $port, null, $flags);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>