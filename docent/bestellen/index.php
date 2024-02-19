<?php
// Mobiel apparaat detectie en toegang weigeren
$useragent = $_SERVER['HTTP_USER_AGENT'];

function isMobileDevice($useragent)
{
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
        header(sprintf('Location: https://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']));
        exit();
    }
}

include '../../includes/config.php';

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
    while ($row = $result->fetch_assoc()) {
        $toegestanewebsites[] = $row['url'];
    }
} else {
    echo "Geen actieve websites gevonden.";
}

// Query om categorieën op te halen
$sqlCategories = "SELECT category_id, name FROM categories";
$resultCategories = $conn->query($sqlCategories);

$categories = array();
if ($resultCategories->num_rows > 0) {
    while ($row = $resultCategories->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();

include '../../includes/login_check.php';

// Get product data
$productData = json_decode(file_get_contents($base_url.'/docent/bestellen/get_products.php'), true);

// Build category options
$catOptions = '<option value=--selecteer categorie-->--selecteer categorie--</option>';
foreach ($productData as $category => $products) {
    $catOptions .= "<option value='$category'>$category</option>";
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Bestellen</title>
    <style>
        /* Stijlen hier */
        .hidden {
            display: none;
        }
    </style>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Eigen stylesheet -->
    <link rel="stylesheet" href="/includes/style.css">
    <link rel="shortcut favicon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">
</head>

<body>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <?php include '../../includes/docent_header.php'; ?>
    <div class="background-overlay">
        <br>
        <div class="container">
            <h2 class="mt-5">Bestelformulier</h2>
            <!-- Formulier POST actie -->
            <form id="order-form" method="post" action="verwerken.php">
                <!-- Gebruikersinformatie -->
                <div class="form-group">
                    <label for="gebruikersnaam">Gebruikersnaam:</label>
                    <div class="form-control" id="gebruikersnaam" name="gebruikersnaam">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Email">Email:</label>
                    <div class="form-control" id="Email">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                </div>
                <hr>
                <!-- Standard Producten-->
                <div id="dropdowns"></div>
                <button type="button" id="addsproduct" class="btn btn-primary mb-3">Voeg standard product toe</button>
                <hr>
                <!-- einde Standard Producten-->
                <!-- Producten -->
                <div id="product-fields"></div>
                <button type="button" id="add-product-btn" class="btn btn-info mb-3">Voeg non standard product toe</button>
                <hr>
                <!-- einde Producten -->
                <div class="form-group">
                    <label for="opmerkingen">Opmerking:</label>
                    <textarea class="form-control" id="opmerkingen" name="opmerkingen" rows="1" maxlength="110"></textarea>
                </div>
                <input id="send-button" type="submit" class="btn btn-success" value="Bestelling Verzenden" style="display: none;">
            </form>
        </div>
    </div>
    </div>
    <script>
        function removeProduct(element) {
            element.closest(".product").remove();
            updateProductHeaders();
        }
        var count = 1;

        var productData = <?php echo json_encode($productData); ?>;
        var catOptions = <?php echo json_encode($catOptions); ?>;

        function fillProductDropdown(catId, prodId) {

            var cat = $("#" + catId).val();
            $("#" + prodId).empty();

            if (productData[cat]) {

                productData[cat].forEach(function(product) {
                    $("#" + prodId).append("<option value='" + product.product_id + "'>" + product.name + "</option>");
                });

            } else {

                $("#" + prodId).append("<option>Geen producten</option>");

            }

        }

        function addDropdown() {
            var code = `
      <div class="dropdown">
      <span class="remove product-remove font-weight-bold">X</span>
      <div class="product-header">Product  ${count}</div>
      <div class="form-group">
      <select class="form-select custom-select" name="category[]" id="category${count}">${catOptions} </select>
      </div>
      <div class="form-group">
      <select class="form-select custom-select" name="products[]" id="products${count}"></select>
      </div>
      <div class="form-group">
        <label for="productaantal">Product Aantal:</label>
        <input type="number" id="productaantal" class="form-control" name="productsaantal[]" required ; updateProductHeaders();">
      </div>
      <hr>
      </div>

    `;

            $("#dropdowns").append(code);
            $("#category" + count).change(function() {
                fillProductDropdown($(this).attr("id"), $(this).attr("id").replace("category", "products"));
            });

            fillProductDropdown("category" + count, "products" + count);

            count++;
            updateProductHeaders();
        }

        $("#addsproduct").click(addDropdown);
        var catDropdown = `
    <select>
    
      <option value=""></option>
    </select>
  `;
        $(document).on("click", ".remove", function() {
            updateProductHeaders();
            $(this).closest(".dropdown").remove();
            updateProductHeaders();
        });
    </script>
    <!-- JavaScript logica -->
    <script>
        // Functie om een product te verwijderen
        function removeProduct(element) {
            element.closest(".product").remove();
            updateProductHeaders();
        }


        function addStandardProduct() {
            const productsDiv = document.getElementById("product-fields");
            const productCount = productsDiv.getElementsByClassName("product").length;
            updateProductHeaders();
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
        <input type="number" id="productaantal" class="form-control" name="productaantal[]" required ; updateProductHeaders();">
      </div>
    `;
            // Voegt het nieuwe product toe aan de pagina
            productsDiv.appendChild(productElement);
            updateProductHeaders();
        }


        // Functie om een nieuw product toe te voegen
        function addProduct() {
            const productsDiv = document.getElementById("product-fields");
            const productCount = productsDiv.getElementsByClassName("product").length;

            const productElement = document.createElement("div");
            productElement.classList.add("product");
            productElement.innerHTML = `
      <span class="product-remove font-weight-bold" onclick="removeProduct(this)">X</span>
      <div class="product-header">Product ${productCount}</div>
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
        <input type="number" id="productaantal" class="form-control" name="productaantal[]" required; updateProductHeaders();">
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
            const dproductCount = document.getElementsByClassName("dropdown").length;

            if (productCount > 0) {
                sendButton.style.display = "block";
            } else if (dproductCount > 0) {
                sendButton.style.display = "block";
            } else {
                sendButton.style.display = "none";
            }
            console.log(dproductCount, productCount);
            const productHeaders = document.getElementsByClassName("product-header");
            for (let i = 0; i < productHeaders.length; i++) {
                productHeaders[i].textContent = `Product ${i + 1}`;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('add-product-btn').addEventListener('click', function(e) {
                addProduct();
                updateProductHeaders();
            });
        });

        // Zorgt ervoor dat bij het laden van de pagina de totaalprijs en product headers worden geupdate
        window.addEventListener("DOMContentLoaded", function() {
            updateProductHeaders();
        });
        setInterval(function() {
            updateProductHeaders();

        }, 1000);
    </script>
</body>

</html>