<?php
// rev/11-20-2024
function fetch_api_data() {
    $db = getDB();
    $api_url = "https://api.example.com/media";
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
?>