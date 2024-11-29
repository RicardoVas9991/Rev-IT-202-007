<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true); // Ensure only logged-in users can create entries
// rev/11-20-2024

if (isset($_POST["create"])) {
    $title = se($_POST, "title", "", false);
    $description = se($_POST, "description", "", false);
    $release_date = se($_POST, "release_date", "", false);

    $db = getDB();
    try {
        $stmt = $db->prepare("INSERT INTO MediaEntities (title, description, release_date, is_api_data, user_id) 
                              VALUES (:title, :description, :release_date, :is_api_data, :user_id)");
        $stmt->execute([
            ":title" => $title,
            ":description" => $description,
            ":release_date" => $release_date,
            ":is_api_data" => false,
            ":user_id" => get_user_id()
        ]);
        flash("Entity created successfully!", "success");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) {
            flash("This entity already exists.", "warning");
        } else {
            flash("An unexpected error occurred.", "danger");
        }
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Data Creation</title>
</head>

<style>
  body {
    -webkit-text-size-adjust: 100%;
    text-size-adjust: 100%;
  }
</style>

<body>
  <form action="/submit" method="post">
    <label for="title">Title</label>
    <input type="text" id="title" name="title" required placeholder="Enter the title of the entity">

    <label for="description">Description</label>
    <textarea id="description" name="description" required placeholder="Provide a brief description"></textarea>

    <label for="release_date">Release Date</label>
    <input type="date" id="release_date" name="release_date" required title="Select the release date">

    <button type="submit" name="create">Create</button>
    <div id="flash"></div>
  </form>
</body>
</html>
