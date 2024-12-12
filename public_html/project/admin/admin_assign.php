<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    exit(header("Location: $BASE_PATH" . "home.php"));
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity = $_POST['entity'] ?? '';
    $username = $_POST['username'] ?? '';

    if (!empty($entity) && !empty($username)) {
        // - rev/12-05-2024
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO UserMediaAssociations (user_id, media_entity_id) 
                              VALUES ((SELECT id FROM Users WHERE username = :username), 
                                      (SELECT id FROM MediaEntities WHERE title = :entity))");
        try {
            $stmt->execute([":username" => $username, ":entity" => $entity]);
            flash("Assignment successful!", "success");
        } catch (Exception $e) {
            flash("Error: " . $e->getMessage(), "danger");
        }
    } else {
        flash("Please fill in both fields.", "warning");
    }
}
?>
<h1>Assign Media to User</h1>
<form method="POST">
    <label for="entity">Entity:</label>
    <input type="text" id="entity" name="entity" required>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <button type="submit">Assign</button>
</form>


<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
