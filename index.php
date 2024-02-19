<?php
// Mobiel apparaat detectie en toegang weigeren
$useragent = $_SERVER['HTTP_USER_AGENT'];

function isMobileDevice($useragent) {
    return preg_match('/(android|bb\d+|meego).+|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(40|60)|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) 
    || strpos($useragent, 'Mobile') !== false 
    || strpos($useragent, 'Android') !== false 
    || strpos($useragent, 'Silk/') !== false 
    || strpos($useragent, 'Kindle') !== false 
    || strpos($useragent, 'BlackBerry') !== false 
    || strpos($useragent, 'Opera Mini') !== false 
    || strpos($useragent, 'Opera Mobi') !== false;
}

if (isMobileDevice($useragent)) {
    die('Toegang tot deze pagina is niet toegestaan vanaf mobiele telefoons.');
}

// Forceer HTTPS voor beveiligde verbinding
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header("Status: 301 Moved Permanently");
        header(sprintf(
            'Location: https://%s%s',
            $_SERVER['HTTP_HOST'],
            $_SERVER['REQUEST_URI']
        ));
        exit();
    }
}

include './includes/config.php';

// Databaseverbinding
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer de connectie
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query om actieve toegestane URLs op te halen
$sql = "SELECT DISTINCT url FROM websites WHERE actief = 1";
$result = $conn->query($sql);

$toegestanewebsites = array();
if ($result->num_rows > 0) {
    // Data fetchen
    while($row = $result->fetch_assoc()) {
        $toegestanewebsites[] = $row['url'];
    }
} else {
    echo "Geen actieve websites gevonden.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bestellingen GCMSI</title>
    <!-- Bootstrap CSS voor styling -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <!-- Eigen stylesheet voor extra styling -->
     <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">

    </head>

<body>
    <div class="background-overlay">
        <br>
        <div class="body-container">
            <div class="container">
                <!-- Titel van de bestelformulier pagina -->
                <h2 class="mt-5">Bestelformulier</h2>
                <!-- Formulier POST actie verwijst naar verwerken.php -->
                <form method="post" action="verwerken.php">
                    <!-- Formulier invoervelden -->
                    <div class="form-group">
                        <label for="voornaam">Voornaam:</label>
                        <input type="text" id="voornaam" class="form-control" name="voornaam" required>
                    </div>
                    <!-- Invoerveld voor achternaam -->
                    <div class="form-group">
                        <label for="achternaam">Achternaam:</label>
                        <input type="text" id="achternaam" class="form-control" name="achternaam" required>
                    </div>
                    <!-- Invoerveld voor e-mail -->
                    <div class="form-group">
                        <label for="email">E-mailadres:</label>
                        <input type="email" id="email" class="form-control" name="email" required>
                    </div>
                    <!-- Invoerveld voor project-ID -->
                    <div class="form-group">
                        <label for="projectnaam">Project Naam:</label>
                        <input type="text" id="projectnaam" class="form-control" name="projectnaam" required>
                    </div>
                    <!-- Scheiding tussen invoervelden en product sectie -->
                    <hr>
                    <!-- Containerveld voor dynamisch toegevoegde producten -->
                    <div id="products"></div>
                    <!-- Button om een product toe te voegen -->
                    <button type="button" class="btn btn-primary mb-3" onclick="addProduct(); updateProductHeaders();">Voeg Product Toe</button>
                    <!-- Totaalprijssectie -->
                    <hr>
                    <div class="form-group">
                        <label for="total-price" class="mt-2">Totaal Prijs:</label>
                        <!-- Weergave van de huidige totaalprijs -->
                        <span id="total-price" class="mt-2"><strong>&euro;0.00</strong></span>
                    </div>
                    <!-- Waarschuwing voor toegestane website links -->
                    <label style="color: red;">Alleen de goedgekeurde website links kunnen worden gebruikt.</label><br>
                    <!-- Toont de toegestane websites -->
                    <label style="color: red;"><?php echo implode(', ', $toegestanewebsites); ?></label>
                    <!-- Verzendknop zie getoond wordt indien er producten zijn toegevoegd -->
                    <button id="send-button" type="submit" class="btn btn-success" style="display: none;">Verzenden</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Functie om een product te verwijderen
        function removeProduct(element) {
            element.closest(".product").remove();
            updateProductHeaders();
        }

        // Functie om een nieuw product toe te voegen
        function addProduct() {
            const productsDiv = document.getElementById("products");
            const productCount = productsDiv.getElementsByClassName("product").length;

            const productElement = document.createElement("div");
            productElement.classList.add("product");
            productElement.innerHTML = `
      <span class="product-remove font-weight-bold" onclick="removeProduct(this)">X</span>
      <div class="product-header">Product ${productCount + 1}</div>
      <div class="form-group">
        <label for="productnaam">Product Naam:</label>
        <input type="text" id="productnaam" class="form-control" name="productnaam[]" required>
      </div>
      <div class="form-group">
        <label for="productlink">Product Link:</label>
        <input type="text" id="productlink" class="form-control" name="productlink[]" pattern="https:\/\/(www\.)?(<?php echo implode('|', $toegestanewebsites); ?>)\/.*" required>
        </div>
      <div class="form-group">
        <label for="productaantal">Product Aantal:</label>
        <input type="number" id="productaantal" class="form-control" name="productaantal[]" required onchange="updateTotalPrice(); updateProductHeaders();">
      </div>
      <div class="form-group">
        <label for="productprijs">Product Prijs:</label>
        <input type="number" id="productprijs" class="form-control" name="productprijs[]" step="0.01" min="0" placeholder="0.00" required onchange="updateTotalPrice(); updateProductHeaders();">
      </div>
    `;

            // Voegt het nieuwe product toe aan de pagina
            productsDiv.appendChild(productElement);
            updateProductHeaders();
        }

        // Update de headers van de producten na toevoeging of verwijdering
        function updateProductHeaders() {
            const productCount = document.getElementsByClassName("product").length;
            const sendButton = document.getElementById("send-button");

            if (productCount > 0) {
                sendButton.style.display = "block";
            } else {
                sendButton.style.display = "none";
            }

            const productHeaders = document.getElementsByClassName("product-header");
            for (let i = 0; i < productHeaders.length; i++) {
                productHeaders[i].textContent = `Product ${i + 1}`;
            }
        }

        // Updatet de totaalprijs met de ingevoerde waarden
        function updateTotalPrice() {
            const productAantalInputs = document.getElementsByName("productaantal[]");
            const productPrijsInputs = document.getElementsByName("productprijs[]");
            let totalPrice = 0;

            for (let i = 0; i < productAantalInputs.length; i++) {
                const productAantal = parseInt(productAantalInputs[i].value);
                const productPrijs = parseFloat(productPrijsInputs[i].value);

                if (!isNaN(productAantal) && !isNaN(productPrijs)) {
                    totalPrice += productAantal * productPrijs;
                }
            }

            document.getElementById("total-price").innerHTML = "<strong>&euro;" + totalPrice.toFixed(2) + "</strong>";
        }

        // Zorgt ervoor dat bij het laden van de pagina de totaalprijs en product headers worden geupdate
        window.addEventListener("DOMContentLoaded", function() {
            updateTotalPrice();
            updateProductHeaders();
        });
    </script>

</body>

</html>