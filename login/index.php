<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
 <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">


    <style>
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>

        <?php
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            echo "<div class=\"message alert {$message['type']}\">{$message['text']}</div>";
            unset($_SESSION['message']);
        }
        ?>

        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username">Gebruikersnaam:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password">Wachtwoord:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Inloggen</button>
        </form>
<div class="mt-3">
            <a href="/register" class="btn btn-success">Registreren</a>
        </div>
    </div>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>