<?php
session_start(); // Start the session to access $_SESSION variables

include('config.php'); // Database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user_id is set in the session
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = 'User not logged in.';
        header('Location: ../pages/settings.php');
        exit();
    }

    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Sanitize and validate input
    $height = filter_input(INPUT_POST, 'height', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $weight = filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Check for valid inputs
    if ($height === false || $weight === false) {
        $_SESSION['message'] = 'Invalid input for height or weight.';
        header('Location: ../pages/settings.php');
        exit();
    }

    // Check if the record already exists for the user
    $check_sql = "SELECT COUNT(*) FROM organiser_body_criteria WHERE user_id = ?";
    if ($check_stmt = $conn->prepare($check_sql)) {
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();
        
        if ($count > 0) {
            // Record already exists
            $_SESSION['message'] = 'Record for this user already exists.';
            header('Location: ../pages/settings.php');
            exit();
        }
    } else {
        // Error preparing the check query
        $_SESSION['message'] = 'Error checking for existing record: ' . htmlspecialchars($conn->error);
        header('Location: ../pages/settings.php');
        exit();
    }

    // Prepare SQL query for insertion
    $sql = "INSERT INTO organiser_body_criteria (user_id, height, weight) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("idd", $user_id, $height, $weight);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Height and weight added successfully.';
        } else {
            $_SESSION['message'] = 'Error adding height and weight: ' . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = 'Error preparing insert query: ' . htmlspecialchars($conn->error);
    }

    $conn->close();
    
    // Redirect to settings page
    header('Location: ../pages/settings.php');
    exit();
}
?>
