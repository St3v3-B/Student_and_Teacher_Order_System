<?php
include 'config.php';
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

$sql = "SELECT COUNT(*) as orderCount FROM bestellingen";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo $row['orderCount'];
} else {
    echo "ERROR";
}

$conn->close();