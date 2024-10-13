<?php
$conn = new mysqli('localhost', 'root', '', 'personalproject');

if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
} else {
    echo "Connected successfully!";
}
?>
