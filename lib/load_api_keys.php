<?php
// string array containing env keys to lookup (this allows usage of multiple APIs)
$env_keys = ["UTELLY_API_KEY"]; // rev - 11/21/2024
$ini = @parse_ini_file(".env");

$API_KEYS = [];
foreach ($env_keys as $key) {
    if ($ini && isset($ini[$key])) {
        //load local .env file
        $API_KEY = $ini[$key];
        $API_KEYS[$key] = $API_KEY;
    } else {
        //load from heroku env variables
        $API_KEY = getenv($key);
        $API_KEYS[$key] = $API_KEY;
    }
    if (!isset($API_KEYS[$key]) || !$API_KEYS[$key]) {
        error_log("Faild to load api key for env key $key");
    }
    unset($API_KEY);
}