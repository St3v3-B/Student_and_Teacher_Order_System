<?php
session_start(); // Zorg dat sessie altijd start voordat je toegang krijgt tot $_SESSION variabelen.

// HTTPS redirect als we niet over HTTPS binnenkomen
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
  if (!headers_sent()) {
    header("Status: 301 Moved Permanently");
    header(
      sprintf(
        'Location: https://%s%s',
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI']
      )
    );
    exit();
  }
}
if (!isset($_SESSION['last_checked'])) {
    $_SESSION['last_checked'] = date('Y-m-d H:i:s');
  }
// Maak verbinding met de database en voer de juiste inloggegevens in
include 'config.php';

// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer de databaseverbinding
if ($conn->connect_error) {
  die("Verbinding mislukt: " . $conn->connect_error);
}

// Haal de gebruikersnaam uit de database
$gebruiker_id = $conn->real_escape_string($_SESSION['user']); // Sanitize de gebruiker_id
$sql = "SELECT username FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param("i", $gebruiker_id); // i staat voor integer
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $gebruikersnaam = $row['username'];
  } else {
    $gebruikersnaam = "Gebruiker niet gevonden";
  }
  $stmt->close();
} else {
  $gebruikersnaam = "Error in query";
}
$conn->close();

?>
<ul class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand  btn btn-outline-default " href="/docent/">Docent</a>
  <a class="btn btn-outline-default " href="/admin">Admin</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
    aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
    <ul class="navbar-nav">
    <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-info shadow " href="/docent/all">alles</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-primary shadow " href="/docent/bestellen">Bestellen</a>
      </li>
      <li class="nav-item ml-2">
        <a class="nav-link text-light btn btn-outline-danger shadow" href="/logout">
          <?php echo htmlspecialchars($gebruikersnaam); // Print de gebruikersnaam veilig ?>
        </a>
      </li>
    </ul>
  </div>
</ul>
<br>
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