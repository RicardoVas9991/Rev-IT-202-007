<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

// Check if ID is provided via GET or POST
$id = se($_GET, "id", -1, false);
if ($id <= 0) {
    flash("Invalid ID", "danger");
    header("Location: data_list.php");
    exit;
}

$db = getDB();
$stmt = $db->prepare("DELETE FROM MediaEntities WHERE id = :id AND user_id = :user_id");
try {
    $stmt->execute([":id" => $id, ":user_id" => get_user_id()]);
    flash("Record deleted successfully", "success");
} catch (Exception $e) {
    flash("Error deleting record: " . $e->getMessage(), "danger");
}

header("Location: data_list.php");
exit;
?>

