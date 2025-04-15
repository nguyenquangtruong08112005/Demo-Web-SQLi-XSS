<?php
// config.php này để kết nối đến cơ sở dữ liệu
$host = 'localhost';
$user = 'root';        
$pass = '';
$dbname = 'cellphoneshop';

error_reporting(0);
ini_set('display_errors', 0);

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed. Please try again later.");
}
?>
