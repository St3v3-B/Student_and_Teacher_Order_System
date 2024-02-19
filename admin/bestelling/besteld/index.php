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
    <br>
    <div class="container">
        <h1>Bestellingen</h1>

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
        $query = "SELECT * FROM bestellingen WHERE besteld = 0";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {

            echo "<table class='table'>";
            echo "<thead class='thead-dark'>
                        <tr>
                            <th>Bestellings-ID</th>
                            <th>Besteldatum</th>
                            <th>Naam</th>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Actie</th>
                        </tr>
                    </thead>";
            echo "<tbody>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><a href='details?id=" . $row['id'] . "'>" . $row['id'] . "</a></td>";
                echo "<td>" . $row['datum'] . "</td>";
                echo "<td>" . $row['voornaam'] . " " .  $row['achternaam'] . "</td>";
                echo "<td>" . $row['project_naam'] . "</td>";
                echo "<td>" . (($row['besteld'] == 1) ? "Besteld" : (($row['besteld'] == 2) ? "Afgekeurd" : "Niet Besteld")) . "</td>";

                if ($row['besteld'] == 0) {
                    echo "<td>
                    <a class='btn btn-primary' href='?email=$email&markeer=" . $row['id'] . "' onclick='return confirm(\"Weet u zeker dat u deze bestelling wilt markeren als besteld?\")'>Besteld</a>
                    <a class='btn btn-danger' href='../besteld/afkeur.php/?id=" . $row['id'] . "'>Afkeuren</a>
                </td>";
                } else {
                    echo "<td>
                    <a class='btn btn-danger' href='?email=$email&verwijder=" . $row['id'] . "' onclick='return confirm(\"Weet u zeker dat u deze bestelling wilt verwijderen?\")'>Verwijderen</a> <!-- Nieuwe kolom -->
                </td>";
                }
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>Geen bestellingen gevonden die nog niet besteld zijn of afgekeurt zijn</p>";
        }
        // Controleren of de verwijderingsparameter is ingesteld
        if (isset($_GET["verwijder"])) {
            $verwijderingsId = $_GET["verwijder"];

            // Verwijder de bestelling uit de database
            $verwijderQuery = "DELETE FROM bestellingen WHERE id = $verwijderingsId";
            $conn->query($verwijderQuery);

            // Eventuele andere acties na het verwijderen van de bestelling
            // ...

            // Terugkeren naar de pagina voor bestellingen
            header("Location: /admin/bestelling/besteld/?email=$email");
            exit();
        }
        $conn->close();
        ?>

        <a class="btn btn-primary" href="/admin">Terug naar Dashboard</a>
    </div>
</body>

</html>