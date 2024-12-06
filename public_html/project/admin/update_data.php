<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

$id = se($_POST, "id", null, false);
$title = se($_POST, "title", null, false);
$description = se($_POST, "description", null, false);
$releaseDate = se($_POST, "release_date", null, false);

if (!$id || !$title || !$description) {
    flash("All fields are required", "danger");
    die(header("Location: edit_data.php?id=$id"));
}

$db = getDB();
$stmt = $db->prepare("UPDATE MediaEntities SET title = :title, description = :description, release_date = :release_date, modified = CURRENT_TIMESTAMP WHERE id = :id");

try {
    $stmt->execute([
        ":id" => $id,
        ":title" => $title,
        ":description" => $description,
        ":release_date" => $releaseDate
    ]);
    flash("Record updated successfully", "success");
} catch (Exception $e) {
    flash("Error updating record: " . $e->getMessage(), "danger");
}

header("Location: view_data.php");
exit;
?>
