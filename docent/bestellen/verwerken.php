<?php
include "../../includes/login_check.php";
// Database connection
include "../../includes/config.php";
$conn = new mysqli($servername, $username, $password, $dbname);

try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error); 
    }

    // Check for POST request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($user["id"], $user["email"])) {
            throw new Exception("User ID of Email is niet gedefinieerd");
        }
        
        $userid = $user["id"];
        $email = $user["email"];
        
        // Prepared statements om SQL injection te voorkomen
        $stmt = $conn->prepare("INSERT INTO bestellingen_docenten (docent_id, opmerking) VALUES (?, ?)");
        $stmt->bind_param("is", $userid, $_POST["opmerkingen"]);
        $stmt->execute();
        $bestellingID = $conn->insert_id;
        $stmt->close();
        
        // Verwerk 'sproductnamen' en 'sproductaantallen'
        if (isset($_POST["products"]) && is_array($_POST["products"])) {
            foreach ($_POST["products"] as $i => $productId) {
                $stmt = $conn->prepare("SELECT link, name FROM products WHERE product_id = ?");
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $link = $row["link"];
                    $name = $row["name"];
                } else {
                    $link = "-";
                    $name = "-";
                }
                $stmt->close();

                $aantal = $_POST["productsaantal"][$i];
                $stmt = $conn->prepare("INSERT INTO producten_docenten (bestelling_id, naam, link, aantal) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $bestellingID, $name, $link, $aantal);
                $stmt->execute();
                $stmt->close();
            }
        }
        $_POST["gebruikersnaam"] = $gebruikersnaam;
        if (isset($_POST["productnaam"]) && is_array($_POST["productnaam"])) {
            // Verwerk 'productnamen', 'productlinks', en 'productaantallen'
            foreach ($_POST["productnaam"] as $i => $naam) {
                $link = $_POST["productlink"][$i];
                $aantal = $_POST["productaantal"][$i];
                
                $stmt = $conn->prepare("INSERT INTO producten_docenten (bestelling_id, naam, link, aantal) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $bestellingID, $naam, $link, $aantal);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Stuur e-mailmelding
        $ontvanger = "stevebussing28@outlook.com"; // Vervang dit met het daadwerkelijke e-mailadres van de ontvanger
        $subject = "Nieuwe bestelling ontvangen";
        $message = "Er is een nieuwe bestelling ontvangen\n";
        $message .= "Bestelling ID: $bestellingID\n";

        // Voeg bestelde producten toe aan e-mailbericht
        foreach ($_POST["products"] as $i => $productId) {
            $message .= "Product: {$_POST['products'][$i]}, Aantal: {$_POST['productsaantal'][$i]}\n";
        }

        foreach ($_POST["productnaam"] as $i => $naam) {
            $message .= "Product: {$_POST['productnaam'][$i]}, Aantal: {$_POST['productaantal'][$i]}\n";
        }

        $headers = "From: $confemail";

        // Stuur de e-mail
        mail($ontvanger, $subject, $message, $headers);

    } else {
        throw new Exception("Geen POST data ontvangen!");
    }
    
} catch (Exception $e) {
    // Error handling
    echo "Er is een fout opgetreden: " . $e->getMessage();
} finally {
    // Verbinding sluiten
    if (isset($conn)) {
        $conn->close();
    } 
    header("Refresh: 5; URL=" . $base_url . "/docent/");
}
?>
