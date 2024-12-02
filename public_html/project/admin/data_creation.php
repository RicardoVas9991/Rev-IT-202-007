<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = se($_POST, "title", null, false);
    $description = se($_POST, "description", null, false);
    $releaseDate = se($_POST, "release_date", null, false);

    if (!$title || !$description) {
        flash("Title and Description are required", "danger");
    } else {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO MediaEntities (title, description, release_date, created) VALUES (:title, :description, :release_date, CURRENT_TIMESTAMP)");

        try {
            $stmt->execute([
                ":title" => $title,
                ":description" => $description,
                ":release_date" => $releaseDate
            ]);
            flash("Record created successfully", "success");
            header("Location: view_data.php");
            exit;
        } catch (Exception $e) {
            flash("Error creating record: " . $e->getMessage(), "danger");
        }
    }
}
?>
<div class="container">
    <h1>Create New Record</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="release_date" class="form-label">Release Date</label>
            <input type="date" class="form-control" name="release_date">
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
