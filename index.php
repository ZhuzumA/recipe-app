<?php
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
