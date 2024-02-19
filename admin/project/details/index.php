<?php include '../../../includes/admin_check.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../../includes/style.css">
    </head>
<body>
<?php include '../../../includes/header.php'; ?>
    <div class="container">
        <?php
        // Controleer of er een bestellings-ID is opgegeven in de URL
        if (isset($_GET["id"])) {
            // Haal het bestellings-ID op uit de URL
            $bestellingID = $_GET["id"];

            // Verbinding maken met de database
            include '../../../includes/config.php';

            // Maak verbinding met de database
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Controleer de verbinding
            if ($conn->connect_error) {
                die("Connectie mislukt: " . $conn->connect_error);
            }

            // Query om de bestelling en bestelde producten op te halen
            $query = "SELECT * FROM bestelde_producten WHERE bestelling_id = $bestellingID";
            $besteldx = "SELECT besteld AS besteld_x FROM bestellingen WHERE id = $bestellingID";
            $totalQuery = "SELECT totaalprijs AS total_prijs FROM bestellingen WHERE id = $bestellingID";
            // Voer de query uit om de bestelde producten op te halen
            $result = $conn->query($query);
            $resultbesteld = $conn->query($besteldx);

            // Controleren of er resultaten zijn gevonden
            if ($result->num_rows > 0) {
                echo "<h3>Bestelde producten:</h3>";
                echo "<table class='table'>";
                echo "<thead><tr><th>Productnaam</th><th>Link</th><th>Aantal</th><th>Prijs per stuk</th></tr></thead>";
                echo "<tbody>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["productnaam"] . "</td>";
                    echo "<td><a href=" . $row["productlink"] . "> Link naar product (klik hier)</a></td>";
                    echo "<td>" . $row["productaantal"] . "</td>";
                    echo "<td>&euro;" . $row["productprijs"] . "</td>";
                    //echo "<td>" . $resultbesteld["besteld_x"] . "</td>";
                    echo "</tr>";
                }

                // Voer de query uit om de totaalprijs op te halen
                $totalResult = $conn->query($totalQuery);
                $totalRow = $totalResult->fetch_assoc();

                echo "<tr>";
                echo "<td colspan='3'><strong>Totaalprijs:</strong></td>";
                echo "<td><strong>&euro;" . $totalRow["total_prijs"] . "</strong></td>";
                echo "</tr>";

                echo "</tbody>";
                echo "</table>";

                // Voeg een knop toe om terug te keren naar de e-mailinvoerpagina
                echo "<a href='javascript:javascript:history.go(-1)' class='btn btn-primary'>Terug naar zoeken op project</a>";
            } else {
                echo "Geen bestelling gevonden met ID: " . $bestellingID;
            }

            // Verbinding met de database sluiten
            $conn->close();
        } else {
            echo "Geen bestellings-ID opgegeven in de URL.";
        }
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>