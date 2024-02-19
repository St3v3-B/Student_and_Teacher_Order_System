<?php
session_start();

if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header("Status: 301 Moved Permanently");
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}

include '../includes/config.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!empty($_GET["token"])) {
    $verificationToken = $_GET["token"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $verificationToken);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {

    } elseif ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];

        $_SESSION['message'] = ['type' => 'success', 'text' => 'Account geverifieerd!'];

        $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $updateStmt->bind_param("i", $userId);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Geen geldige verificatie link gebruikt.'];
    }
    $stmt->close();
}

$conn->close();

header("Location: /login");
exit();
