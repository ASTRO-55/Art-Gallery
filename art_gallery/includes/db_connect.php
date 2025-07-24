<?php
$servername = "localhost";
$username = "root";
$password = ""; // or your DB password
$dbname = "art_gallery"; // your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
