<?php include '../../../includes/admin_check.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Bestellingen Docenten GCMSI</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../../includes/style.css">
</head>
<body>
<?php include '../../../includes/header.php'; ?>
    <div class="container">
        <?php
        if (isset($_GET["id"])) {
            $bestellingID = $_GET["id"];

            include '../../../includes/config.php';
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connectie mislukt: " . $conn->connect_error);
            }

            // Aannemende dat producten voor docenten worden opgeslagen in een tabel genaamd `producten_docenten`
            $query = "SELECT pd.id, pd.naam, pd.link, pd.aantal FROM producten_docenten pd INNER JOIN bestellingen_docenten b ON b.id = pd.bestelling_id WHERE b.id = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $bestellingID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<h3>Bestelde producten door docenten:</h3>";
                echo "<table class='table'>";
                echo "<thead><tr><th>Productnaam</th><th>Link</th><th>Aantal</th></tr></thead>";
                echo "<tbody>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["naam"] . "</td>";
                    echo "<td><a href='" . $row["link"] . "' target='_blank'>Link naar product</a></td>";
                    echo "<td>" . $row["aantal"] . "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";

                echo "<a href='javascript:history.go(-1)' class='btn btn-primary'>Terug naar bestelde bestellingen</a>";
            } else {
                echo "Geen bestelling gevonden voor docent met ID: " . $bestellingID;
            }

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