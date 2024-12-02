<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

$id = se($_GET, "id", null, false);
if (!$id) {
    flash("No ID provided for editing", "danger");
    exit(header("Location: view_data.php"));
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id");
$stmt->execute([":id" => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    flash("Record not found", "danger");
    die(header("Location: view_data.php"));
}
?>
<div class="container">
    <h1>Edit Record</h1>
    <form method="POST" action="update_data.php">
        <input type="hidden" name="id" value="<?php echo se($data, 'id'); ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" value="<?php echo se($data, 'title'); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" required><?php echo se($data, 'description'); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="release_date" class="form-label">Release Date</label>
            <input type="date" class="form-control" name="release_date" value="<?php echo se($data, 'release_date'); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
