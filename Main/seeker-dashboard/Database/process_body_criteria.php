<?php
session_start(); // Start the session to access $_SESSION variables

include('config.php'); // Database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user_id is set in the session
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'User not logged in';
        header('Location: ../pages/settings.php');
        exit();
    }

    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Sanitize and validate input
    $height = filter_input(INPUT_POST, 'height', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Validate that height and weight are positive numbers
    if (!is_numeric($height) || !is_numeric($weight) || $height <= 0 || $weight <= 0) {
        $_SESSION['message'] = 'Invalid height or weight value.';
        header('Location: ../pages/settings.php');
        exit();
    }

    // Check if record already exists
    $check_sql = "SELECT COUNT(*) FROM body_criteria WHERE user_id = ?";
    if ($check_stmt = $conn->prepare($check_sql)) {
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();
        
        if ($count > 0) {
            // Record already exists
            $_SESSION['message'] = 'Record for this user already exists';
            header('Location: ../pages/settings.php');
            exit();
        }
    } else {
        // Error preparing the check query
        $_SESSION['message'] = 'Error preparing check query: ' . $conn->error;
        header('Location: ../pages/settings.php');
        exit();
    }

    // Prepare SQL query for insertion
    $sql = "INSERT INTO body_criteria (user_id, height, weight) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("idd", $user_id, $height, $weight);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Height and weight added successfully';
        } else {
            $_SESSION['message'] = 'Error adding height and weight: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = 'Error preparing insert query: ' . $conn->error;
    }

    $conn->close();
    
    // Redirect to settings page
    header('Location: ../pages/settings.php');
    exit();
}
?>
