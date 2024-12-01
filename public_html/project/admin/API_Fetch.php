<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/11-20-2024

// Function to fetch data from the Utelly API
function fetchAPIData() {
    $url = "https://rapidapi.com/utelly/api/utelly"; // Replace with your actual API URL
    $apiKey = "UTELLY_API_KEY"; // Replace with your API key
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-RapidAPI-Key: $apiKey",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        flash("Error fetching data from API: " . curl_error($ch), "danger");
        curl_close($ch);
        return [];
    }
    curl_close($ch);

    $data = json_decode($response, true);
    return $data["results"] ?? []; // Adjust based on API's actual response structure
}

// Function to process and store API data in the database
function processAPIData($apiData) {
    $db = getDB();

    foreach ($apiData as $entry) {
        $apiId = se($entry, "id", null, false);
        $title = se($entry, "title", "Unknown Title", false);
        $description = se($entry, "description", "No description available.", false);
        $releaseDate = se($entry, "release_date", null, false);

        // Check if record already exists
        $stmt = $db->prepare("SELECT id FROM MediaEntities WHERE api_id = :api_id");
        $stmt->execute([":api_id" => $apiId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            // Update existing record
            $stmt = $db->prepare("UPDATE MediaEntities SET 
                                  title = :title, 
                                  description = :description, 
                                  release_date = :release_date, 
                                  modified = CURRENT_TIMESTAMP 
                                  WHERE api_id = :api_id");
        } else {
            // Insert new record
            $stmt = $db->prepare("INSERT INTO MediaEntities (api_id, title, description, release_date, created) 
                                  VALUES (:api_id, :title, :description, :release_date, CURRENT_TIMESTAMP)");
        }

        $stmt->execute([
            ":api_id" => $apiId,
            ":title" => $title,
            ":description" => $description,
            ":release_date" => $releaseDate
        ]);
    }

    flash("API data processed successfully!", "success");
}

// Trigger data fetch and processing on form submission
if (isset($_POST["fetch_api"])) {
    $apiData = fetchAPIData();
    if (!empty($apiData)) {
        processAPIData($apiData);
    } else {
        flash("No data returned from API", "danger");
    }
}
?>
<div class="container">
    <h1>Fetch API Data</h1>
    <form method="POST">
        <button type="submit" name="fetch_api" class="btn btn-primary">Fetch API Data</button>
    </form>
</div>
<?php require_once(__DIR__ . "/../../../partials/flash.php"); ?>
