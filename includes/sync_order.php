<?php
// Zorg ervoor dat deze waarden overeenkomen met je databasegegevens.
include 'config.php';

// Maak een nieuwe databaseverbinding.
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer de verbinding.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query die alle bestelling_id's en besteld statussen ophaalt uit de `bestellingen` tabel.
$sql = "SELECT id, besteld FROM bestellingen";

if ($result = $conn->query($sql)) {
    // Ga door elke bestelling heen.
    while ($row = $result->fetch_assoc()) {
        $bestelling_id = $row['id'];
        $besteld_status = $row['besteld'];

        // Update de besteld_p in de bestelde_producten tabel met de besteld waarde uit de bestellingen tabel.
        $updateQuery = "UPDATE bestelde_producten SET besteld_p = ? WHERE bestelling_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ii", $besteld_status, $bestelling_id);

        // Voer de update query uit.
        $stmt->execute();
    }
    $stmt->close(); // Sluit de prepared statement.
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Sluit de verbinding.
$conn->close();
?>