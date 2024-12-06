<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

// Check if ID is provided via GET
$id = se($_GET, "id", -1, false);

// Validate the ID
if (!is_numeric($id) || $id <= 0) {
    flash("Invalid ID", "danger");
    header("Location: data_list.php");
    exit;
}

$db = getDB();

// Verify that the record exists and belongs to the current user
$stmt = $db->prepare("SELECT id FROM MediaEntities WHERE id = :id AND user_id = :user_id");
$stmt->execute([":id" => $id, ":user_id" => get_user_id()]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    flash("Record not found or you do not have permission to delete it.", "warning");
    header("Location: data_list.php");
    exit;
}

// Attempt to delete the record
$stmt = $db->prepare("DELETE FROM MediaEntities WHERE id = :id AND user_id = :user_id");
try {
    $stmt->execute([":id" => $id, ":user_id" => get_user_id()]);
    if ($stmt->rowCount() > 0) {
        flash("Record deleted successfully", "success");
    } else {
        flash("No record deleted. It may have already been removed.", "warning");
    }
} catch (Exception $e) {
    error_log("Error deleting record: " . $e->getMessage()); // Log the error
    flash("An unexpected error occurred while trying to delete the record.", "danger");
}

header("Location: data_list.php");
exit;
?>