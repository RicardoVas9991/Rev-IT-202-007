<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

// Fetch filter/sort parameters from GET request
$filterTitle = se($_GET, "filter_title", "", false);
$sortField = se($_GET, "sort_field", "title", false);
$sortOrder = se($_GET, "sort_order", "ASC", false);
$limit = max(1, min(se($_GET, "limit", 10, false), 100)); // Default to 10, range 1-100

$db = getDB();
$query = "SELECT * FROM MediaEntities WHERE 1=1";
$params = [];

// Filter by title
if ($filterTitle) {
    $query .= " AND title LIKE :filter_title";
    $params[":filter_title"] = "%$filterTitle%";
}

// Add sorting and limit
$query .= " ORDER BY $sortField $sortOrder LIMIT $limit";

$stmt = $db->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container">
    <h1>Data List</h1>
    <form method="GET" class="mb-3">
        <input type="text" name="filter_title" placeholder="Filter by Title" value="<?= se($filterTitle) ?>">
        <select name="sort_field">
            <option value="title" <?= $sortField === "title" ? "selected" : "" ?>>Title</option>
            <option value="release_date" <?= $sortField === "release_date" ? "selected" : "" ?>>Release Date</option>
        </select>
        <select name="sort_order">
            <option value="ASC" <?= $sortOrder === "ASC" ? "selected" : "" ?>>Ascending</option>
            <option value="DESC" <?= $sortOrder === "DESC" ? "selected" : "" ?>>Descending</option>
        </select>
        <input type="number" name="limit" min="1" max="100" value="<?= se($limit) ?>">
        <button type="submit" class="btn btn-primary">Apply</button>
    </form>

    <?php if (count($results) === 0): ?>
        <p>No results available</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach (array_merge($results) as $item): ?>
                <li class="list-group-item">
                    <h5><?= se($item, "title") ?></h5>
                    <p><?= se($item, "description") ?></p>
                    <a href="view_data.php?id=<?= se($item, "id") ?>" class="btn btn-info">View</a>
                    <?php if (has_role("Admin")): ?>
                        <a href="edit_data.php?id=<?= se($item, "id") ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_data.php?id=<?= se($item, "id") ?>" class="btn btn-danger">Delete</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
