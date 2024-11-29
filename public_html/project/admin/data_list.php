<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/11-20-2024

$limit = min(max((int)se($_GET, "limit", 10, false), 1), 100);
$page = max((int)se($_GET, "page", 1, false), 1);
$offset = ($page - 1) * $limit;

$db = getDB();
$filter = "%" . se($_GET, "filter", "", false) . "%";

$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE title LIKE :filter LIMIT :limit OFFSET :offset");
$stmt->bindValue(":filter", $filter, PDO::PARAM_STR);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($results):
    foreach ($results as $r):
?>
<!DOCTYPE html>
<html lang="en">
        <div>
            <h3><?php se($r, "title"); ?></h3>
            <p><?php se($r, "description"); ?></p>
            <a href="view_data.php?id=<?php se($r, "id"); ?>">View</a>
            <a href="edit_data.php?id=<?php se($r, "id"); ?>">Edit</a>
            <a href="delete_data.php?id=<?php se($r, "id"); ?>">Delete</a>
        </div>
<?php
    endforeach;
else:
    echo "<p>No results available</p>";
endif;
?>
