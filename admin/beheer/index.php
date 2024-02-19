<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once "../../includes/config.php"; // Veronderstelt dat uw database configuratie hier zit

// Maak de databaseverbinding
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getCategoriesDropdown($conn) {
    $html = '<option value="">Selecteer een categorie...</option>';

    $sql = "SELECT category_id, name FROM categories WHERE active = 1"; // Veronderstelt dat actieve categorieën met active = 1 aangeduid worden
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $categoryId = $row['category_id'];
        $categoryName = htmlspecialchars($row['name']);
        $html .= "<option value=\"$categoryId\">$categoryName</option>";
    }
    return $html;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['productName'], $_POST['link'], $_POST['category'])) {
    $productName = $_POST['productName'];
    $link = $_POST['link'];
    $productPrice = isset($_POST['productPrice']) ? $_POST['productPrice'] : 0;
    $categoryId = $_POST['category'];

    if ($categoryId == "new") {
        $newCategoryName = $_POST['newCategoryName'];
        $stmt = $conn->prepare("INSERT INTO categories (name, active) VALUES (?, 1)"); // Stel nieuwe categorieën in als actief (active = 1)
        $stmt->bind_param("s", $newCategoryName);
        $stmt->execute();
        $categoryId = $conn->insert_id;
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO products (name, link, category_id, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssid", $productName, $link, $categoryId, $productPrice);
    $stmt->execute();
    $stmt->close();

    echo "<div class='alert alert-success' role='alert'>Product toegevoegd!</div>";
}

// De 'getCategoriesDropdown' functie wordt hieronder aangeroepen in de HTML sectie

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Voeg Product Toe</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/includes/style.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">

</head>
<body>
<div class="container mt-5">
    <h2>Voeg een nieuw product toe</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <div class="form-group">
            <label for="productName">Productnaam:</label>
            <input type="text" class="form-control" id="productName" name="productName" required>
        </div>
        <div class="form-group">
            <label for="link">link:</label>
            <textarea class="form-control" id="link" name="link" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="productPrice">Prijs:</label>
            <input type="text" class="form-control" id="productPrice" name="productPrice" placeholder="0.00" required>
        </div>
        <div class="form-group">
            <label for="category">Categorie:</label>
            <select class="form-control" id="category" name="category" required onchange="newCategoryFieldToggle(this);">
                <?php echo getCategoriesDropdown($conn); ?>
                <option value="new">Nieuwe categorie...</option>
            </select>
        </div>
        <div class="form-group" id="newCategory" style="display: none;">
            <label for="newCategoryName">Nieuwe Categorienaam:</label>
            <input type="text" name="newCategoryName" class="form-control" id="newCategoryName">
        </div>
        <button type="submit" class="btn btn-primary">Voeg Product Toe</button>
        <a class="btn btn-primary" href="delproduct.php">Product lijst</a>
        <a class="btn btn-primary" href="../">Terug naar dashboard</a>
    </form>
</div>

<!-- Bootstrap JS and related libraries -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.9.14/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
function newCategoryFieldToggle(select) {
    var newCategory = document.getElementById('newCategory');
    if (select.value === 'new') {
        newCategory.style.display = 'block';
    } else {
        newCategory.style.display = 'none';
    }
}
</script>

</body>
</html>