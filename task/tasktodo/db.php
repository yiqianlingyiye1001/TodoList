<?php

$localhost  = "db";
$username   = "root";
$password   = "654321";
$dbname     = "php";

try {
    $db = new PDO("mysql:host=$localhost;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['error' => '数据库连接失败: ' . $e->getMessage()]));
}

// mysqli_set_charset($db, "utf8mb4");
?>
