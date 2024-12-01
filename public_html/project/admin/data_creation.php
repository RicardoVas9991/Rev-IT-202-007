<?php
require(__DIR__ . "/../../../partials/nav.php");

// Ensure the user is logged in - rev/11-20-2024
if (is_logged_in(true)) {
    flash("You don't have permission to view this page", "warning");
    exit(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php
// Handle form submission
if (isset($_POST["action"])) {
    $action = se($_POST, "action", "", false);
    $title = se($_POST, "title", "", false);
    $description = se($_POST, "description", "", false);
    $release_date = se($_POST, "release_date", "", false);

    if ($action === "create") {
        if ($title && $description && $release_date) {
            // Prepare the data for insertion
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
                flash("Media entity created successfully!", "success");
            } catch (PDOException $e) {
                if ($e->errorInfo[1] === 1062) {
                    flash("This media entity already exists.", "warning");
                } else {
                    error_log("Error inserting media entity: " . var_export($e->errorInfo, true));
                    flash("An unexpected error occurred. Please try again.", "danger");
                }
            }
        } else {
            flash("All fields are required.", "warning");
        }
    }
}

?>
<div class="container-fluid">
    <h3>Create Media Entity</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#" onclick="switchTab('create')">Create</a>
        </li>
    </ul>

    <div id="create" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "text", "name" => "title", "placeholder" => "Enter title", "label" => "Title", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "textarea", "name" => "description", "placeholder" => "Enter description", "label" => "Description", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "date", "name" => "release_date", "placeholder" => "Select release date", "label" => "Release Date", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>
            <?php render_button(["text" => "Create", "type" => "submit"]); ?>
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let elements = document.getElementsByClassName("tab-target");
            for (let ele of elements) {
                ele.style.display = (ele.id === tab) ? "block" : "none";
            }
        }
    }
</script>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
