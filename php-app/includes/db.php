<?php
// Thông tin kết nối Azure Database for MySQL
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'cloudnotes_db';

$conn = new mysqli($host, $username, $password, $database);

// Kiểm tra kết nối sử dụng cấu trúc điều kiện thay vì try-catch
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>