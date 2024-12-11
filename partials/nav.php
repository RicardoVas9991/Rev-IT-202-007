<?php
require_once(__DIR__ . "/../lib/functions.php");

// Resolve cookie domain for session
$domain = $_SERVER["HTTP_HOST"];
if (strpos($domain, ":")) {
    $domain = explode(":", $domain)[0];
}
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

//this is an extra condition added to "resolve" the localhost issue for the session cookie
$localWorks = false; // Set to true if local setups work with domain-based cookies
if (($localWorks && $domain == "localhost") || $domain != "localhost") {
session_set_cookie_params([
    "lifetime" => 60 * 60,
    "path" => $BASE_PATH,
    "domain" => $domain,
    "secure" => $isSecure,
    "httponly" => true,
    "samesite" => "lax"
]);
}
session_start();


?>

<!-- Bootstrap CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom CSS and JS -->
<link rel="stylesheet" href="<?php echo get_url('styles.css'); ?>">
<script src="<?php echo get_url('helpers.js'); ?>"></script>

<nav class="navbar navbar-expand-lg bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Utelly</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('home.php'); ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('profile.php'); ?>">Profile</a></li>
                <?php else : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('login.php'); ?>">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('register.php'); ?>">Register</a></li>
                <?php endif; ?>

                <?php if (has_role("Admin")) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Admin Tools
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/create_role.php'); ?>">Create Role</a></li>
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/list_roles.php'); ?>">List Roles</a></li>
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/assign_roles.php'); ?>">Assign Roles</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/data_creation.php'); ?>">Create Data</a></li>
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/data_list.php'); ?>">List Data</a></li>
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/API_Fetch.php'); ?>">Fetch API Data</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <!-- Newly added links -->
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/admin_assign.php'); ?>">Admin Assign</a></li>
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/admin_association_pages.php'); ?>">Admin Association Pages</a></li>
                            <li><a class="dropdown-item" href="<?php echo get_url('admin/admin_association.php'); ?>">Admin Association</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (is_logged_in()) : ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo get_url('logout.php'); ?>">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>