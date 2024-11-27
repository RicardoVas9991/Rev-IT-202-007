<?php
require_once(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);
// rev/11-07-2024
?>
<?php
if (isset($_POST["save"])) {
    $email = se($_POST, "email", null, false);
    $username = se($_POST, "username", null, false);

    $params = [":email" => $email, ":username" => $username, ":id" => get_user_id()];
    $db = getDB();
    $stmt = $db->prepare("UPDATE Users set email = :email, username = :username where id = :id");

    try {
        $stmt->execute($params);
        flash("Profile saved", "success");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) { // rev/11-09-2024 - Duplicate entry error code
            preg_match("/Users.(\w+)/", $e->errorInfo[2], $matches);
            if (isset($matches[1])) {
                $field = $matches[1];
                if ($field === "email") {
                    flash("The chosen email address is already in use. Please try a different one.", "warning");
                } elseif ($field === "username") {
                    flash("The chosen username is already in use. Please try a different one.", "warning");
                } else {
                    flash("A database error occurred, please try again.", "danger");
                }
            } else {
                flash("A database error occurred, please try again.", "danger");
            }
        } else {
            flash("An unexpected error occurred, please try again", "danger");
        }
    }

    // Refresh user data
    $stmt = $db->prepare("SELECT id, email, username FROM Users WHERE id = :id LIMIT 1");
    try {
        $stmt->execute([":id" => get_user_id()]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION["user"]["email"] = $user["email"];
            $_SESSION["user"]["username"] = $user["username"];
        } else {
            flash("User doesn't exist", "danger");
        }
    } catch (PDOException $e) {
        flash("An unexpected error occurred, please try again", "danger");
    }

    // Password Update
    $current_password = se($_POST, "currentPassword", null, false);
    $new_password = se($_POST, "newPassword", null, false);
    $confirm_password = se($_POST, "confirmPassword", null, false);

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            $stmt = $db->prepare("SELECT password FROM Users WHERE id = :id");
            try {
                $stmt->execute([":id" => get_user_id()]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if (isset($result["password"]) && password_verify($current_password, $result["password"])) {
                    $stmt = $db->prepare("UPDATE Users SET password = :password WHERE id = :id");
                    $stmt->execute([
                        ":id" => get_user_id(),
                        ":password" => password_hash($new_password, PASSWORD_BCRYPT)
                    ]);
                    flash("Password reset", "success");
                } else {
                    flash("Current password is invalid", "warning");
                }
            } catch (PDOException $e) {
                flash("An unexpected error occurred during password update", "danger");
            }
        } else {
            flash("New passwords don't match", "warning");
        }
    }
}
?>

<?php
$email = get_user_email();
$username = get_username();
?>
<div class="container-fluid">
    <form method="POST" onsubmit="return validate(this);">
        <?php render_input(["type" => "email", "id" => "email", "name" => "email", "label" => "Email", "value" => $email, "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "text", "id" => "username", "name" => "username", "label" => "Username", "value" => $username, "rules" => ["required" => true, "maxlength" => 30]]); ?>
        <!-- DO NOT PRELOAD PASSWORD -->
        <div class="lead">Password Reset</div>
        <?php render_input(["type" => "password", "id" => "cp", "name" => "currentPassword", "label" => "Current Password", "rules" => ["minlength" => 8]]); ?>
        <?php render_input(["type" => "password", "id" => "np", "name" => "newPassword", "label" => "New Password", "rules" => ["minlength" => 8]]); ?>
        <?php render_input(["type" => "password", "id" => "conp", "name" => "confirmPassword", "label" => "Confirm Password", "rules" => ["minlength" => 8]]); ?>
        <?php render_input(["type" => "hidden", "name" => "save"]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_button(["text" => "Update Profile", "type" => "submit"]); ?>
    </form>
</div>
<script>
    // rev/11-07-2024
    function validate(form) {
        const email = form.email.value.trim();
        const username = form.username.value.trim();
        const currentPassword = form.currentPassword.value;
        const newPassword = form.newPassword.value;
        const confirmPassword = form.confirmPassword.value;
        let isValid = true;

        // Email validation (simple pattern check)
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            flash("Please enter a valid email address.", "warning");
            isValid = false;
        }

        // Username validation (3-16 characters, alphanumeric, _, - only)
        const usernamePattern = /^[a-zA-Z0-9_-]{3,16}$/;
        if (!usernamePattern.test(username)) {
            flash("Username must be 3-16 characters and contain only letters, numbers, _, or -", "warning");
            isValid = false;
        }

        // Password validation (at least 8 characters)
        if (newPassword && newPassword.length < 8) {
            flash("New password must be at least 8 characters long.", "warning");
            isValid = false;
        }

        // Confirm password matches new password
        if (newPassword && newPassword !== confirmPassword) {
            flash("New password and confirm password must match.", "warning");
            isValid = false;
        }
        return isValid;
    }
</script>
<?php
require_once(__DIR__ . "/../../partials/footer.php");
?>