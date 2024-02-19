<?php
// Inbegrepen bestand dat een beveiligingscontrole uitvoert om te bepalen of de huidige gebruiker toegang heeft tot de beheerdersgerelateerde functies.
include '../../includes/admin_check.php';
?>
<?php
// Sessie starten
session_start();

// Configuratiebestand inbegrepen
include '../../includes/config.php';

// Databaseverbinding
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleren op een databaseverbindingsfout
if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

// Verwerking van het formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Voeg een nieuw linkje toe
    if (isset($_POST['url'])) {
        $url = $_POST['url'];
        $actief = isset($_POST['actief']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO websites (url, actief) VALUES (?, ?)");
        $stmt->bind_param("si", $url, $actief);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Nieuw linkje succesvol toegevoegd.";
        } else {
            $_SESSION['error'] = "Fout bij het toevoegen van een nieuw linkje: " . $stmt->error;
        }
        $stmt->close();

        header("Location: index.php");
        exit();
    }

    // Activeren/deactiveren van een linkje
    if (isset($_POST['toggle_active'])) {
        $id = $_POST['id'];

        $stmt = $conn->prepare("UPDATE websites SET actief = NOT actief WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "De status van het linkje is bijgewerkt.";
        } else {
            $_SESSION['error'] = "Er was een fout bij het bijwerken van de status: " . $stmt->error;
        }
        $stmt->close();

        header("refresh:0;url=./");
        exit();
    }

    // Verwijderen van een linkje
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM websites WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Linkje succesvol verwijderd.";
        } else {
            $_SESSION['error'] = "Er was een fout bij het verwijderen van het linkje: " . $stmt->error;
        }
        $stmt->close();

        header("refresh:0;url=./");
        exit();
    }
}

// Code voor het ophalen van linkjes uit de database
$sql = "SELECT id, url, actief FROM websites";
$result = $conn->query($sql);

// Sluit de databaseverbinding
$conn->close();

// HTML hieronder
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Goed gekeurde Websites</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="/includes/style.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">


</head>

<body>
    <?php

    include '../../includes/header.php';
    ?>
    <div class="container mt-5">
        <!-- Toon berichten -->
        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>

        <!-- Formulier om een nieuw linkje toe te voegen -->
        <h2>Voeg een nieuw linkje toe</h2>
        <form action="" method="post">
            <label>de linkjes moetten zonder https www voorbeeld: indi.nl</label>
            <div class="form-group">

                <label for="url">URL:</label>
                <input type="text" class="form-control" name="url" id="url" required>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="actief" id="actief" value="1">
                <label class="form-check-label" for="actief">Actief</label>
            </div>
            <button type="submit" class="btn btn-primary">Toevoegen</button>
        </form>

        <!-- Tabel met linkjes -->
        <h2>Bestaande Linkjes</h2>
        <?php

        include '../../includes/config.php';

        // Databaseverbinding
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Controleren op een databaseverbindingsfout
        if ($conn->connect_error) {
            die("Verbinding mislukt: " . $conn->connect_error);
        }
        // Code voor het ophalen van linkjes uit de database
        $sql = "SELECT id, url, actief FROM websites";
        $result = $conn->query($sql);

        // Sluit de databaseverbinding
        $conn->close();
        if ($result && $result->num_rows > 0) {
            echo "<table class='table table-bordered'>";
            echo "<thead class='thead-light'>";
            echo "<tr>";
            echo "<th>URL</th>";
            echo "<th>Status</th>";
            echo "<th>Actie</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td>" . ($row['actief'] ? 'Actief' : 'Niet actief') . "</td>";
                echo "<td>";
                echo "<form action='' method='post' style='display:inline;'>";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "<button type='submit' class='btn " . ($row['actief'] ? "btn-warning" : "btn-success") . " btn-sm' name='toggle_active'>" . ($row['actief'] ? "Deactiveer" : "Activeer") . "</button>";
                echo "</form> ";
                echo "<form action='' method='post' style='display:inline; margin-left: 10px;'>";
                echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                echo "<button type='submit' class='btn btn-danger btn-sm' name='delete'>Verwijder</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>Geen linkjes gevonden.</p>";
        }
        ?>
    </div>
</body>

</html>