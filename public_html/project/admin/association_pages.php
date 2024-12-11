<?php 
require(__DIR__ . "/../../../partials/nav.php"); 
is_logged_in(true);
// rev/12-08-2024

$userId = get_user_id();
$limit = $_GET['limit'] ?? 10;
$limit = min(max((int)$limit, 1), 100);
$filter = $_GET['filter'] ?? "";
$sort = $_GET['sort'] ?? "title ASC";
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mediaTitle = $_POST['media_title'] ?? '';
    $description = $_POST['description'] ?? '';
    $releaseDate = $_POST['release_date'] ?? '';

    // Validate input
    if (!empty($mediaTitle) && !empty($description) && !empty($releaseDate)) {
        $db = getDB();
        $query = "INSERT INTO MediaEntities (title, description, release_date) VALUES (:title, :description, :release_date)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(":title", htmlspecialchars($mediaTitle), PDO::PARAM_STR);
        $stmt->bindValue(":description", htmlspecialchars($description), PDO::PARAM_STR);
        $stmt->bindValue(":release_date", $releaseDate, PDO::PARAM_STR);

        try {
            $stmt->execute();
            $mediaEntityId = $db->lastInsertId();

            // Link the media entity to the user - rev/12-10-2024
            $query = "INSERT INTO UserMediaAssociations (user_id, media_entity_id) VALUES (:user_id, :media_entity_id)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":media_entity_id", $mediaEntityId, PDO::PARAM_INT);
            $stmt->execute();

            flash("Association created successfully!", "success");
        } catch (Exception $e) {
            flash("Error creating association: " . $e->getMessage(), "danger");
        }
    } else {
        flash("All fields are required.", "warning");
    }
}

// Fetch associations
$data = getUserAssociations($userId, $limit, $offset, $filter, $sort);
$total = count($data);

function getUserAssociations($userId, $limit = 10, $offset = 0, $filter = "", $sort = "title ASC") {
    $db = getDB();
    $query = "SELECT 
                  um.id AS association_id, 
                  me.title, 
                  me.description, 
                  me.release_date 
              FROM UserMediaAssociations um 
              JOIN MediaEntities me ON um.media_entity_id = me.id 
              WHERE um.user_id = :user_id";
    if ($filter) { // rev/12-05-2024
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
?>

<div class="container">
<h1>Your Associated Media</h1>
<form method="POST" action="user_association.php">
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
                <a href="view.php?id=<?= htmlspecialchars($row['association_id']) ?>" class="btn btn-info">View</a>
                <a href="delete_association.php?id=<?= htmlspecialchars($row['association_id']) ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
<!-- <a href="remove_all.php" onclick="return confirm('Remove all associations?')">Remove All Associations</a> 
 see admin_association_pages for access to remove_all -->
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
