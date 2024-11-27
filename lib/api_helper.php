<?php
require(__DIR__ . "/load_api_keys.php");
/**
 * Send a request to the specified URL with the given method.
 * 
 * @param string $url The URL to send the request to.
 * @param string $key The API key to use for the request.
 * @param array $data The data to send with the request.
 * @param string $method The HTTP method to use for the request.
 * @param bool $isRapidAPI Whether the request is for RapidAPI.
 * @param string $rapidAPIHost The host value for the RapidAPI Header
 * 
 * @throws Exception If the API key is missing or empty.
 * 
 * @return array The response status and body.
 */
function _sendRequest($url, $key, $data = [], $method = 'GET', $isRapidAPI = true, $rapidAPIHost = "")
{
    global $API_KEYS;
    // Check if the API key is set and not empty
    if (!isset($API_KEYS) || !isset($API_KEYS[$key]) || empty($API_KEYS[$key])) {
        throw new Exception("Missing or empty API KEY");
    }
    $headers = [];
    if ($isRapidAPI) {
        $headers = [
            "X-RapidAPI-Host" => $rapidAPIHost,
            "X-RapidAPI-Key" => $API_KEYS[$key],
        ];
    } else {
        $headers = [
            "x-api-key" => $API_KEYS[$key]
        ];
    }
    $callback = fn (string $k, string $v): string => "$k: $v";
    $headers = array_map($callback, array_keys($headers), array_values($headers));
    $curl = curl_init();

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "", // Specify encoding if known
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ];

    if ($method == 'GET') {
        $options[CURLOPT_URL] = "$url?" . http_build_query($data); //key1=v1&key2=v2
    } else {
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($data);
    }
    //error_log("curl options: " . var_export($options, true));
    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        throw new Exception($err);
    } else {
        return ["status" => 200, "response" => $response];
    }
}

/**
 * Send a GET request to the specified URL.
 * 
 * @param string $url The URL to send the request to.
 * @param string $key The API key to use for the request.
 * @param array $data The data to send with the request.
 * @param bool $isRapidAPI Whether the request is for RapidAPI.
 * @param string $rapidAPIHost The host value for the RapidAPI Header
 * 
 * @return array The response status and body.
 */
function get($url, $key, $data = [], $isRapidAPI = true, $rapidAPIHost = "")
{
    return _sendRequest($url, $key, $data, 'GET', $isRapidAPI, $rapidAPIHost);
}


/**
 * Send a POST request to the specified URL.
 * 
 * @param string $url The URL to send the request to.
 * @param string $key The API key to use for the request.
 * @param array $data The data to send with the request.
 * @param bool $isRapidAPI Whether the request is for RapidAPI.
 * @param string $rapidAPIHost The host value for the RapidAPI Header
 * 
 * @return array The response status and body.
 */
function post($url, $key, $data = [], $isRapidAPI = true,  $rapidAPIHost = "")
{
    return _sendRequest($url, $key, $data, 'POST', $isRapidAPI, $rapidAPIHost);
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
<form method="POST">
    <button type="submit" name="fetch_api">Fetch API Data</button>
</form>
<?php require_once(__DIR__ . "/../../../partials/footer.php"); ?>