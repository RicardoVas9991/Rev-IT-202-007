<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/11-20-2024
function fetch_api_data() {
    $db = getDB();
    $api_url = "https://rapidapi.com/utelly/api/utelly";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);

    foreach ($data as $item) {
        $stmt = $db->prepare("INSERT IGNORE INTO MediaEntities (title, description, release_date, api_id, is_api_data) 
                              VALUES (:title, :description, :release_date, :api_id, true)");
        $stmt->execute([
            ":title" => $item["title"],
            ":description" => $item["description"],
            ":release_date" => $item["release_date"],
            ":api_id" => $item["id"]
        ]);
    }
}

// rev/11-20-2024
function fetchAPIData() {
    $url = "https://rapidapi.com/utelly/api/utelly"; // Replace with your API URL
    $apiKey = "UTELLY_API_KEY"; // Replace with your actual API key
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey"
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        flash("Error fetching data from API: " . curl_error($ch), "danger");
        return [];
    }
    curl_close($ch);

    $data = json_decode($response, true); // Parse the JSON response
    return $data["results"] ?? []; // Adjust based on your API response structure
}

// rev/11-20-2024
function processAPIData($apiData) {
    $db = getDB();

    foreach ($apiData as $entry) {
        $apiId = se($entry, "id", null, false);
        $title = se($entry, "title", null, false);
        $description = se($entry, "description", null, false);
        $releaseDate = se($entry, "release_date", null, false);

        // Check for duplicates using the API ID
        $stmt = $db->prepare("SELECT id FROM MediaEntities WHERE api_id = :api_id");
        $stmt->execute([":api_id" => $apiId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            // Update existing record if needed
            $stmt = $db->prepare("UPDATE MediaEntities SET title = :title, description = :description, 
                                  release_date = :release_date, modified = CURRENT_TIMESTAMP 
                                  WHERE api_id = :api_id");
            $stmt->execute([
                ":title" => $title,
                ":description" => $description,
                ":release_date" => $releaseDate,
                ":api_id" => $apiId
            ]);
        } else {
            // Insert new record
            $stmt = $db->prepare("INSERT INTO MediaEntities (api_id, title, description, release_date, created) 
                                  VALUES (:api_id, :title, :description, :release_date, CURRENT_TIMESTAMP)");
            $stmt->execute([
                ":api_id" => $apiId,
                ":title" => $title,
                ":description" => $description,
                ":release_date" => $releaseDate
            ]);
        }
    }
    flash("API data processed successfully!", "success");
}

// rev/11-20-2024
if (isset($_POST["fetch_api"])) {
    $apiData = fetchAPIData();
    if (!empty($apiData)) {
        processAPIData($apiData);
    } else {
        flash("No data returned from API", "danger");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<form method="POST">
    <button type="submit" name="fetch_api">Fetch API Data</button>
</form>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
