<?php
// Start the session — not strictly needed for registration itself,
// but useful if we later want to store messages or auto-login after signup.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Pull in the database connection so we can talk to MySQL
include('db.php'); // Adjust path if needed

// We'll collect any validation errors in this array
$errors = [];

// --- Handle form submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Grab and clean up the form inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Basic validation checks ---
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    // --- Check if the email is already registered ---
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "This email is already registered.";
        }
    }

    // --- If everything looks good, insert the new user ---
    if (empty($errors)) {
        // Hash the password securely before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $hashed_password
        ]);

        // Redirect to login page with a success flag
        header("Location: login.php?success=registered");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>

    <?php
    // If there are any errors, show them in red above the form
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
    ?>

    <!-- Registration form -->
    <form method="POST" action="register.php">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>
