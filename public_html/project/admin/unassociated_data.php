<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-05-2024

// Fetch unassociated entities from the database
$db = getDB();

// Modify the query to match your schema
$query = "SELECT * FROM MediaEntities WHERE user_id IS NULL";
$stmt = $db->prepare($query);

try {
    $stmt->execute();
    $unassociated = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    flash("Error fetching unassociated entities: " . $e->getMessage(), "danger");
    $unassociated = [];
}
?>

<div class="container">
    <h1>Unassociated Media Entities</h1>
    <?php if (count($unassociated) === 0): ?>
        <div class="alert alert-info">No unassociated entities found.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>API ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Release Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unassociated as $entity): ?>
                    <tr>
                        <td><?php echo se($entity, "id"); ?></td>
                        <td><?php echo se($entity, "api_id"); ?></td>
                        <td><?php echo se($entity, "title"); ?></td>
                        <td><?php echo se($entity, "description"); ?></td>
                        <td><?php echo se($entity, "release_date"); ?></td>
                        <td>
                            <!-- Action buttons like "Edit" or "Delete" -->
                            <a href="edit_data.php?id=<?php echo se($entity, 'id'); ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_data.php?id=<?php echo se($entity, 'id'); ?>" class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this entity?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
