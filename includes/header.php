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
<script>
function checkForNewOrders() {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      var newOrderCount = parseInt(this.responseText, 10);
      var currentOrderCount = parseInt(sessionStorage.getItem('currentOrderCount'), 10);

      // Als de currentOrderCount nog niet is ingesteld of als het aantal bestellingen is toegenomen
      if (!currentOrderCount) {
        sessionStorage.setItem('currentOrderCount', newOrderCount);
      } else if (currentOrderCount < newOrderCount) {
        // Er zijn nieuwe bestellingen
        alert('Er zijn nieuwe bestellingen!');
        sessionStorage.setItem('currentOrderCount', newOrderCount); // Update de opgeslagen teller
      }

      // Optioneel: Werk een element bij met het huidige aantal bestellingen
      //document.getElementById('newOrderCount').textContent = newOrderCount;
    }
  };
  xmlhttp.open("GET", "/includes/check_updates.php", true);
  xmlhttp.send();
}

// Voer de functie checkForNewOrders uit direct bij het laden, daarna elke 5 seconden
checkForNewOrders(); // Eerste check direct uitvoeren
setInterval(checkForNewOrders, 5000); // Daarna elke 5 seconden
</script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<ul class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand  btn btn-outline-default " href="/admin">Admin</a>
  <a class="btn btn-outline-default " href="/docent">Docent</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
    aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
    <ul class="navbar-nav">
    <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-info shadow " href="/admin/bestelling">Zoeken op email</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-primary shadow " href="/admin/project">Zoeken op project</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-info shadow " href="/admin/all/items">Bestellen</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-primary shadow " href="/admin/bestelling/besteld">Overzicht niet besteld</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-info shadow " href="/admin/all">Alle bestellingen</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-primary shadow " href="/admin/reject">Afgekeurd</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-info shadow " href="/admin/complete">Besteld</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-primary shadow " href="/admin/websites">Websites</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-info shadow " href="/admin/users">Gebruikers</a>
      </li>
      <li class="nav-item mr-2">
        <a class="nav-link text-light btn btn-outline-primary shadow " href="/admin/beheer">Producten</a>
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