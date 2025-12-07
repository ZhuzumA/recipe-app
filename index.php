<?php include "inc/header.php"; ?>

<div class="card">
    <h1>This Is Our First Recipe WebSite</h1>
    <p>Your place to find and save great recipes.</p>

    <form action="/recipe/recipes/search.php" method="get">
        <input type="text" name="q" placeholder="Search recipes...">
        <button class="btn">Search</button>
    </form>
</div>

<?php include "inc/footer.php"; ?>



<?php
/*
// Include the database connection file
include('inc\db.php');

// Check if the PDO object is created (i.e., the connection was successful)
if ($pdo) {
    echo "Amazing you are connected!";
} else {
    echo "Unable to connect";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Test</title>
</head>
<body>
    <h1>Testing Database Connection with PDO</h1>
    <p><?php echo isset($pdo) ? 'Connected successfully!' : 'Unable to connect'; ?></p>
</body>
</html>
*/