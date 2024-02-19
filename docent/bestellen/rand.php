<?php

// Database connection
include "../../includes/config.php";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection 
if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$product_prices = array(19.99, 29.99, 24.99, 12.99, 15.50, 10.99, 18.75, 20.99, 22.99, 17.50);  
$categories = array(1, 2, 3);
    
$inserted_products = array(); 

for($i = 0; $i < 200; $i++) {

  $count = $i+1;
  
  // Genereer unieke name en link
  $name = "Product " . $count;
  $link = "link" . $count . ".com";

  // Valideer uniek product  
  if(in_array($name, $inserted_products)) {
    echo "$name bestaat al <br>";
    continue;
  }

  $price = $product_prices[array_rand($product_prices)];
  $category_id = $categories[array_rand($categories)];

  // SQL om product toe te voegen
  $sql = "INSERT INTO products(name, link, price, category_id) VALUES ('$name', '$link', $price, $category_id)";

  // Voer SQL uit
  if($conn->query($sql) === TRUE) {
    echo "Toegevoegd $name <br>";
  } else {
    echo "Fout bij $name: " . $conn->error . "<br>";
  }
  
  $inserted_products[] = $name;
}

$conn->close();  
?>