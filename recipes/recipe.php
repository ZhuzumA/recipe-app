<?php
// recipes/recipe.php

require_once __DIR__ . '/../inc/db.php';   // connect to DB + session

// 1) Get and validate recipe id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<h2 style="color:red;">Invalid recipe. Please go back and choose a recipe.</h2>');
}
$recipe_id = (int)$_GET['id'];

$recipe      = null;
$ingredients = [];
$steps       = [];
$images      = [];

// 2) Load main recipe info
try {
    $sql_recipe = "
        SELECT
            id,
            title,
            description,
            difficulty,
            total_time_minutes
        FROM recipes
        WHERE id = :id
    ";
    $stmt = $pdo->prepare($sql_recipe);
    $stmt->execute([':id' => $recipe_id]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        die('<h2 style="color:red;">Recipe not found.</h2>');
    }

    // 3) Ingredients
    try {
        $sql_ing = "
            SELECT
                ing.name,
                ri.quantity,
                ri.unit
            FROM recipe_ingredients ri
            JOIN ingredients ing ON ing.id = ri.ingredient_id
            WHERE ri.recipe_id = :id
            ORDER BY ri.id
        ";
        $stmt_ing = $pdo->prepare($sql_ing);
        $stmt_ing->execute([':id' => $recipe_id]);
        $ingredients = $stmt_ing->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Ingredients query failed: '.$e->getMessage());
    }

    // 4) Steps
    try {
        $sql_steps = "
            SELECT step_number, instruction
            FROM recipe_steps
            WHERE recipe_id = :id
            ORDER BY step_number
        ";
        $stmt_steps = $pdo->prepare($sql_steps);
        $stmt_steps->execute([':id' => $recipe_id]);
        $steps = $stmt_steps->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Steps query failed: '.$e->getMessage());
    }

    // 5) Images (optional table – safe to fail)
    try {
        $sql_images = "
            SELECT image_url, caption
            FROM recipe_images
            WHERE recipe_id = :id
            ORDER BY id
        ";
        $stmt_img = $pdo->prepare($sql_images);
        $stmt_img->execute([':id' => $recipe_id]);
        $images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Images query failed: '.$e->getMessage());
    }

} catch (PDOException $e) {
    error_log('Recipe query failed: '.$e->getMessage());
    die('<h2 style="color:red;">Error loading recipe.</h2>');
}

include __DIR__ . '/../inc/header.php';
?>

<article class="recipe-detail">
    <h1><?php echo htmlspecialchars($recipe['title'], ENT_QUOTES); ?></h1>

    <p>
        Difficulty:
        <?php echo htmlspecialchars($recipe['difficulty'], ENT_QUOTES); ?>
        |
        Time:
        <?php echo (int)$recipe['total_time_minutes']; ?> minutes
    </p>

    <?php if (!empty($recipe['description'])): ?>
        <p><?php echo nl2br(htmlspecialchars($recipe['description'], ENT_QUOTES)); ?></p>
    <?php endif; ?>

    <?php if (!empty($images)): ?>
        <section class="recipe-images">
            <?php foreach ($images as $img): ?>
                <figure>
                    <img
                        src="<?php echo htmlspecialchars($img['image_url'], ENT_QUOTES); ?>"
                        alt="<?php echo htmlspecialchars($recipe['title'], ENT_QUOTES); ?>"
                    >
                    <?php if (!empty($img['caption'])): ?>
                        <figcaption><?php echo htmlspecialchars($img['caption'], ENT_QUOTES); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <?php if (!empty($ingredients)): ?>
        <section>
            <h2>Ingredients</h2>
            <ul>
                <?php foreach ($ingredients as $ing): ?>
                    <li>
                        <?php
                            $qty  = trim($ing['quantity'] ?? '');
                            $unit = trim($ing['unit'] ?? '');
                            $parts = [];
                            if ($qty !== '')  { $parts[] = htmlspecialchars($qty, ENT_QUOTES); }
                            if ($unit !== '') { $parts[] = htmlspecialchars($unit, ENT_QUOTES); }
                            $prefix = implode(' ', $parts);
                            if ($prefix !== '') { $prefix .= ' '; }
                            echo $prefix . htmlspecialchars($ing['name'], ENT_QUOTES);
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if (!empty($steps)): ?>
        <section>
            <h2>Steps</h2>
            <ol>
                <?php foreach ($steps as $step): ?>
                    <li><?php echo nl2br(htmlspecialchars($step['instruction'], ENT_QUOTES)); ?></li>
                <?php endforeach; ?>
            </ol>
        </section>
    <?php endif; ?>

    <p><a href="recipes.php">← Back to all recipes</a></p>
</article>

<?php include __DIR__ . '/../inc/footer.php'; ?>
