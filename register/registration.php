<?php
session_start();

include '../includes/config.php';
// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer op fouten bij de verbinding
if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

// Ontvang de gegevens van het registratieformulier
$username = $_POST["username"];
$email = $_POST["email"];
$password = $_POST["password"];

// Controleer of de gebruikersnaam en het e-mailadres al in de database bestaan
$existingUserSql = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
$existingUserResult = $conn->query($existingUserSql);

if ($existingUserResult->num_rows > 0) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Gebruikersnaam of e-mailadres bestaat al'];
    header("Location: /register
"); // Redirect naar ../register

    exit();
}

// Controleer of het juiste e-maildomein wordt gebruikt
$allowedDomain = 'graafschapcollege.nl'; // Vervang dit met het gewenste domein
$emailParts = explode('@', $email);
$domain = end($emailParts);

if ($domain !== $allowedDomain) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Alleen e-mailadressen met het domein ' . $allowedDomain . ' zijn toegestaan.'];
    header("Location: /register
"); // Redirect naar ../register

    exit();
}

// Genereer een verificatietoken
$verificationToken = bin2hex(random_bytes(32));

// Encrypteer het wachtwoord
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Voeg de gegevens toe aan de database
$sql = "INSERT INTO users (username, email, password, verification_token, is_verified) VALUES ('$username', '$email', '$hashedPassword', '$verificationToken', 0)";

if ($conn->query($sql) === TRUE) {
    // Verstuur de bevestigingsmail
    $to = $email;
    $subject = "Accountregistratie - Verifieer je e-mailadres";
    $verificationLink = $base_url.'/verify?token=' . $verificationToken;
    $message = "Beste " . $username . ",\n\nBedankt voor je registratie\n\nKlik op de volgende link om je account te verifireren: " . $verificationLink;

    // Stel e-mailheaders in
    $headers = "From:Admin verificatie  <".$confemail.">\r\n";

    // Verzend de e-mail
    mail($to, $subject, $message, $headers);

    $_SESSION['message'] = ['type' => 'success', 'text' => 'Registratie succesvol! Een verificatiemail is naar je e-mailadres verzonden.'];

} else {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Er is een fout opgetreden: ' . $conn->error];
}

// Sluit de databaseverbinding
$conn->close();

header("Location: /login
");

exit();
?>