<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);

if (!has_role("Admin")) {
    flash("You do not have permission to access this page.", "danger");
    header("Location: project/home.php");
    exit;
}

// Handle bulk delete
if (isset($_POST["remove_all"])) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM UserMediaAssociations");
    try {
        $stmt->execute();
        flash("All associations removed successfully.", "success");
    } catch (Exception $e) {
        flash("Error removing associations: " . $e->getMessage(), "danger");
    }

    // Redirect to the admin page
    header("Location: admin_association.php");
    exit;
}
?>

<div class="container">
    <h1>Remove All Associations</h1>
    <form method="POST">
    <p>This will remove <strong>all associations</strong>. Type <strong>DELETE ALL</strong> to confirm:</p>
    <input type="text" name="confirmation" class="form-control" placeholder="Type DELETE ALL here" required>
    <button type="submit" name="remove_all" class="btn btn-danger" disabled id="confirm-btn">Remove All Associations</button>
    </form>
    <script>
        const input = document.querySelector('input[name="confirmation"]');
        const button = document.getElementById('confirm-btn');
        input.addEventListener('input', () => {
            button.disabled = input.value.trim().toUpperCase() !== "DELETE ALL";
        });
    </script>
</div>

<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
