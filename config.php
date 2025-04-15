<?php
// config.php này để kết nối đến cơ sở dữ liệu
$host = 'localhost';
$user = 'root';        
$pass = '';
$dbname = 'cellphoneshop';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
