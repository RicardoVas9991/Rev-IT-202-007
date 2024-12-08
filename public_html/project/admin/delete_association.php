<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

if (!has_role("Admin")) {
    flash("You do not have permission to access this page.", "danger");
    header("Location: home.php");
    exit;
}

$assocId = se($_POST, "assoc_id", -1, false);
if ($assocId > 0) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM UserMediaAssociations WHERE id = :id");
    try {
        $stmt->execute([":id" => $assocId]);
        flash("Association deleted successfully.", "success");
    } catch (Exception $e) {
        flash("Error deleting association: " . $e->getMessage(), "danger");
    }
} else {
    flash("Invalid association ID.", "danger");
}

header("Location: admin_association.php");
exit;
?>
