<?php
// Include the configuration file
include('config.php');

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php"); // Redirect if user is not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form input
    $twitter_username = !empty($_POST['twitter_username']) ? htmlspecialchars(trim($_POST['twitter_username'])) : null;
    $facebook_username = !empty($_POST['facebook_username']) ? htmlspecialchars(trim($_POST['facebook_username'])) : null;
    $linkedin_username = !empty($_POST['linkedin_username']) ? htmlspecialchars(trim($_POST['linkedin_username'])) : null;
    $youtube_username = !empty($_POST['youtube_username']) ? htmlspecialchars(trim($_POST['youtube_username'])) : null;

    // Start building the SQL query
    $updates = [];
    $params = [];
    $types = '';

    // Check which fields are provided and build the update query dynamically
    if ($twitter_username !== null) {
        $updates[] = "twitter_username = ?";
        $params[] = $twitter_username;
        $types .= 's';
    }
    if ($facebook_username !== null) {
        $updates[] = "facebook_username = ?";
        $params[] = $facebook_username;
        $types .= 's';
    }
    if ($linkedin_username !== null) {
        $updates[] = "linkedin_username = ?";
        $params[] = $linkedin_username;
        $types .= 's';
    }
    if ($youtube_username !== null) {
        $updates[] = "youtube_username = ?";
        $params[] = $youtube_username;
        $types .= 's';
    }

    // Check if there are any fields to update
    if (empty($updates)) {
        echo "No fields to update.";
        exit();
    }

    // Append the user_id to the parameters
    $params[] = $user_id;
    $types .= 'i';

    // Build the final SQL query
    $sql = "UPDATE user_social SET " . implode(", ", $updates) . " WHERE user_id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters
        $stmt->bind_param($types, ...$params);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Record updated successfully!";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
}

