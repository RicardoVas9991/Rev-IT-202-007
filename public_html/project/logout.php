<?php
session_start();
require(__DIR__ . "/../../lib/functions.php");
reset_session();
// rev/11-09-2024
flash("Successfully logged out", "success");
header("Location: login.php");