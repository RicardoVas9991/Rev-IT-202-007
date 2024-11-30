<?php
// rev/11-20-2024
require(__DIR__ . "/../../../partials/nav.php");

// Ensure the user is logged in
is_logged_in(true);

// Retrieve the `id` parameter from the URL
$id = se($_GET, "id", null, false);
if (!$id) {
    flash("Invalid or missing ID", "danger");
    exit(header("Location: data_list.php"));
}

// Fetch the entity details from the database
$db = getDB();
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id");
$stmt->execute([":id" => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// If the entity doesn't exist, redirect to the list page
if (!$data) {
    flash("Entity not found", "danger");
    exit(header("Location: data_list.php"));
}

// Handle the form submission for saving updates
if (isset($_POST["save"])) {
    $title = se($_POST, "title", "", false);
    $description = se($_POST, "description", "", false);
    $release_date = se($_POST, "release_date", "", false);

    if ($title && $description && $release_date) {
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

            // Refresh the page to reflect updated data
            header("Location: edit_data.php?id=" . $id);
            exit;
        } catch (PDOException $e) {
            error_log("Error updating entity: " . var_export($e->errorInfo, true));
            flash("An error occurred while saving the changes.", "danger");
        }
    } else {
        flash("All fields are required.", "warning");
    }
}
?>

<div class="container-fluid">
    <h3>Edit Entity</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php se($data, "title"); ?>" required />
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" class="form-control" rows="5" required><?php se($data, "description"); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="release_date" class="form-label">Release Date:</label>
            <input type="date" id="release_date" name="release_date" class="form-control" value="<?php se($data, "release_date"); ?>" required />
        </div>
        <button type="submit" name="save" class="btn btn-primary">Save Changes</button>
        <a href="data_list.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
// Include flash messages
require_once(__DIR__ . "/../../../partials/flash.php");
?>
