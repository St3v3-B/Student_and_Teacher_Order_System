<?php include '../../includes/admin_check.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <title>Docenten Bestellingen</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/includes/style.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container">
        <h1>Bestellingen van Docenten</h1>

        <?php
        // Databasegegevens
        include '../../includes/config.php';
        // Verbinding maken met de database
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Verwijderen van een bestelling
        if (isset($_GET['verwijder'])) {
            $bestellingsId = $_GET['verwijder'];
            $deleteQuery = "DELETE FROM bestellingen_docenten WHERE id = ?";
            $stmt = $conn->prepare($deleteQuery);
            if ($stmt) {
                $stmt->bind_param("i", $bestellingsId);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success' role='alert'>Bestelling " . htmlspecialchars($bestellingsId) . " is succesvol verwijderd.</div>";
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Fout bij het uitvoeren van de query: " . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            } else {
                echo "<div class='alert alert-danger' role='alert'>Fout bij het voorbereiden van de query: " . htmlspecialchars($conn->error) . "</div>";
            }
        }

        // Markeringsfunctionaliteit
        if (isset($_GET['markeer'])) {
            $bestellingsId = $_GET['markeer'];
            $updateQuery = "UPDATE bestellingen_docenten SET besteld = 1 WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $bestellingsId);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success' role='alert'>
                        Bestelling " . $bestellingsId . " is gemarkeerd als besteld.
                      </div>";
            } else {
                echo "<div class='alert alert-danger' role='alert'>
                        Er is een fout opgetreden bij het markeren van de bestelling als besteld.
                      </div>";
            }
            $stmt->close();
        }

        // Ophalen van alle bestellingen
        $query = "SELECT bd.id, bd.datum, u.username, bd.opmerking, bd.besteld FROM bestellingen_docenten bd INNER JOIN users u ON bd.docent_id = u.id";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            echo "<table class='table'>";
            echo "<thead class='thead-dark'>
                    <tr>
                        <th>Bestellings-ID</th>
                        <th>Besteldatum</th>
                        <th>Docent</th>
                        <th>Opmerking</th>
                        <th>Status</th>
                        <th>Actie</th>
                    </tr>
                  </thead>";
            echo "<tbody>";

            while ($row = $result->fetch_assoc()) {
                $opmerkingHTML = strlen($row['opmerking']) > 20 ? substr($row['opmerking'], 0, 20) . '...<span class="more">Meer</span><span class="completeOpmerking" style="display: none;">' . $row['opmerking'] . '</span>' : $row['opmerking'];
                $statusHTML = $row['besteld'] == 1 ? 'Besteld' : 'Niet Besteld';
                echo "<tr>";
                echo "<td><a href='details?id=" . $row['id'] . "'>" . $row['id'] . "</a></td>";
                echo "<td>" . $row['datum'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td class='opmerking'>" . $opmerkingHTML . "</td>";
                echo "<td>" . $statusHTML . "</td>";
                echo "<td>";
                if ($row['besteld'] == 0) {
                    echo "<a class='btn btn-primary' href='?markeer=" . $row['id'] . "' onclick='return confirm(\"Weet u zeker dat u deze bestelling wilt markeren als besteld?\")'>Markeer als besteld</a> ";
                }
                // Voeg 'Verwijderen' knop toe
                echo "<a class='btn btn-danger' href='?verwijder=" . $row['id'] . "' onclick='return confirm(\"Weet u zeker dat u deze bestelling wilt verwijderen?\")'>Verwijderen</a>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>Geen bestellingen gevonden voor docenten.</p>";
        }

        $conn->close();
        ?>

        <script>
            $(document).ready(function(){
                $(".more").click(function(){
                    $(this).hide();
                    $(this).next(".completeOpmerking").show();
                });
            });
        </script>

        <a class="btn btn-primary" href="../">Terug naar dashboard</a>
    </div>
</body>

</html>