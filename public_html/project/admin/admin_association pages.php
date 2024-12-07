<?php require(__DIR__ . "/../../../partials/nav.php"); 

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
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

function getAllUserAssociations($limit = 10, $offset = 0, $usernameFilter = "", $sort = "username ASC") {
    $db = getDB();
    $query = "SELECT um.id, u.username, me.title, me.description, COUNT(um.id) AS total 
              FROM UserMedia um 
              JOIN MediaEntities me ON um.media_id = me.id 
              JOIN Users u ON um.user_id = u.id 
              GROUP BY u.username, me.title";
    if ($usernameFilter) {
        $query .= " HAVING u.username LIKE :usernameFilter";
    }
    $query .= " ORDER BY $sort LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    if ($usernameFilter) {
        $stmt->bindValue(":usernameFilter", "%" . $usernameFilter . "%");
    }
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// rev/12-06-2024

?>
<h1>Your Associated Media</h1>
<form method="POST" action="admin_assign.php">
    <label for="entity">Entity (partial):</label>
    <input type="text" name="entity" id="entity" required>
    <label for="username">Username (partial):</label>
    <input type="text" name="username" id="username" required>
    <button type="submit">Search</button>
</form>
<p>Total Count: <?= $total ?></p>
<table>
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
                <a href="view.php?id=<?= $row['id'] ?>">View</a>
                <a href="delete_association.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
