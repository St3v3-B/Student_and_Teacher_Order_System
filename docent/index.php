<?php
include '../includes/login_check.php';
include '../includes/config.php'; // Maakt getConnection variabel beschikbaar

$did = $user['id']; // Aangenomen dat $user['id'] juist is ingesteld

function getOrderCounts($status, $did) {
    global $servername, $username, $password, $dbname; // Databasegegevens

    // Maak een nieuwe databaseconnectie
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Kan geen verbinding maken met de database: " . $conn->connect_error);
    }

    // Prepared statement om SQL-injectie te voorkomen
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM bestellingen_docenten WHERE besteld = ? AND docent_id = ?");
    $stmt->bind_param("ii", $status, $did);
    $stmt->execute();
    $result = $stmt->get_result();

    // Haal het aantal bestellingen op
    $count = $result->fetch_assoc()['count'];

    // Sluit de statement en connectie
    $stmt->close();
    $conn->close();

    return $count;
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="/includes/style.css" rel="stylesheet">
    <title>Docenten Landing Page</title>
</head>

<body>
    <?php include '../includes/docent_header.php'; ?>
    <br><br><br><br><br><br><br><br><br>
    <div class="container mt-4">
    <?php
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            echo "<div class=\"message alert {$message['type']}\">{$message['text']}</div>";
            unset($_SESSION['message']);
        }
        ?>
        <div class="row">
            <div class="col-md-3">
                <a href="all" class="card-link btn btn-outline shadow">
                    <div class="card">
                        <div class="card-header">
                            Totaal Bestellingen
                        </div>
                        <div class="card-body">
                            <?= getOrderCounts(0, $did) + getOrderCounts(1, $did) + getOrderCounts(2, $did); ?>
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
                            <?= getOrderCounts(1, $did); ?>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="besteld/" class="card-link btn btn-outline shadow">
                    <div class="card">
                        <div class="card-header">
                            Niet Bestelde Bestellingen
                        </div>
                        <div class="card-body">
                            <?= getOrderCounts(0, $did); ?>
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
                            <?= getOrderCounts(2, $did); ?>
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