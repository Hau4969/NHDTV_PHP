<?php
$servername = "sql207.infinityfree.com"; // host
$username = "if0_40319987"; // user
$password =  // thay bằng mật khẩu tài khoản InfinityFree
$dbname = "if0_40319987_ql_cinema"; // tên database

// Kết nối MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
