<?php
// recipes/recipes.php

// Connect to DB (PDO)
require_once __DIR__ . '/../inc/db.php';   // $pdo must be defined in this file

// --- Read input from query string (GET) ---
$q    = trim($_GET['q'] ?? '');          // keyword
$sort = $_GET['sort'] ?? 'time';         // 'time' or 'alpha'

// --- Build SQL query ---
// We use joins so we can:
//  - search in ingredients
//  - get category names for display (optional)
$sql = "
    SELECT 
        r.id,
        r.title,
        r.description,
        r.difficulty,
        r.total_time_minutes,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category_list
    FROM recipes r
    LEFT JOIN recipe_categories rc ON rc.recipe_id = r.id
    LEFT JOIN categories c         ON c.id = rc.category_id
    LEFT JOIN recipe_ingredients ri ON ri.recipe_id = r.id
    LEFT JOIN ingredients ing       ON ing.id = ri.ingredient_id
    WHERE 1 = 1
";

$params = [];

// --- Keyword search in title, description, ingredient name ---
if ($q !== '') {
    $sql .= " AND (
        r.title       LIKE :q
        OR r.description LIKE :q
        OR ing.name   LIKE :q
    )";
    $params[':q'] = '%' . $q . '%';
}

// --- Group by recipe (because of joins) ---
$sql .= " GROUP BY r.id";

// --- Sorting ---
switch ($sort) {
    case 'alpha':
        // Alphabetical A–Z by title
        $sql .= " ORDER BY r.title ASC";
        break;
    case 'time':
    default:
        // Shortest time first, then A–Z by title
        $sql .= " ORDER BY r.total_time_minutes ASC, r.title ASC";
        break;
}

// --- Execute query ---
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recipe Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 1.5rem;
            background: #f5f5f5;
        }
        h1 {
            margin-bottom: 1rem;
        }
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .search-form input[type="text"] {
            flex: 1 1 220px;
            padding: 0.4rem 0.6rem;
            font-size: 0.95rem;
        }
        .search-form select,
        .search-form button {
            padding: 0.4rem 0.6rem;
            font-size: 0.95rem;
        }
        .search-form button {
            cursor: pointer;
        }
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1rem;
        }
        .recipe-card {
            background: #ffffff;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .recipe-card h2 {
            font-size: 1.1rem;
            margin: 0 0 0.4rem;
        }
        .recipe-meta {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.4rem;
        }
        .recipe-card a {
            font-size: 0.9rem;
            text-decoration: none;
            color: #0066cc;
        }
    </style>
</head>
<body>

<h1>Recipes</h1>

<form method="get" action="recipes.php" class="search-form">
    <!-- Keyword search -->
    <input
        type="text"
        name="q"
        placeholder="Search by title, description or ingredient"
        value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>"
    >

    <!-- Sorting -->
    <select name="sort">
        <option value="time"  <?php if ($sort === 'time')  echo 'selected'; ?>>Shortest time first</option>
        <option value="alpha" <?php if ($sort === 'alpha') echo 'selected'; ?>>Alphabetically (A–Z)</option>
    </select>

    <button type="submit">Search</button>
</form>

<?php if (empty($recipes)): ?>
    <p>No recipes found. Try a different keyword.</p>
<?php else: ?>
    <div class="recipes-grid">
        <?php foreach ($recipes as $recipe): ?>
            <article class="recipe-card">
                <h2><?php echo htmlspecialchars($recipe['title']); ?></h2>

                <div class="recipe-meta">
                    <?php if (!empty($recipe['category_list'])): ?>
                        <div>Categories: <?php echo htmlspecialchars($recipe['category_list']); ?></div>
                    <?php endif; ?>
                    <div>Time: <?php echo (int)$recipe['total_time_minutes']; ?> minutes</div>
                    <div>Difficulty: <?php echo htmlspecialchars($recipe['difficulty']); ?></div>
                </div>

                <?php if (!empty($recipe['description'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                <?php endif; ?>

                <!-- details page (adjust path if your file is elsewhere) -->
                <a href="recipe.php?id=<?php echo (int)$recipe['id']; ?>">View full recipe</a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>
