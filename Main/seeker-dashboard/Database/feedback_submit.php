<?php
// Include database configuration
include('../Database/config.php'); // Ensure the path is correct

// Start the session to access session variables
session_start();

// Check if the user is logged in and get the username
if (!isset($_SESSION['username'])) {
    die("User not logged in.");
}
$username = $_SESSION['username'];

// Retrieve form data
$platform_rating = $_POST['platform_rating'];
$platform_feedback = $_POST['platform_feedback'];
$improvements = $_POST['improvements'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO feedback (username, platform_rating, platform_feedback, improvements) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $platform_rating, $platform_feedback, $improvements);

// Execute and check for success
if ($stmt->execute()) {
    echo "<script>alert('Feedback submitted successfully!'); window.location.href = '../pages/feedback.php';</script>";
} else {
    echo "<script>alert('Error submitting feedback. Please try again later.'); window.location.href = '../pages/feedback.php';</script>";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
