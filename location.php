<?php
// Check if latitude and longitude data has been sent
if (isset($_POST['lat']) && isset($_POST['long'])) {
    // Retrieve latitude and longitude data
    $latitude = $_POST['lat'];
    $longitude = $_POST['long'];

    // Create a string to store
    $locationData = "Latitude: " . $latitude . ", Longitude: " . $longitude . "\r\n";

    // Save the data to a file
    $file = 'locations.txt';
    $fp = fopen($file, 'a'); // Open the file in append mode
    fwrite($fp, $locationData); // Write the data to the file
    fclose($fp); // Close the file

    // Return a success response
    echo json_encode(array("status" => "success", "message" => "Location saved successfully."));
} else {
    // If data is not present, return an error response
    echo json_encode(array("status" => "error", "message" => "Location data not provided."));
}
?>
