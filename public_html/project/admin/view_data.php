<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

$db = getDB();
$stmt = $db->query("SELECT * FROM MediaEntities");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
