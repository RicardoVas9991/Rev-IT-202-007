<?php require(__DIR__ . "/../../../partials/nav.php"); is_logged_in(true);

$userId = get_user_id();
$limit = $_GET['limit'] ?? 10; // Default 10
$limit = min(max((int)$limit, 1), 100); // Enforce range 1-100
$filter = $_GET['filter'] ?? "";
$sort = $_GET['sort'] ?? "title ASC";
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

$data = getUserAssociations($userId, $limit, $offset, $filter, $sort);
$total = count($data); // rev/12-06-2024

function getUserAssociations($userId, $limit = 10, $offset = 0, $filter = "", $sort = "title ASC") {
    $db = getDB();
    $query = "SELECT um.id, me.title, me.description, me.release_date 
              FROM UserMedia um 
              JOIN MediaEntities me ON um.media_id = me.id 
              WHERE um.user_id = :user_id";
    if ($filter) {
        $query .= " AND me.title LIKE :filter";
    }
    $query .= " ORDER BY $sort LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    if ($filter) {
        $stmt->bindValue(":filter", "%" . $filter . "%");
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// rev/12-06-2024

?>
<h1>Your Associated Media</h1>
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
