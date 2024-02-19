<?php
// Verbinding maken met de database
include '../../includes/config.php';
define('JSON_OPTIONS', JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer de databaseverbinding
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query om alle products onder elke category te halen
$sql = "SELECT c.name as category_name, p.name, p.price, p. product_id FROM categories c LEFT JOIN products p ON c.category_id = p.category_id ORDER BY c.name, p.name;";

// Voer de query uit en controleer op fouten
$result = $conn->query($sql);
if (!$result) {
    die("Error: " . $sql . "<br>" . $conn->error);
}

// Maak een structuur om de gegevens in te formaten
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[$row['category_name']][] = $row;
}

// Zet de resultaten om in JSON-formaat
$jsonResult = json_encode($categories, JSON_OPTIONS);;

// Sluit de databaseverbinding
$conn->close();

// Toon het JSON-resultaat
header('Content-Type: application/json');
echo $jsonResult;
