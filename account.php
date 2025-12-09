<?php
// Start the session so we can access user data stored in $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Pull in the database connection — make sure 'db.php' defines $pdo
include('db.php'); 

// --- 1. Authorization Check ---
// If there's no user ID in the session, the user isn't logged in.
// Redirect them to the login page to prevent unauthorized access.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Grab the user's ID and name from the session.
// These were set during login and will be used to personalize the page.
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name']; // This assumes you stored it during login
$favorites = []; // We'll fill this with the user's saved recipes

// --- 2. Data Fetching: Favorite Recipes ---
try {
    // SQL query to get the user's favorite recipes.
    // We're joining the 'favorites' table with 'recipes' to get full details.
    // We also format the saved date for display.
    $sql = "
        SELECT 
            r.id, 
            r.title, 
            r.total_time_minutes, 
            r.difficulty, 
            DATE(f.saved_at) AS saved_date
        FROM 
            favorites f
        JOIN 
            recipes r ON f.recipe_id = r.id
        WHERE 
            f.user_id = :user_id
        ORDER BY 
            f.saved_at DESC;
    ";

    // Prepare and execute the query securely using PDO
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    // Fetch all matching recipes into an array
    $favorites = $stmt->fetchAll();

} catch (PDOException $e) {
    // If something goes wrong, log the error for debugging
    error_log("Favorites Fetch Error: " . $e->getMessage());

    // Show a friendly message to the user (don’t expose technical details)
    $error_message = "Could not retrieve your favorite recipes.";
}
?>
