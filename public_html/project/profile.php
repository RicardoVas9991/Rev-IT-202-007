<?php
require_once(__DIR__ . "/../../partials/nav.php");
is_logged_in(true);

if (isset($_POST["save"])) {
    $email = se($_POST, "email", null, false);
    $username = se($_POST, "username", null, false);

    $params = [":email" => $email, ":username" => $username, ":id" => get_user_id()];
    $db = getDB();
    $stmt = $db->prepare("UPDATE Users SET email = :email, username = :username WHERE id = :id");

    try {
        $stmt->execute($params);
        flash("Profile saved", "success");
    } catch (PDOException $e) {
        if ($e->errorInfo[1] === 1062) {
            preg_match("/Users.(\w+)/", $e->errorInfo[2], $matches);
            if (isset($matches[1])) {
                flash("The chosen " . $matches[1] . " is not available.", "warning");
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
<form method="POST" onsubmit="return validate(this);">
    <div class="mb-3">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php se($email); ?>" />
    </div>
    <div class="mb-3">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="<?php se($username); ?>" />
    </div>
    <!-- DO NOT PRELOAD PASSWORD -->
    <div>Password Reset</div>
    <div class="mb-3">
        <label for="cp">Current Password</label>
        <input type="password" name="currentPassword" id="cp" />
    </div>
    <div class="mb-3">
        <label for="np">New Password</label>
        <input type="password" name="newPassword" id="np" />
    </div>
    <div class="mb-3">
        <label for="conp">Confirm Password</label>
        <input type="password" name="confirmPassword" id="conp" />
    </div>
    <input type="submit" value="Update Profile" name="save" />
</form>

<script>
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
require_once(__DIR__ . "/../../partials/flash.php");
?>