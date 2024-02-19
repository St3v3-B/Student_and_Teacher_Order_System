<?php
session_start();

// Controleer of er een melding is opgeslagen in de sessie
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Verwijder de melding uit de sessie
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
 <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">


</head>
<body>
    <div class="container">
        <h2 class="mt-5">Registratie</h2>
        <?php if (isset($message)) { ?>
            <div class="alert alert-<?php echo $message['type']; ?>"><?php echo $message['text']; ?></div>
        <?php } ?>
        <form method="POST" action="registration.php">
            <div class="form-group">
                <label for="username">Gebruikersnaam:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Wachtwoord:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Registreren</button>
        </form>
        <div class="mt-3">
            <a href="../login" class="btn btn-success">Login</a>
        </div>
    </div>
</body>
</html>