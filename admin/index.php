<?php
include '../includes/admin_check.php';
include '../includes/config.php'; // Zorg dat config hier geinclude wordt zodat getConnection beschikbaar is

function getOrderCounts($status)
{
    global $servername, $username, $password, $dbname; // Gebruik de globale DB gegevens in de functie

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    $query = "SELECT COUNT(*) AS count FROM bestellingen WHERE besteld = " . $status;
    $result = $conn->query($query);
    $count = $result->fetch_assoc();
    $conn->close();

    return $count['count'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/includes/style.css">
    <title>bestellingen gcmsi</title>
</head>

<body>
    <?php include '../includes/header.php'; ?><br><br><br><br><br><br><br><br><br>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <a href="all" class="card-link btn btn-outline shadow">
                    <div class="card">
                        <div class="card-header">
                            Totaal Bestellingen
                        </div>
                        <div class="card-body">
                            <?= getOrderCounts(0) + getOrderCounts(1) + getOrderCounts(2); ?>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="complete/" class="card-link btn btn-outline shadow">
                    <div class="card">
                        <div class="card-header">
                            Bestelde Bestellingen
                        </div>
                        <div class="card-body">
                            <?= getOrderCounts(1); ?>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="bestelling/besteld/" class="card-link btn btn-outline shadow">
                    <div class="card">
                        <div class="card-header">
                            Niet Bestelde Bestellingen
                        </div>
                        <div class="card-body">
                            <?= getOrderCounts(0); ?>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="reject/" class="card-link btn btn-outline shadow">
                    <div class="card">
                        <div class="card-header">
                            Afgekeurde Bestellingen
                        </div>
                        <div class="card-body">
                            <?= getOrderCounts(2); ?>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>