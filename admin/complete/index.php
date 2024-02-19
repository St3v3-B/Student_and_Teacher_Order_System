<?php include '../../includes/admin_check.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">

    
</head>
<body>
<?php include '../../includes/header.php'; ?>
    <div class="container">
        <h1>Bestelde Bestellingen</h1>

        <?php
            // Databasegegevens
            include '../../includes/config.php';

            // Verbinding maken met de database
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Haal bestellingen op voor het opgegeven e-mailadres
            $email = $_GET["email"];
            $query = "SELECT * FROM bestellingen WHERE besteld = 1";
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
                    echo "<td><a href='details?id=".$row['id']."'>" . $row['id'] . "</a></td>";
                    echo "<td>" . $row['datum'] . "</td>";
                    echo "<td>" . $row['voornaam'] . " " .  $row['achternaam'] . "</td>";
                    echo "<td>" . $row['project_naam'] . "</td>";
                    echo "<td>" . (($row['besteld'] == 1) ? "Besteld" : (($row['besteld'] == 2) ? "Afgekeurd" : "Niet Besteld")) . "</td>";

                    if ($row['besteld'] == 0) {
                        echo "<td>
                                <a class='btn btn-primary' href='?email=$email&markeer=" . $row['id'] . "'>Besteld</a>
                                <a class='btn btn-danger' href='../bestelling/afkeur.php/?id=" . $row['id'] . "'>Afkeuren</a>
                            </td>";
                    } else {
                        echo "<td></td>";
                    }

                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>Geen bestellingen gevonden die al besteld zijn</p>";
            }

            $conn->close();
        ?>

        <a class="btn btn-primary" href="../">Terug naar dashboard</a>
    </div>
</body>
</html>