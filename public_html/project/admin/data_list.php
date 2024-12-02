<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

// Fetch all data from the database
$db = getDB();
$stmt = $db->query("SELECT id, title, description, release_date, created FROM MediaEntities ORDER BY created DESC");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Data List</h1>
    <a href="data_creation.php" class="btn btn-success mb-3">Create New Record</a>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Release Date</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo se($row, "id"); ?></td>
                        <td><?php echo se($row, "title"); ?></td>
                        <td><?php echo se($row, "description"); ?></td>
                        <td><?php echo se($row, "release_date"); ?></td>
                        <td><?php echo se($row, "created"); ?></td>
                        <td>
                            <a href="edit_data.php?id=<?php echo se($row, 'id'); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_data.php?id=<?php echo se($row, 'id'); ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                            <a href="view_data.php?id=<?php echo se($row, 'id'); ?>" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No data available</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
