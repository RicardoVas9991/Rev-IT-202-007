<?php
require(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
// rev/11-20-2024

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

?>
<h3><?php se($data, "title"); ?></h3>
<p><?php se($data, "description"); ?></p>
<p>Release Date: <?php se($data, "release_date"); ?></p>
<a href="edit_data.php?id=<?php se($data, "id"); ?>">Edit</a>
<a href="delete_data.php?id=<?php se($data, "id"); ?>">Delete</a>
