<?php
include '../includes/config.php';
session_start();



// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer op fouten bij de verbinding
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error); // Log de fout
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Er is een probleem met de verbinding met de database.'];
    header("Location: /login"); // Stuur de gebruiker terug naar de login-pagina
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["username"]); // Bescherming tegen SQL-injectie
    $password = $_POST["password"];

    // Voorbereiden van de SQL statement
    $sql = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $sql->bind_param("s", $username); // 's' specificereert dat de variabele een string is
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $hashedPassword = $user['password'];

        if (password_verify($password, $hashedPassword)) {
            if ($user['is_verified'] == 1) {
                $_SESSION['user'] = $user['id'];
                header("Location: /docent"); // Redirect naar het dashboard
                exit();
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => 'Je account is nog niet geverifieerd. Controleer je e-mail voor de verificatielink.'];
            }
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Ongeldige gebruikersnaam of wachtwoord.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Ongeldige gebruikersnaam of wachtwoord.'];
    }
    $sql->close();
}

$conn->close();
header("Location: /login");
exit();
?>