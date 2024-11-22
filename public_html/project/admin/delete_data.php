// rev/11-20-2024
<?php
require_once(__DIR__ . "/../../partials/nav.php");
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

// Perform soft delete
try {
    $stmt = $db->prepare("UPDATE MediaEntities SET is_deleted = 1 WHERE id = :id");
    $stmt->execute([":id" => $id]);
    flash("Entity deleted successfully!", "success");
    die(header("Location: data_list.php"));
} catch (PDOException $e) {
    flash("An error occurred while deleting the entity.", "danger");
}