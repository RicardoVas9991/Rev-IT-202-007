<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

// Check if ID is provided via GET or POST
$id = se($_GET, "id", null, false) ?? se($_POST, "id", null, false);
if (!$id) {
    flash("No ID provided for deletion", "danger");
    exit(header("Location: view_data.php"));
}

$db = getDB();
$stmt = $db->prepare("DELETE FROM MediaEntities WHERE id = :id");
try {
    $stmt->execute([":id" => $id]);
    flash("Record deleted successfully", "success");
} catch (Exception $e) {
    flash("Error deleting record: " . $e->getMessage(), "danger");
}

header("Location: view_data.php");
exit;
?>
