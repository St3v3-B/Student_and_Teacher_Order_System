<?php
include_once "../../includes/config.php"; // Voeg de juiste pad naar het bestand toe

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

function getCategoriesOptions($conn) {
  $option = '';
  $query = "SELECT category_id, name FROM categories ORDER BY name";
  $result = $conn->query($query);
  while($row = $result->fetch_assoc()){
    $option .= '<option value="'.$row['category_id'].'">'.$row['name'].'</option>';
  }
  return $option;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['deleteProduct']) && isset($_POST['productId'])) {
    // Voer DELETE query uit
    $productId = $_POST['productId'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();

    echo "<div class='alert alert-success' role='alert'>Product verwijderd!</div>";

  } else if (isset($_POST['changeCategory'])) {
    // Voer UPDATE query uit
    $productId = $_POST['productId'];
    $newCategoryId = $_POST['newCategory'];
    
    $stmt = $conn->prepare("UPDATE products SET category_id = ? WHERE product_id = ?");
    $stmt->bind_param("ii", $newCategoryId, $productId);
    $stmt->execute();
    $stmt->close();

    echo "<div class='alert alert-success' role='alert'>Categorie bijgewerkt!</div>";
  }
}

function getProductsTable($conn) {
  $html = '';
  $sql = "SELECT product_id, name FROM products";
  $result = $conn->query($sql);

  while ($row = $result->fetch_assoc()) {
    $productId = $row['product_id'];
    $productName = htmlspecialchars($row['name']);
    $html .= "<tr>";
    $html .= "<td>$productName</td>";
    $html .= "<td>
                <form method='POST' action=''>
                  <input type='hidden' name='productId' value='$productId'>
                  <select name='newCategory' class='form-control my-2'>
                    ".getCategoriesOptions($conn)."
                  </select>
                  <button type='submit' name='changeCategory' class='btn btn-info'>Categorie wijzigen</button>
                  <button type='submit' name='deleteProduct' class='btn btn-danger'>Verwijder</button>
                </form>
              </td>";
    $html .= "</tr>";
  }
  return $html;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <title>Product Beheer</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="/includes/style.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">

</head>
<body>
<?php include '../../includes/header.php'; ?>
<br>  <br>  <br>  
<div class="container mt-5">
  <h2>Product Beheer</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Productnaam</th>
        <th>Categorie</th>
      </tr>
    </thead>
    <tbody>
      <?php echo getProductsTable($conn); ?>
    </tbody>
  </table>
</div>

<!-- Bootstrap JS and related libraries -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.9.14/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>