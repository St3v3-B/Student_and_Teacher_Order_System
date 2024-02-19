<?php
// Inbegrepen bestand dat een beveiligingscontrole uitvoert om te bepalen of de huidige gebruiker toegang heeft tot de beheerdersgerelateerde functies.
include '../../includes/admin_check.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="/includes/style.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">


</head>

<body>
    <?php
    // Dit is de koptekstbestand dat gemeenschappelijke markup bevat die waarschijnlijk gedeeld wordt tussen verschillende paginas
    include '../../includes/header.php';
    ?>
    <br>
    <?php
    // Hier beginnen we met de PHP-code die interageert met de database 

    // Maak verbinding met de database
    include '../../includes/config.php'; // Verbinding gegevens zoals de servernaam, gebruikersnaam, wachtwoord en de databasenaam.
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Controleer of de verbinding met de database niet tot stand kon komen en stopt de uitvoering van het script als er een fout optreedt.
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Controleert of het http verzoek van het type POST is en of het verwijderen actie heeft
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verwijderen'])) {
        $userId = $_POST['verwijderen']; // Hier wordt de gebruikers ID verkregen die moet worden verwijderd

        // Eerst haal de gebruikersnaam op van de gebruiker die we mogelijk gaan verwijderen.
        $query = "SELECT username FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!in_array($user['username'], $non_deletable_user)) {
            // Als de gebruiker verwijderbaar is, voer de delete opdracht uit.
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                // Sla een succesbericht op in de sessie om later weer te geven.
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Gebruiker is verwijderd.'];
            } else {
                // Bij een fout bij het verwijderen, sla een foutmelding op in de sessie.
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Er is een fout opgetreden bij het verwijderen van de gebruiker.'];
            }
            $stmt->close();
        } else {
            // Als de gebruiker niet verwijderd kan worden, sla een foutbericht op in de sessie.
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Deze gebruiker kan niet worden verwijderd.'];
        }

        // Herlaad de pagina om de veranderingen te tonen en te voorkomen dat het formulier opnieuw wordt verstuurd bij een paginaverfrissing.
        header("Location: /admin/users");
        exit();
    }

    // Checkt of er een POST verzoek is om gebruikerstypes te wijzigen
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_type'])) {
        // Loop door alle verstuurde gebruikerstypes
        foreach ($_POST['user_type'] as $userID => $userType) {
            //Check voor elke gebruiker of deze niet verwijderbaar is.
            $query = "SELECT username FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!in_array($user['username'], $non_deletable_user)) {
                // Update de gebruikerstype en ontvanger status in de database
                $ontvanger = isset($_POST['ontvanger'][$userID]) ? 1 : 0;
                $sql = "UPDATE users SET user_type = ?, ontvanger = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $userType, $ontvanger, $userID);
                $stmt->execute();
                $stmt->close();
            }
        }
        // Sla een succesbericht op in de sessie na het bijwerken van de gebruikers.
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Gebruikers zijn bijgewerkt.'];
        header("Location: /admin/users");
        exit();
    }

    // Selecteer alle gebruikers uit de database om ze in de tabel te tonen
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    ?>
    <div class="container">
        <?php // Controleer of er een bericht is opgeslagen in de sessie en toon dit vervolgens 
        if (isset($_SESSION['message'])) : ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>">
                <?php echo $_SESSION['message']['text']; ?>
            </div>
            <?php
            // Vergeet niet het bericht uit de sessie te verwijderen na het tonen
            unset($_SESSION['message']);
            ?>
        <?php endif; ?>
        <h1 class="mt-4">Gebruikerslijst</h1>
        <form method="POST">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gebruikersnaam</th>
                        <th>E-mail</th>
                        <th>Gebruikerstype</th>
                        <th>Ontvanger</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) {
                        // Controleer of de huidige gebruiker verwijderbaar is.
                        $notDeletable = in_array($row['username'], $non_deletable_user);
                    ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <select name="user_type[<?php echo $row['id']; ?>]" class="form-control" <?php if ($notDeletable) echo ' disabled'; ?>>
                                    <option value="user" <?php if ($row['user_type'] === 'user') echo 'selected'; ?>>Gebruiker</option>
                                    <option value="admin" <?php if ($row['user_type'] === 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                            </td>
                            <td>
                                <input type="checkbox" name="ontvanger[<?php echo $row['id']; ?>]" <?php if ($row['ontvanger'] === '1') echo 'checked'; ?> <?php if ($notDeletable) echo ' disabled'; ?>>
                            </td>
                            <td>
                                <?php if (!$notDeletable) { ?>
                                    <!-- Voeg een confirmatie dialoog toe voordat de gebruiker verwijderd wordt -->
                                    <button type="submit" name="verwijderen" value="<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Weet u zeker dat u deze gebruiker wilt verwijderen?');">
                                        Verwijder
                                    </button>
                                <?php } else { ?>
                                    <button type="button" class="btn btn-danger disabled">Niet verwijderbaar</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <!-- Opslaan knop voor het bijwerken van gebruikerstypes en ontvanger gegevens -->
            <button type="submit" name="opslaan" class="btn btn-primary">Opslaan</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>

</html>

<?php
// Sluit de databaseconnectie
$conn->close();
?>