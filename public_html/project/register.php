<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
?>
<div class="container-fluid">
<form onsubmit="return validate(this)" method="POST">
    <?php render_input(["type"=>"email", "id"=>"email", "name"=>"email", "label"=>"Email", "rules"=>["required"=>true]]);?>
    <?php render_input(["type"=>"text", "id"=>"username", "name"=>"username", "label"=>"Username", "rules"=>["required"=>true, "maxlength"=>30]]);?>
    <?php render_input(["type"=>"password", "id"=>"password", "name"=>"password", "label"=>"Password", "rules"=>["required"=>true, "minlength"=>8]]);?>
    <?php render_input(["type"=>"password", "id"=>"confirm", "name"=>"confirm", "label"=>"Confirm Password", "rules"=>["required"=>true,"minlength"=>8]]);?>
    <?php render_button(["text"=>"Register", "type"=>"submit"]);?>
</form>
</div>
<script>
    // rev/11-07-2024
    function validate(form) {
        const email = form.email.value.trim();
        const username = form.username.value.trim();
        const password = form.password.value;
        const confirm = form.confirm.value;

        // Email validation: simple pattern check
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        // Username validation: 3-16 characters, only a-z, 0-9, _ or -
        const usernamePattern = /^[a-zA-Z0-9_-]{3,16}$/;
        if (!usernamePattern.test(username)) {
            alert("Username must be 3-16 characters and contain only a-z, 0-9, _, or -");
            return false;
        }

        // Password validation: at least 8 characters
        if (password.length < 8) {
            alert("Password must be at least 8 characters long.");
            return false;
        }

        // Confirm password matches
        if (password !== confirm) {
            alert("Passwords do not match.");
            return false;
        }

        // All validations passed
        return true;
    }
</script>
<?php
// rev/11-07-2024
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])) {
    // Retrieve and sanitize inputs
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);

    $hasError = false;

    //Sanitize email - rev/11-07-2024
    $email = sanitize_email($email);
    // Validate email
    if (empty($email) || !is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }

    // Validate username
    if (!is_valid_username($username)) {
        flash("Username must be 3-16 characters and contain only a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }

    // Validate password and confirm password match
    if (empty($password) || !is_valid_password($password)) {
        flash("Password must be at least 8 characters long", "danger");
        $hasError = true;
    } elseif ($password !== $confirm) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }

    // If no errors, proceed with registration
    if (!$hasError) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");

        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!", "success");
        } catch (PDOException $e) {
            users_check_duplicate($e->errorInfo);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/footer.php");
?>