<?php
// rev/11-20-2024
require(__DIR__ . "/../../../partials/nav.php");

// Ensure the user is logged in
is_logged_in(true);

// Retrieve the `id` parameter from the URL
$id = se($_GET, "id", null, false);
if (!$id) {
    flash("Invalid or missing ID.", "danger");
    exit(header("Location: data_list.php"));
}

$db = getDB();

// Verify that the entity exists and is not already deleted
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id AND is_deleted = 0");
$stmt->execute([":id" => $id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    flash("Entity not found or already deleted.", "danger");
    exit(header("Location: data_list.php"));
}

// Perform the soft delete
try {
    $stmt = $db->prepare("UPDATE MediaEntities SET is_deleted = 1 WHERE id = :id");
    $stmt->execute([":id" => $id]);
    flash("Entity deleted successfully!", "success");
} catch (PDOException $e) {
    error_log("Error deleting entity: " . var_export($e->errorInfo, true));
    flash("An error occurred while attempting to delete the entity.", "danger");
}

// Redirect back to the data list page
header("Location: data_list.php");
exit;
?>
