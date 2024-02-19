<?php
session_start();

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user'])) {
  $_SESSION['message'] = ['type' => 'danger', 'text' => 'Je moet eerst inloggen.'];
  header("Location: /login");
  exit();
}

// Haal de gebruikersinformatie op uit de database
include 'config.php';

// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer op fouten bij de verbinding
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Escape user input for security, voorkom SQL-injectie
$userID = $conn->real_escape_string($_SESSION['user']);

// Bereid de SQL-statement voor om SQL-injectie te voorkomen
$sql = "SELECT * FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
  // Bind de ingevoerde waarde aan de prepared statement
  $stmt->bind_param("s", $userID);

  // Voer de prepared statement uit
  $stmt->execute();

  // Sla het resultaat op
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    // Controleer of de gebruiker een admin is
    if ($user['user_type'] !== 'admin') {
      $_SESSION['message'] = ['type' => 'danger', 'text' => 'Toegang geweigerd. Alleen admins hebben toegang.'];
      header("Location: /docent");
      exit();
    }
  } else {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Fout bij het ophalen van gebruikersinformatie of gebruiker bestaat niet.'];
    header("Location: /login");
    exit();
  }
  // Sluit de statement
  $stmt->close();
} else {
  echo "Error preparing statement: " . $conn->error;
}

// Sluit de database verbinding
$conn->close();
?>