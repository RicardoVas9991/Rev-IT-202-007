<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/11-20-2024

// Validate ID
$id = se($_GET, "id", null, false);
if (!$id) {
    flash("Invalid ID", "danger");
    exit(header("Location: data_list.php"));
}

// Fetch the entity
$db = getDB();
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id AND is_deleted = 0");
$stmt->execute([":id" => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    flash("Entity not found", "danger");
    exit(header("Location: data_list.php"));
}
?>
<div class="container-fluid">
    <h1>View Entity</h1>
    <div class="card">
        <div class="card-header">
            <h3><?php se($data, "title"); ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Description:</strong></p>
            <p><?php se($data, "description"); ?></p>
            <p><strong>Release Date:</strong> <?php se($data, "release_date"); ?></p>
        </div>
        <div class="card-footer">
            <a href="edit_data.php?id=<?php se($data, 'id'); ?>" class="btn btn-primary">Edit</a>
            <a href="delete_data.php?id=<?php se($data, 'id'); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this entity?')">Delete</a>
            <a href="data_list.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
