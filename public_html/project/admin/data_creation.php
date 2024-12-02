<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true); // Ensure the user is logged in - rev/11-20-2024

// Handle form submission
if (isset($_POST["action"])) {
    $action = se($_POST, "action", "", false);
    $title = se($_POST, "title", "", false);
    $description = se($_POST, "description", "", false);
    $release_date = se($_POST, "release_date", "", false);
    $isApiData = isset($data['is_api_data']) ? (int)$data['is_api_data'] : 0;
    $user_id = get_user_id();
    if (!$user_id) {
        error_log("Error: user_id is null or invalid");
        flash("User ID is invalid. Please log in again.", "danger");
        return;
    }

    if ($action === "create") {
        if ($title && $description && $release_date) {
            // Prepare the data for insertion
            $db = getDB();
            try {
                $stmt = $db->prepare("INSERT INTO MediaEntities (title, description, release_date, is_api_data, user_id)
                                      VALUES (:title, :description, :release_date, :is_api_data, :user_id)");
                $params = [
                    ":title" => $title,
                    ":description" => $description,
                    ":release_date" => $release_date,
                    ':is_api_data' => $isApiData ? 1 : 0,
                    ":user_id" => get_user_id()
                ];
                error_log("SQL Query: " . $stmt->queryString);
                error_log("SQL Params: " . var_export($params, true));
                $stmt->execute($params);
                flash("Media entity created successfully!", "success");
            } catch (PDOException $e) {
                error_log("PDO Exception: " . $e->getMessage());
                flash("An unexpected error occurred. Please try again.", "danger");
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
