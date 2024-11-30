<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/11-20-2024

// Pagination variables
$limit = min(max((int)se($_GET, "limit", 10, false), 1), 100); // Limit between 1 and 100
$page = max((int)se($_GET, "page", 1, false), 1); // Page must be >= 1
$offset = ($page - 1) * $limit;

// Filter input
$filter = "%" . se($_GET, "filter", "", false) . "%";

// Fetch data
$db = getDB();
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE title LIKE :filter AND is_deleted = 0 LIMIT :limit OFFSET :offset");
$stmt->bindValue(":filter", $filter, PDO::PARAM_STR);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total rows for pagination
$totalStmt = $db->prepare("SELECT COUNT(*) as total FROM MediaEntities WHERE title LIKE :filter AND is_deleted = 0");
$totalStmt->execute([":filter" => $filter]);
$totalRows = (int)$totalStmt->fetch(PDO::FETCH_ASSOC)["total"];
$totalPages = ceil($totalRows / $limit);
?>
<div class="container-fluid">
    <h1>Data List</h1>
    <form method="GET" class="form-inline">
        <input type="text" name="filter" placeholder="Search by title" value="<?php se($_GET, 'filter'); ?>" />
        <button type="submit">Search</button>
    </form>
    <hr />
    <?php if ($results): ?>
        <?php foreach ($results as $r): ?>
            <div class="card">
                <h3><?php se($r, "title"); ?></h3>
                <p><?php se($r, "description"); ?></p>
                <p>Release Date: <?php se($r, "release_date"); ?></p>
                <a href="view_data.php?id=<?php se($r, "id"); ?>">View</a>
                <a href="edit_data.php?id=<?php se($r, "id"); ?>">Edit</a>
                <a href="delete_data.php?id=<?php se($r, "id"); ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
            </div>
            <hr />
        <?php endforeach; ?>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
    <nav>
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li><a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&filter=<?php se($_GET, 'filter'); ?>">Previous</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li <?php if ($i === $page) echo 'class="active"'; ?>>
                    <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&filter=<?php se($_GET, 'filter'); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <li><a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&filter=<?php se($_GET, 'filter'); ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
