<?php
session_start();

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user'])) {
  $_SESSION['message'] = ['type' => 'danger', 'text' => 'Je moet eerst inloggen.'];
  header("Location: /login");
  exit();
}

// Haal de database configuratie op
require 'config.php';  // Gebruik require zodat het script niet doorgaat zonder de configuratie

// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer of er een fout is met de verbinding
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Gebruiker invoer altijd escapen om SQL injctie te voorkomen
$userID = $conn->real_escape_string($_SESSION['user']);

// Het is beter om een prepared statement te gebruiken
$sql = "SELECT * FROM users WHERE id = ?";

// Prepare statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
  die('Prepare failed: ' . htmlspecialchars($conn->error));
}

// Bind parameters
$stmt->bind_param('i', $userID); // 'i' want userID zou een integer moeten zijn

// Voer de query uit
$stmt->execute();

// Verkrijg het resultaat
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  // Haal associatief de gebruikersinformatie op
  $user = $result->fetch_assoc();
} else {
  $_SESSION['message'] = ['type' => 'danger', 'text' => 'Fout bij het ophalen van gebruikersinformatie.'];
  header("Location: /login");
  exit();
}
// Sluit statement en verbinding
$stmt->close();
$conn->close();
?>