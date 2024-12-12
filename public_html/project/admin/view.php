<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true); // Ensure only authenticated users access this page - rev/12-05-2024

$id = $_GET['id'] ?? null; // Get the ID from the URL
if (!$id) {
    flash("No ID provided.", "warning");
    exit(header("Location: admin_association_pages.php")); // Redirect if no ID
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM MediaEntities WHERE id = :id");
$stmt->bindValue(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$media = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$media) {
    flash("Media not found or is Admin only.", "warning");
    exit(header("Location: admin_association_pages.php")); // Redirect if media not found
}

?>
<h1>View Media</h1>
<p>Title: <?= htmlspecialchars($media['title']) ?></p>
<p>Description: <?= htmlspecialchars($media['description']) ?></p>
<p>Release Date: <?= htmlspecialchars($media['release_date']) ?></p>
<?php if (has_role("Admin")) : ?>
    <a href="admin_association_pages.php">Back to List</a>
<?php else : ?>
    <a href="association_pages.php">Back to List</a>
<?php endif; ?>
<!-- <a href="admin_association_pages.php">Back to List</a> -->