<?php include '../../../includes/admin_check.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">

    
</head>

<body>
    <?php include '../../../includes/header.php'; ?>
    <div class="container">
        <h1>Alle Bestellingen</h1>

        <?php
        // Databasegegevens
        include '../../../includes/config.php';
        // Verbinding maken met de database
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Controleer of op de knop "Markeer als besteld" is geklikt
        if (isset($_GET["markeer"])) {
            $bestellingsId = $_GET["markeer"];

            // Update de bestelling als "Besteld" in de database
            $updateQuery = "UPDATE bestellingen SET besteld = 1 WHERE id = $bestellingsId";
            $conn->query($updateQuery);

            // E-mail naar de gebruiker sturen
            $emailQuery = "SELECT voornaam, achternaam, email FROM bestellingen WHERE id = $bestellingsId";
            $emailResult = $conn->query($emailQuery);

            if ($emailResult->num_rows > 0) {
                $row = $emailResult->fetch_assoc();
                $voornaam = $row['voornaam'];
                $achternaam = $row['achternaam'];
                $emailadres = $row['email'];

                // E-mail informatie
                $ontvanger = $emailadres;
                $onderwerp = 'bestelling is gemarkeerd als besteld';
                $bericht = 'Beste ' . $voornaam . $achternaam . ',<br><br>bestelling met id ' . $bestellingsId . ' is gemarkeerd als besteld.<br><br><a href=' . $base_url . '/lijst?id=' . $bestellingsId . '>Klik hier om de bestelling te bekijken </a><br> ';

                // E-mail verzenden

                // Headers voor HTML-e-mail
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Bestellings Bevestiging <" . $confemail . ">" . "\r\n";
                if (mail($ontvanger, $onderwerp, $bericht, $headers)) {
                    // Als de e-mail succesvol is verzonden
                    echo "<div class='alert alert-success' role='alert'>
                        Bestelling " . $bestellingsId . " is gemarkeerd als besteld en een bevestigingsmail is verzonden naar de gebruiker.
                    </div>";
                    header("refresh:1;url=./");
                } else {
                    // Als er een fout optreedt bij het verzenden van de e-mail
                    echo "<div class='alert alert-danger' role='alert'>
                        Er is een fout opgetreden bij het verzenden van de e-mail.
                    </div>";
                }
            }
        }
        // Haal bestellingen op voor het opgegeven e-mailadres
        $email = $_GET["email"];
        $query = "
        SELECT DISTINCT bp.bestelling_id, CONCAT(b.voornaam, ' ', b.achternaam) AS besteller_naam, b.project_naam
        FROM bestelde_producten bp
        INNER JOIN bestellingen b ON bp.bestelling_id = b.id
        WHERE b.besteld = 0
        ORDER BY bp.bestelling_id;
    ";
        $bestellingenResult = $conn->query($query);

        if ($bestellingenResult && $bestellingenResult->num_rows > 0) {
            while ($bestelling = $bestellingenResult->fetch_assoc()) {
                $bestelling_id = $bestelling["bestelling_id"];
                $subQuery = "SELECT * FROM bestelde_producten WHERE bestelling_id = $bestelling_id";
                $subResult = $conn->query($subQuery);
                echo "<div class='mb-4'>";
                echo "<h5>Bestelling ID: " . $bestelling_id . " - " . $bestelling["besteller_naam"] . "  - " . " Project naam: " . $bestelling["project_naam"] . "</h5>";
                // Query om de producten per bestelling te halen
                $subQuery = "SELECT * FROM bestelde_producten WHERE bestelling_id = $bestelling_id";
                $subResult = $conn->query($subQuery);

                echo "<table class='table'>";
                echo "<thead><tr><th>Productnaam</th><th>Link</th><th>Aantal</th><th>Prijs per stuk</th></tr></thead>";
                echo "<tbody>";
                while ($product = $subResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $product["productnaam"] . "</td>";
                    echo "<td><a href='" . $product["productlink"] . "' target='_blank'>Productlink</a></td>";
                    echo "<td>" . $product["productaantal"] . "</td>";
                    echo "<td>&euro;" . $product["productprijs"] . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";

                // Goedkeurknop voor bestelling
                echo "<input type='hidden' name='bestelling_id' value='" . $bestelling_id . "'>";
                echo "<td>
                <a class='btn btn-primary' href='?markeer=" . $bestelling_id . "' onclick='return confirm(\"Weet u zeker dat u deze bestelling wilt markeren als besteld?\")'>Besteld</a>
                <a class='btn btn-danger' href='./afkeur.php/?id=" . $bestelling_id . "'>Afkeuren</a>
                    </td>";
                echo "</form>";
                echo "</div>"; // Sluiting van de bestellingscontainer
            }
        } else {
            echo "Er zijn geen producten te tonen of alle producten zijn al besteld.";
        }
        $conn->close();
        ?>

        <a class="btn btn-info" href="../../">Terug naar dashboard</a>
    </div>
</body>

</html>