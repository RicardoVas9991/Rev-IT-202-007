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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = se($_POST, "title", $entity["title"], false);
    $description = se($_POST, "description", $entity["description"], false);
    $releaseDate = se($_POST, "release_date", $entity["release_date"], false);

    $stmt = $db->prepare("UPDATE MediaEntities SET title = :title, description = :description, release_date = :release_date WHERE id = :id");
    try {
        $stmt->execute([
            ":title" => $title,
            ":description" => $description,
            ":release_date" => $releaseDate,
            ":id" => $id
        ]);
        flash("Record updated successfully", "success");
    } catch (Exception $e) {
        flash("Error updating record: " . $e->getMessage(), "danger");
    }
}
?>

<div class="container">
    <h1>Edit Record</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" value="<?= se($entity, "title") ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" required><?= se($entity, "description") ?></textarea>
        </div>
        <div class="mb-3">
            <label for="release_date" class="form-label">Release Date</label>
            <input type="date" class="form-control" name="release_date" value="<?= se($entity, "release_date") ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
