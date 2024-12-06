<?php
require(__DIR__ . "/../../../partials/nav.php");
is_logged_in(true);
// rev/12-02-2024

// Function to fetch data from the Utelly API
function fetchAPIData() {
   
    $data = [];
    $endpoint = "https://imdb188.p.rapidapi.com/api/v1/searchIMDB";
    $isRapidAPI = true;
    $rapidAPIHost = "imdb188.p.rapidapi.com";
    $result = get($endpoint, "IMDB_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    error_log("Response: " . var_export($result, true));
    
    return $result;
}

// Function to process and store API data in the database
function processAPIData($apiData) {
    $db = getDB();

    foreach ($apiData as $entry) {
        $apiId = se($entry, "movieId", null, false); // Adjust key based on API response
        $title = se($entry, "name", "Unknown Title", false);
        $description = se($entry, "summary", "No description available.", false);
        $releaseDate = se($entry, "year", null, false);

        if ($apiId !== null && strlen($apiId) > 255) {
            error_log("Truncated api_id: Original value: $apiId");
            $apiId = substr($apiId, 0, 255);
        }
        
        if (empty($apiId)) {
            error_log("Skipping entry due to missing api_id.");
            continue; // Skip entries without a valid api_id
        }

        // Truncate api_id if it exceeds 255 characters
        if ($title !== null && strlen($title) > 255) {
            error_log("Truncated title: Original value: $title");
            $title = substr($title, 0, 255);
        }

        // Validate release date
        if (!empty($releaseDate) && !isValidDate($releaseDate)) {
            $releaseDate = null; // Set to null if invalid
        }

        // Check if the record exists
        $stmt = $db->prepare("SELECT id FROM MediaEntities WHERE api_id = :api_id");
        $stmt->execute([":api_id" => $apiId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $stmt = $db->prepare("UPDATE MediaEntities SET 
                                  title = :title, 
                                  description = :description, 
                                  release_date = :release_date, 
                                  modified = CURRENT_TIMESTAMP 
                                  WHERE api_id = :api_id");
        } else {
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


// Function to validate the release date format
function isValidDate($date) {
    // Check if the date is a valid format (e.g., 'YYYY-MM-DD')
    $format = 'Y'; // only checking year
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
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
