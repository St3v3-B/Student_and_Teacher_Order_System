<?php include '../../includes/login_check.php';
$did = $user['id'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">

    
</head>

<body>
<?php include '../../includes/docent_header.php'; ?>
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

        // Haal bestellingen op voor het opgegeven e-mailadres
        $email = $_GET["email"];
        $query = "SELECT * FROM bestellingen_docenten WHERE besteld = 0 AND docent_id = $did";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {

            echo "<table class='table'>";
            echo "<thead class='thead-dark'>
                        <tr>
                            <th>Bestellings-ID</th>
                            <th>Besteldatum</th>
                            <th>Naam</th>
                            <th>Status</th>
                        </tr>
                    </thead>";
            echo "<tbody>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td><a href='details?id=" . $row['id'] . "'>" . $row['id'] . "</a></td>";
                echo "<td>" . $row['datum'] . "</td>";
                echo "<td>" . $user['username']. "</td>";
                echo "<td>" . (($row['besteld'] == 1) ? "Besteld" : (($row['besteld'] == 2) ? "Afgekeurd" : "Niet Besteld")) . "</td>";
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

        <a class="btn btn-primary" href="/docent">Terug naar Dashboard</a>
    </div>
</body>

</html>