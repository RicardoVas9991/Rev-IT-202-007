<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

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
    <h1>Media Records</h1>
    <a href="data_creation.php" class="btn btn-success mb-3">Add New Record</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Release Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?php echo se($row, "id"); ?></td>
                    <td><?php echo se($row, "title"); ?></td>
                    <td><?php echo se($row, "description"); ?></td>
                    <td><?php echo se($row, "release_date"); ?></td>
                    <td>
                        <a href="edit_data.php?id=<?php echo se($row, 'id'); ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_data.php?id=<?php echo se($row, 'id'); ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>