<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-05-2024

if (!has_role("Admin")) { // Ensure only admins access this page
    flash("You do not have permission to access this page.", "danger");
    header("Location: home.php");
    exit;
}

// Fetch users and unassociated media entities
$db = getDB();

// Fetch all users
$userStmt = $db->prepare("SELECT id, username FROM Users ORDER BY username ASC");
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
$limit = $_GET['limit'] ?? 10;
$limit = max((int)$limit, 25);


// Fetch unassociated media entities
$mediaStmt = $db->prepare("SELECT id, title FROM MediaEntities WHERE id NOT IN (
    SELECT media_entity_id FROM UserMediaAssociations
)");
$mediaStmt->execute();
$unassociatedMedia = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle association submission - rev/12-08-2024
if (isset($_POST["assign"])) {
    $userId = se($_POST, "user_id", -1, false);
    $mediaId = se($_POST, "media_id", -1, false);

    if ($userId > 0 && $mediaId > 0) {
        $assocStmt = $db->prepare("INSERT INTO UserMediaAssociations (user_id, media_entity_id) VALUES (:user_id, :media_id)");
        try {
            $assocStmt->execute([":user_id" => $userId, ":media_id" => $mediaId]);
            flash("Association created successfully!", "success");
        } catch (Exception $e) {
            flash("Error creating association: " . $e->getMessage(), "danger");
        }
    } else {
        flash("Invalid user or media entity selected.", "danger");
    }

    // Refresh the page to reflect changes
    header("Location: admin_association.php");
    exit;
}

// Fetch all associations
$assocStmt = $db->prepare("
    SELECT UMA.id, U.username, ME.title 
    FROM UserMediaAssociations UMA 
    JOIN Users U ON UMA.user_id = U.id 
    JOIN MediaEntities ME ON UMA.media_entity_id = ME.id
    ORDER BY U.username, ME.title
");
$assocStmt->execute();
$associations = $assocStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h1>Admin Association Management</h1>
    <h3>Create New Association</h3>
    <form method="POST">
        <div class="form-group">
            <label for="user_id">User</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="" disabled selected>Select a user</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo se($user, "id"); ?>">
                        <?php echo se($user, "username"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="media_id">Media Entity</label>
            <select name="media_id" id="media_id" class="form-control" required>
                <option value="" disabled selected>Select a media entity</option>
                <?php foreach ($unassociatedMedia as $media): ?>
                    <option value="<?php echo se($media, "id"); ?>">
                        <?php echo se($media, "title"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="assign" class="btn btn-primary">Create Association</button>
    </form>

    <h3>Existing Associations</h3>
    <?php if (count($associations) === 0): ?>
        <div class="alert alert-info">No associations found.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Media Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($associations as $assoc): ?>
                    <tr>
                        <td><?php echo se($assoc, "username"); ?></td>
                        <td><?php echo se($assoc, "title"); ?></td>
                        <td>
                            <form method="POST" action="delete_association.php" style="display:inline;">
                                <input type="hidden" name="assoc_id" value="<?php echo se($assoc, "id"); ?>">
                                <button type="submit" name="delete" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this association?');">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
