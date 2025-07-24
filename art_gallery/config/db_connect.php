<?php
$host = 'localhost';
$dbname = 'art_gallery'; // change if your DB name is different
$username = 'root';
$password = ''; // adjust if you have a DB password

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
