<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity = $_POST['entity'] ?? '';
    $username = $_POST['username'] ?? '';

    if (!empty($entity) && !empty($username)) {
        // Example: Logic to assign an entity to a user
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO UserMedia (user_id, media_id) 
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
