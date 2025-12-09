<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RecipeApp</title>
    <link rel="stylesheet" href="/recipe/css/style.css">
</head>

<body>
<header class="navbar">
    <div class="nav-container">
        <a href="/recipe/index.php" class="logo">You are in the Best Place with the Best Recipes</a>

        <nav id="nav-menu" class="nav-links">
            <a href="recipes/recipes.php">Recipes</a>

            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/recipe/account.php">Account</a>
                <a href="/recipe/logout.php" class="btn btn-small">Logout</a>
            <?php else: ?>
                <a href="/recipe/login.php">Login</a>
                <a href="/recipe/register.php" class="btn btn-small">Register</a>
            <?php endif; ?>
        </nav>

        <button class="nav-toggle" onclick="toggleNav()">☰</button>
    </div>
</header>

<main class="page-wrapper">


