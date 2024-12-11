<?php require(__DIR__ . "/../../../partials/nav.php"); 
is_logged_in(true);
// rev/12-08-2024

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    exit(header("Location: $BASE_PATH" . "home.php"));
}

$userId = get_user_id();
$limit = $_GET['limit'] ?? 10; // Default 10
$limit = min(max((int)$limit, 1), 100); // Enforce range 1-100
$filter = $_GET['filter'] ?? "";
$sort = $_GET['sort'] ?? "title ASC";
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

$data = getAllUserAssociations($userId, $limit, $offset, $filter, $sort);
$total = count($data); // rev/12-06-2024
function getAllUserAssociations($userId, $limit = 10, $offset = 0, $usernameFilter = "", $sort = "username ASC") {
    $db = getDB();
    $query = "SELECT 
                  me.id AS media_entity_id,
                  u.username,
                  me.title,
                  me.description,
                  me.release_date,
                  COUNT(um.id) AS total 
              FROM UserMediaAssociations um 
              JOIN MediaEntities me ON um.media_entity_id = me.id 
              JOIN Users u ON um.user_id = u.id 
              WHERE um.user_id = :userId ";
              
    // Add filter if provided
    if ($usernameFilter) {
        $query .= "AND u.username LIKE :usernameFilter ";
    }
    $query .= "GROUP BY me.id,u.username, me.title, me.description, me.release_date  ";
    $query .= "ORDER BY $sort ";
    $query .= "LIMIT $limit OFFSET $offset";

    $stmt = $db->prepare($query);

    // Bind parameters
    $stmt->bindValue(":userId", $userId, PDO::PARAM_INT);
    if ($usernameFilter) {
        $stmt->bindValue(":usernameFilter", "%" . $usernameFilter . "%");
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// rev/12-06-2024

?>

<div class="container">
<h1>Your Admin Associated Media</h1>
    <form method="POST" action="admin_association.php">
        <label for="entity">Entity (partial):</label>
            <input type="text" name="entity" id="entity" required>
            <label for="username">Name (partial):</label>
                <input type="text" name="username" id="username" required>
                    <button type="search">Search</button>
    </form>
<p>Total Count: <?= $total ?></p>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Release Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['release_date']) ?></td>
            <td>
            <a href="view_data.php?id=<?= se($row, "media_entity_id") ?>" class="btn btn-info">View</a>
            <a href="delete_association.php?id=<?= se($row, "media_entity_id") ?>" class="btn btn-danger" 
                onclick="return confirm('Are you sure?')">Delete</a>
        </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($data)): ?>
        <tr>
            <td colspan="4">No results available.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<a href="remove_all.php" onclick="return confirm('Remove all associations?')">Remove All Associations</a>
</div>


<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>