<?php include "inc/header.php"; ?>

<div class="card">
    <h1>This Is Our First Recipe WebSite</h1>
    <p>Your place to find and save great recipes.</p>

    <form action="recipes/recipes.php" method="get" class="searchForm">
        <!-- Free text -->
        <input
            type="text"
            name="q"
            placeholder="Search..."
        />

        <!-- Difficulty filter -->
        <select name="difficulty">
            <option value="">Any Difficulty</option>
            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
        </select>

        <!-- Max time -->
        <input
            type="number"
            name="max_time"
            placeholder="Max minutes"
            min="1"
        />

        <!-- Sorting options -->
        <select name="sort">
            <option value="">Sort By</option>
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="az">A–Z</option>
            <option value="za">Z–A</option>
            <option value="easy_first">Difficulty: Easy → Hard</option>
            <option value="hard_first">Difficulty: Hard → Easy</option>
            <option value="time_low">Time: Low → High</option>
            <option value="time_high">Time: High → Low</option>
        </select>

        <button class="btn">Search</button>
    </form>
</div>

<?php include "inc/footer.php"; ?>
