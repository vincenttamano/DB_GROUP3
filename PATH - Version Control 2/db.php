<?php
// FILE: db.php
$conn = new mysqli("localhost", "root", "", "PATHdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>