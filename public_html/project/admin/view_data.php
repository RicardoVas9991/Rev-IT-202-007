<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    exit(header("Location: $BASE_PATH" . "home.php"));
}

$id = se($_GET, "id", -1, false);
if ($id <= 0) {
    flash("Invalid ID", "danger");
    header("Location: data_list.php");
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id");
$stmt->execute([":id" => $id]);
$entity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entity) {
    flash("No entity found with that ID", "danger");
    header("Location: data_list.php");
    exit;
}
?>

<div class="container">
    <h1>View Media Record</h1>
    <a href="data_list.php" class="btn btn-secondary mb-3">Back to List</a>
    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td><?php echo se($entity, "id"); ?></td>
        </tr>
        <tr>
            <th>Title</th>
            <td><?php echo se($entity, "title"); ?></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><?php echo se($entity, "description"); ?></td>
        </tr>
        <tr>
            <th>Release Date</th>
            <td><?php echo se($entity, "release_date"); ?></td>
        </tr>
    </table>
    <a href="edit_data.php?id=<?php echo se($entity, 'id'); ?>" class="btn btn-warning">Edit</a>
    <a href="delete_data.php?id=<?php echo se($entity, 'id'); ?>" class="btn btn-danger" 
       onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>