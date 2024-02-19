<?php include '../../includes/admin_check.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>bestellingen gcmsi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="/includes/style.css">
	<link rel="shortcut icon" type="image/x-icon" href="https://graafschapcollege.nl/wp-content/themes/graafschapcollege/includes/images/favicon.ico">

    
</head>
<body>
<?php include '../../includes/header.php'; ?>
<br>  <br>  <br>  <br>  <br>  <br>  <br>  <br>
    <div class="container">
        <h1>Project Zoekfunctie</h1>

        <form action="search.php" method="GET">
            <div class="form-group">
                <label for="project">Project Naam</label>
                <input type="project" name="project" id="project" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Zoeken</button><br><br>
            <a class="btn btn-danger" href="/admin/logout">Uitloggen</a>
        </form>
    </div>
</body>
</html>