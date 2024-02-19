<?php include '../../../includes/admin_check.php'; ?>

<!DOCTYPE html>
<html>
<head>
  <title>bestellingen gcmsi</title>
  <!-- Voeg de Bootstrap CSS toe -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
 <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">



</head>
<?php include '../../../includes/header.php'; ?>
<body class="text-center">
  <div class="container">
    
    
    <?php
    
    // Databasegegevens
    include '../../../includes/config.php';
    // Maak verbinding met de database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Controleer en verwerk eventuele verbindingsfouten

    // Haal het bestellings-ID op uit de URL
    $bestellingId = $_GET['id'];
    
    // Controleer of het bestellings-ID is opgegeven
    if ($bestellingId) {
      // Haal de e-mail van de besteller op
      $query = "SELECT email FROM bestellingen WHERE id = $bestellingId";
      $result = $conn->query($query);
      $row = $result->fetch_assoc();
      $email = $row['email'];

      // Controleer of het formulier is ingediend
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ontvang de ingevoerde berichttekst
        $ingevuldBericht = $_POST['bericht'];

        // Markeer de bestelling als afgewezen
        $updateQuery = "UPDATE bestellingen SET besteld = 2 WHERE id = $bestellingId";
        $conn->query($updateQuery);

        // Verstuur de e-mail als de e-mail van de besteller is gevonden
        if ($email) {
          // Stel de e-mailonderwerpregel in
          $onderwerp = "Uw bestelling is afgewezen";

          // Stel het bericht samen
          $bericht = "Bestelling met ID $bestellingId is afgewezen.";

          // Voeg het ingevulde bericht toe aan het bericht
          $bericht .= "\n\nReden voor afwijzing:\n\n $ingevuldBericht";

           // Stel het afzender-e-mailadres in
          $headers .= "From: Bestellings afgewezen <" . $confemail . ">" . "\r\n";
          // Verstuur de e-mail (in deze code is de verzending van de e-mail niet ge&#1087;mplementeerd)
          mail($email, $onderwerp, $bericht, $headers);

          // Geef een bevestigingsbericht weer aan de gebruiker
          echo "De bestelling is succesvol afgewezen en een e-mail is verzonden naar de besteller.";
          $emailen = urlencode($email);
          $redirectUrl = $base_url.'/admin/all/items';
          header('Location: ' . $redirectUrl);
  exit;
        } else {
          echo "Kon de e-mail van de besteller niet vinden.";
        }
      }
    } else {
      echo "Ongeldig bestellings-ID.";
    }

    // Sluit de databaseverbinding
    $conn->close();
    echo "<h1>Bestelling " . $bestellingId . " afkeuren</h1>"
    ?>


    <div class="row justify-content-center">
      <div class="col-md-6">
        <form method="POST" action="">
          <div class="form-group">
            <label for="bericht">Bericht:</label>
            <textarea class="form-control w-100" name="bericht" rows="4" cols="50"></textarea>
          </div>
          <input class="btn btn-primary" type="submit" value="Bestelling afkeuren">
        </form>
      </div>
    </div>
  </div>

  <!-- Voeg de Bootstrap JS toe -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>