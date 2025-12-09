<?php
// Starting the session — this will help later if we want to track favorites or user actions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Including the database connection file — this gives us access to $pdo for queries
include('db.php'); // Make sure this file is in the same folder

// --- Step 1: Get the recipe ID from the URL ---
// We expect something like recipe.php?id=3
// If it's missing or not a number, show a friendly message and stop the script
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<h2 style='color:red;'>Invalid recipe ID. Please go back and select a recipe.</h2>");
}

// Store the recipe ID as an integer — just to be safe
$recipe_id = (int)$_GET['id'];

// Prepare variables to hold the recipe data
$recipe = null;
$ingredients = [];
$steps = [];
$error_message = '';

// --- Step 2: Fetch the recipe details from the database ---
try {
    // Getting the main recipe info: title, description, difficulty, and total time
    $sql_recipe = "
        SELECT 
            id, 
            title, 
            description, 
            difficulty, 
            total_time_minutes 
        FROM 
            recipes 
        WHERE 
            id = :id;
    ";
    $stmt = $pdo->prepare($sql_recipe);
    $stmt->execute([':id' => $recipe_id]);
    $recipe = $stmt->fetch();

    // If no recipe is found, we set an error message
    if (!$recipe) {
        $error_message = "Recipe not found.";
    }

} catch (PDOException $e) {
    // Log the error for debugging, but show a simple message to the user
    error_log("Recipe Fetch Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching the recipe.";
}

// If we found the recipe, continue to fetch ingredients and steps
if ($recipe) {
    // --- Step 3: Fetch ingredients for this recipe ---
    try {
        // Joining recipe_ingredients with ingredients to get names and quantities
        $sql_ingredients = "
            SELECT 
                i.name, 
                ri.quantity 
            FROM 
                recipe_ingredients ri
            JOIN 
                ingredients i ON ri.ingredient_id = i.id
            WHERE 
                ri.recipe_id = :id
            ORDER BY 
                i.name;
        ";
        $stmt = $pdo->prepare($sql_ingredients);
        $stmt->execute([':id' => $recipe_id]);
        $ingredients = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Ingredients Fetch Error: " . $e->getMessage());
        $ingredients = []; // Clear the array just in case
        $error_message .= " Could not load ingredients.";
    }

    // --- Step 4: Fetch preparation steps ---
    try {
        // Getting the step-by-step instructions with time estimates
        $sql_steps = "
            SELECT 
                step_number, 
                instruction, 
                time_minutes 
            FROM 
                steps 
            WHERE 
                recipe_id = :id
            ORDER BY 
                step_number;
        ";
        $stmt = $pdo->prepare($sql_steps);
        $stmt->execute([':id' => $recipe_id]);
        $steps = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Steps Fetch Error: " . $e->getMessage());
        $steps = []; // Clear the array just in case
        $error_message .= " Could not load preparation steps.";
    }
}
?>








<!DOCTYPE html>
<html>
<head>
    <!-- Page title changes based on the recipe name -->
    <title><?= $recipe ? htmlspecialchars($recipe['title']) : 'Recipe Details' ?></title>
</head>
<body>

    <?php if ($error_message): ?>
        <!-- If something went wrong (e.g. recipe not found), show the error in red -->
        <p style="color:red;"><?= htmlspecialchars($error_message) ?></p>

    <?php elseif ($recipe): ?>
        
        <!-- === Recipe Header Section === -->
        <header>
            <!-- Main recipe title -->
            <h1><?= htmlspecialchars($recipe['title']) ?></h1>

            <!-- Difficulty level and total time -->
            <p><strong>Difficulty:</strong> <?= htmlspecialchars($recipe['difficulty']) ?></p>
            <p><strong>Total Time:</strong> <?= (int)$recipe['total_time_minutes'] ?> minutes</p>
        </header>

        <!-- === Description Section === -->
        <section>
            <h2>Description</h2>
            <!-- Converts line breaks from the database into <br> tags -->
            <p><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
        </section>

        <hr>

        <!-- === Ingredients Section === -->
        <section>
            <h2>Ingredients</h2>

            <?php if (!empty($ingredients)): ?>
                <ul>
                    <?php foreach ($ingredients as $item): ?>
                        <li>
                            <!-- Shows quantity and ingredient name -->
                            <strong><?= htmlspecialchars($item['quantity']) ?></strong> of <?= htmlspecialchars($item['name']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <!-- If no ingredients are found -->
                <p>No ingredients listed.</p>
            <?php endif; ?>
        </section>

        <hr>

        <!-- === Preparation Steps Section === -->
        <section>
            <h2>Preparation Steps</h2>

            <?php if (!empty($steps)): ?>
                <ol>
                    <?php foreach ($steps as $step): ?>
                        <li>
                            <!-- Instruction and time estimate for each step -->
                            <?= htmlspecialchars($step['instruction']) ?> 
                            (Time: <strong><?= (int)$step['time_minutes'] ?> minutes</strong>)
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else: ?>
                <!-- If no steps are found -->
                <p>No steps listed.</p>
            <?php endif; ?>
        </section>

        <!-- === Navigation Link === -->
        <p><a href="account.php">Back to Account</a></p>

    <?php else: ?>
        <!-- Just in case nothing is set and no error was triggered -->
        <p style="color:gray;">No content to display. Please check your recipe ID or database.</p>
    <?php endif; ?>

</body>
</html>
