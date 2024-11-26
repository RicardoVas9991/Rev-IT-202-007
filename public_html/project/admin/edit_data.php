<?php
// rev/11-20-2024
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

$id = se($_GET, "id", null, false);
if (!$id) {
    flash("Invalid ID", "danger");
    die(header("Location: data_list.php"));
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id");
$stmt->execute([":id" => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    flash("Entity not found", "danger");
    die(header("Location: data_list.php"));
}

if (isset($_POST["save"])) {
    $title = se($_POST, "title", "", false);
    $description = se($_POST, "description", "", false);
    $release_date = se($_POST, "release_date", "", false);

    try {
        $stmt = $db->prepare("UPDATE MediaEntities SET title = :title, description = :description, 
                              release_date = :release_date WHERE id = :id");
        $stmt->execute([
            ":title" => $title,
            ":description" => $description,
            ":release_date" => $release_date,
            ":id" => $id
        ]);
        flash("Entity updated successfully!", "success");
        // Reload the updated entity
        $stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id");
        $stmt->execute([":id" => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        flash("An error occurred while saving the changes.", "danger");
    }
}
?>
<h3>Edit Entity</h3>
<form method="POST">
    <label>Title:</label>
    <input type="text" name="title" value="<?php se($data, "title"); ?>" required />
    <label>Description:</label>
    <textarea name="description" required><?php se($data, "description"); ?></textarea>
    <label>Release Date:</label>
    <input type="date" name="release_date" value="<?php se($data, "release_date"); ?>" required />
    <button type="submit" name="save">Save Changes</button>
</form>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
