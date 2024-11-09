<?php
require_once(__DIR__ . "/../../partials/nav.php");
?>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email/Username</label>
        <input type="text" name="email" required />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <input type="submit" value="Login" />
</form>
<script>
    // rev/11-07-2024
    function validate(form) {
        const emailInput = form.email.value.trim();
        const passwordInput = form.password.value.trim();
        let isValid = true;

        // Simple email or username validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;  // For email validation
        const usernamePattern = /^[a-zA-Z0-9_-]{3,30}$/;    // For username validation (3-30 characters, alphanumeric, _ and - allowed)

        if (emailInput.includes("@")) {
            if (!emailPattern.test(emailInput)) {
                flash("Please enter a valid email address.", "warning");
                isValid = false;
            }
        } else {
            if (!usernamePattern.test(emailInput)) {
                flash("Username must be 3-30 characters and contain only letters, numbers, _ or -.", "warning");
                isValid = false;
            }
        }

        // Password validation (minimum 8 characters)
        if (passwordInput.length < 8) {
            flash("Password must be at least 8 characters long.", "warning");
            isValid = false;
        }

        return isValid;
    }
</script>

<?php
// rev/11-07-2024
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    $hasError = false;
    if (empty($email)) {
        flash("Email or username must be provided", "danger");
        $hasError = true;
    }

    if (str_contains($email, "@")) {
        $email = sanitize_email($email);
        if (!is_valid_email($email)) {
            flash("Invalid email address", "danger");
            $hasError = true;
        }
    } else {
        if (!is_valid_username($email)) {
            flash("Invalid username", "danger");
            $hasError = true;
        }
    }

    if (empty($password)) {
        flash("Password must be provided", "danger");
        $hasError = true;
    }

    if (strlen($password) < 8) {
        flash("Password must be at least 8 characters long", "danger");
        $hasError = true;
    }

    if (!$hasError) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password FROM Users WHERE email = :email OR username = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        $_SESSION["user"] = $user;
                        try {
                            $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                                JOIN UserRoles ON Roles.id = UserRoles.role_id 
                                WHERE UserRoles.user_id = :user_id AND Roles.is_active = 1 AND UserRoles.is_active = 1");
                            $stmt->execute([":user_id" => $user["id"]]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            error_log(var_export($e, true));
                        }
    
                        $_SESSION["user"]["roles"] = $roles ?? [];
                        flash("Welcome, " . get_username(), "success");
                        //header("Location: home.php");
                        exit(); // Use exit() instead of die()
                    } else {
                        flash("Invalid password", "danger");
                    }
                } else {
                    flash("User not found", "danger");
                }
            }
        } catch (Exception $e) {
            flash("An error occurred, please try again.", "danger");
        }
    }
}
?>
<?php require_once(__DIR__ . "/../../partials/flash.php"); ?>