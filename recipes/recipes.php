<?php
// recipes/recipes.php

// show errors while developing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB connection
require_once __DIR__ . '/../inc/db.php';

// Read input
$q          = trim($_GET['q'] ?? '');
$difficulty = trim($_GET['difficulty'] ?? '');
$maxTime    = trim($_GET['max_time'] ?? '');
$sort       = $_GET['sort'] ?? '';

// ---- BUILD SQL ----
$sql = "
    SELECT DISTINCT
        r.id,
        r.title,
        r.description,
        r.difficulty,
        r.total_time_minutes
    FROM recipes r
    LEFT JOIN recipe_ingredients ri ON ri.recipe_id = r.id
    LEFT JOIN ingredients ing       ON ing.id = ri.ingredient_id
    WHERE 1 = 1
";

$params = [];

// Text search  🔧 FIXED HERE
if ($q !== '') {
    $sql .= " AND (
        r.title       LIKE :q_title
        OR r.description LIKE :q_desc
        OR ing.name   LIKE :q_ing
    )";

    $like = '%' . $q . '%';
    $params[':q_title'] = $like;
    $params[':q_desc']  = $like;
    $params[':q_ing']   = $like;
}

// Difficulty filter
if ($difficulty !== '') {
    $sql .= " AND r.difficulty = :difficulty";
    $params[':difficulty'] = $difficulty;
}

// Max time filter
if ($maxTime !== '' && is_numeric($maxTime)) {
    $sql .= " AND r.total_time_minutes <= :max_time";
    $params[':max_time'] = (int)$maxTime;
}

// Sorting
switch ($sort) {
    case 'newest':
        $sql .= " ORDER BY r.id DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY r.id ASC";
        break;
    case 'az':
        $sql .= " ORDER BY r.title ASC";
        break;
    case 'za':
        $sql .= " ORDER BY r.title DESC";
        break;
    case 'easy_first':
        $sql .= " ORDER BY FIELD(r.difficulty,'Easy','Medium','Hard')";
        break;
    case 'hard_first':
        $sql .= " ORDER BY FIELD(r.difficulty,'Hard','Medium','Easy')";
        break;
    case 'time_low':
        $sql .= " ORDER BY r.total_time_minutes ASC, r.title ASC";
        break;
    case 'time_high':
        $sql .= " ORDER BY r.total_time_minutes DESC, r.title ASC";
        break;
    default:
        $sql .= " ORDER BY r.title ASC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Query failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
}

include __DIR__ . '/../inc/header.php';
?>

<h1>All Recipes</h1>

<form method="get" action="recipes.php" class="search-form">
    <input
        type="text"
        name="q"
        placeholder="Search by title, description or ingredient"
        value="<?php echo htmlspecialchars($q, ENT_QUOTES); ?>"
    >

    <select name="difficulty">
        <option value="" <?php if ($difficulty === '') echo 'selected'; ?>>Any Difficulty</option>
        <option value="Easy"   <?php if ($difficulty === 'Easy')   echo 'selected'; ?>>Easy</option>
        <option value="Medium" <?php if ($difficulty === 'Medium') echo 'selected'; ?>>Medium</option>
        <option value="Hard"   <?php if ($difficulty === 'Hard')   echo 'selected'; ?>>Hard</option>
    </select>

    <input
        type="number"
        name="max_time"
        placeholder="Max minutes"
        min="1"
        value="<?php echo htmlspecialchars($maxTime, ENT_QUOTES); ?>"
    >

    <select name="sort">
        <option value=""        <?php if ($sort === '')        echo 'selected'; ?>>Sort By</option>
        <option value="newest"  <?php if ($sort === 'newest')  echo 'selected'; ?>>Newest First</option>
        <option value="oldest"  <?php if ($sort === 'oldest')  echo 'selected'; ?>>Oldest First</option>
        <option value="az"      <?php if ($sort === 'az')      echo 'selected'; ?>>A–Z</option>
        <option value="za"      <?php if ($sort === 'za')      echo 'selected'; ?>>Z–A</option>
        <option value="easy_first" <?php if ($sort === 'easy_first') echo 'selected'; ?>>
            Difficulty: Easy → Hard
        </option>
        <option value="hard_first" <?php if ($sort === 'hard_first') echo 'selected'; ?>>
            Difficulty: Hard → Easy
        </option>
        <option value="time_low"  <?php if ($sort === 'time_low')  echo 'selected'; ?>>
            Time: Low → High
        </option>
        <option value="time_high" <?php if ($sort === 'time_high') echo 'selected'; ?>>
            Time: High → Low
        </option>
    </select>

    <button type="submit">Search</button>
</form>

<hr>

<?php if (empty($recipes)): ?>
    <p>No recipes found.</p>
<?php else: ?>
    <ul class="recipe-list">
        <?php foreach ($recipes as $recipe): ?>
            <li class="recipe-card">
                <h2>
                    <a href="recipe.php?id=<?php echo (int)$recipe['id']; ?>">
                        <?php echo htmlspecialchars($recipe['title'], ENT_QUOTES); ?>
                    </a>
                </h2>

                <?php if (!empty($recipe['description'])): ?>
                    <p><?php echo htmlspecialchars(substr($recipe['description'], 0, 150), ENT_QUOTES); ?>...</p>
                <?php endif; ?>

                <p>
                    Difficulty: <?php echo htmlspecialchars($recipe['difficulty'], ENT_QUOTES); ?>
                    |
                    Time: <?php echo (int)$recipe['total_time_minutes']; ?> min
                </p>

                <p><a href="recipe.php?id=<?php echo (int)$recipe['id']; ?>">View full recipe »</a></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php include __DIR__ . '/../inc/footer.php'; ?>
