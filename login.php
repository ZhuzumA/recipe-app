<?php
// Start the session so we can track whether the user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Pull in the database connection — this gives us access to $pdo
include('db.php'); // Make sure db.php defines $pdo properly

// Initialize an error message variable (empty by default)
$error_message = '';

// --- Handle the form submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Grab the email and password from the form
    // Trim removes extra spaces from the email input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Look up the user by email in the database
    $sql = "SELECT id, name, password_hash FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If we found a user AND the password matches the stored hash
    if ($user && password_verify($password, $user['password_hash'])) {
        // Save user info into the session so we know they're logged in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['logged_in'] = true;

        // Redirect them to their account page
        header("Location: account.php");
        exit;
    } else {
        // If login fails, set an error message to show on the page
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>

    <!-- Show the error message if login failed -->
    <?php if (!empty($error_message)): ?>
        <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- Simple login form -->
    <form method="POST" action="login.php">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
