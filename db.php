<?php
$conn = new mysqli("localhost", "root", "", "stroke_dbm(1)");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>