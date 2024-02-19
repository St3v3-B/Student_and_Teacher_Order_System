<!doctype html>
<html lang="en">

<head>
    <title>bestellingen gcmsi</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="./includes/truck.css">
    <link rel="stylesheet" href="./includes/style.css">
    
</head>

<body>
    <?php

    // Verbinding maken met de database
    include './includes/config.php';

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Controleren op connectiefouten
    if ($conn->connect_error) {
        die("Connectie mislukt: " . $conn->connect_error);
    }
    // Controleren of er een POST-verzoek is verzonden
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ontvangen gegevens uit het formulier
        $voornaam = $_POST["voornaam"];
        $achternaam = $_POST["achternaam"];
        $email = $_POST["email"];
        $project_naam = $_POST["projectnaam"];
        $productnamen = $_POST["productnaam"];
        $productlinks = $_POST["productlink"];
        $productaantallen = $_POST["productaantal"];
        $productprijzen = $_POST["productprijs"];

        // Bereken de totaalprijs van de producten
        $totaalprijs = 0;

        for ($i = 0; $i < count($productnamen); $i++) {
            $aantal = intval($productaantallen[$i]);
            $prijs = floatval($productprijzen[$i]);

            if ($aantal > 0 && $prijs > 0) {
                $totaalprijs += $aantal * $prijs;
            }
        }


        $aquery = "SELECT email FROM users WHERE ontvanger = '1'";
        $aresult = $conn->query($aquery);

        // Een array maken om de e-mailadressen van de admins op te slaan
        $adminEmails = array();

        while ($row = $aresult->fetch_assoc()) {
            $adminEmails[] = $row['email']; // Het e-mailadres toevoegen aan de array
        }


        // SQL-query om de ingevulde gegevens in te voegen
        $sql = "INSERT INTO bestellingen (voornaam, achternaam, email, project_naam, totaalprijs) VALUES ('$voornaam', '$achternaam', '$email','$project_naam', $totaalprijs)";
        $conn->query($sql);
        // Laatste ingevoegde bestelling ID ophalen
        $bestellingID = $conn->insert_id;

        // Loop door de producten en voeg ze toe aan de bestelde_producten tabel
        for ($i = 0; $i < count($productnamen); $i++) {
            $sql = "INSERT INTO bestelde_producten (bestelling_id, productnaam, productlink, productaantal, productprijs) VALUES ($bestellingID, '$productnamen[$i]', '$productlinks[$i]', $productaantallen[$i], $productprijzen[$i])";
            $conn->query($sql);
        }

        // Stel de e-mailinformatie in
        $ontvanger = $email;
        $onderwerp = "Bestelling ontvangen";
        $bericht = "<h1>Bestelgegevens</h1>";
        $bericht = "<a href=" . $base_url . "/lijst?id=" . $bestellingID . ">het volgende id hoort bij jou bestelling: " . $bestellingID . "</a><br>";
        $bericht .= "<label>Voornaam:</label> " . $voornaam . "<br>";
        $bericht .= "<label>Achternaam:</label> " . $achternaam . "<br>";
        $bericht .= "<label>E-mailadres:</label> " . $email . "<br>";
        $bericht .= "<label>Project Naam:</label> " . $project_naam . "<br>";
        $bericht .= "<br>";
        $bericht .= "<h2>Producten</h2>";
        for ($i = 0; $i < count($productnamen); $i++) {
            $bericht .= "<div class='form-group'>";
            $bericht .= "<label>Product " . ($i + 1) . "</label>";
            $bericht .= "</div>";
            $bericht .= "<label>Product :</label> " . $productnamen[$i] . "<br>";
            $bericht .= '<label>Product Link:</label>  <a href="' . $productlinks[$i] . '">' . $productlinks[$i] . '</a> <br>';
            $bericht .= "<label>Product Aantal:</label> " . $productaantallen[$i] . "<br>";
            $bericht .= "<label>Product Prijs:</label> " . $productprijzen[$i] . "<br>";
            $bericht .= "<hr>";
            $bericht .= "<br>";
        }

        $bericht .= "<div class='form-group'>";
        $bericht .= "<label>Totaal Prijs:</label> " . $totaalprijs . "<br>";
        $bericht .= "</div>";
        // Headers voor HTML-e-mail
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Bestellings Bevestiging <" . $confemail . ">" . "\r\n";
        $headers .= "Cc:" . implode(',', $adminEmails)  . "\r\n";
        // Verstuur de e-mail
        if (mail($ontvanger, $onderwerp, $bericht, $headers)) {
            echo '
    <div class="containerd">
    <br><br>
    <button class="truck-button">
    <span class="default">_</span>
    <span class="success">
        Bestelling Geplaatst. &#9989;
    </span>
    <div class="truck">
        <div class="wheel"></div>
        <div class="back"></div>
        <div class="front"></div>
        <div class="box"></div>
    </div>
</button>
</div>
    ';
            header("Refresh: 5; URL=" . $base_url . "/lijst?id=" . $bestellingID);
        } else {
            echo '
    <button class="truck-button">
    <span class="default">_</span>
    <span class="success">
    Er is een fout opgetreden bij het verzenden van de e-mail. Probeer het later opnieuw. &#10060;
    </span>
    <div class="truck">
        <div class="wheel"></div>
        <div class="back"></div>
        <div class="front"></div>
        <div class="box"></div>
    </div>
</button>
    ';
        }
    } else {
        echo '
    <button class="truck-button">
    <span class="default">_</span>
    <span class="success">
    Geen geldig formulier verzonden. &#10060;
    </span>
    <div class="truck">
        <div class="wheel"></div>
        <div class="back"></div>
        <div class="front"></div>
        <div class="box"></div>
    </div>
</button>
    ';
    }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.0.1/dist/gsap.min.js"></script>
    <script src="./includes/truck.js"></script>
</body>

</html>