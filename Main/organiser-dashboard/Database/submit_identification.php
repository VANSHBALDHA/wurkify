<?php
session_start();
include('config.php'); // Include your database connection file

// Check if the user is logged in and user_id is set
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('User not logged in.'); window.location.href = '../pages/settings.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id']; // Get user_id from session

// Check if the POST data exists
if (isset($_POST['aadhar'], $_POST['pan'], $_POST['address_line1'], $_POST['address_line2'], $_POST['city'], $_POST['state'], $_POST['zipcode'])) {
    // Sanitize and validate input
    $aadhar = filter_input(INPUT_POST, 'aadhar', FILTER_SANITIZE_STRING);
    $pan = filter_input(INPUT_POST, 'pan', FILTER_SANITIZE_STRING);
    $address_line1 = filter_input(INPUT_POST, 'address_line1', FILTER_SANITIZE_STRING);
    $address_line2 = filter_input(INPUT_POST, 'address_line2', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $zipcode = filter_input(INPUT_POST, 'zipcode', FILTER_SANITIZE_STRING);

    
    // Prepare SQL query to insert or update identification info
    $sql = "INSERT INTO organiser_identification_info (user_id, aadhar, pan, address_line1, address_line2, city, state, zipcode) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            aadhar = VALUES(aadhar), 
            pan = VALUES(pan), 
            address_line1 = VALUES(address_line1), 
            address_line2 = VALUES(address_line2), 
            city = VALUES(city), 
            state = VALUES(state), 
            zipcode = VALUES(zipcode)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isssssss", $user_id, $aadhar, $pan, $address_line1, $address_line2, $city, $state, $zipcode);
        if ($stmt->execute()) {
            echo "<script>alert('Data successfully submitted.'); window.location.href = '../pages/settings.php';</script>";
        } else {
            // Log error instead of exposing it to the user
            error_log("Database error: " . $stmt->error, 3, "/var/log/myapp/errors.log");
            echo "<script>alert('An error occurred while processing your request. Please try again later.'); window.location.href = '../pages/settings.php';</script>";
        }
        $stmt->close();
    } else {
        // Log error instead of exposing it to the user
        error_log("Error preparing query: " . $conn->error, 3, "/var/log/myapp/errors.log");
        echo "<script>alert('An error occurred while processing your request. Please try again later.'); window.location.href = '../pages/settings.php';</script>";
    }
} else {
    echo "<script>alert('Required form data is missing.'); window.location.href = '../pages/settings.php';</script>";
}

// Close the database connection
$conn->close();
?>
