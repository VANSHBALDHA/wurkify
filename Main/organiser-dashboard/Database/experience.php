<?php
// Include the database configuration file
include 'config.php'; // Adjust the path as needed

// Start session to access user ID
session_start();

// Check if user ID is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('User not logged in.');
            window.location.href = '../seeker/seekerlogin.html';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve the user ID from the session

// Sanitize and validate input data
$job_title = filter_input(INPUT_POST, 'job_title', FILTER_SANITIZE_STRING);
$company_name = filter_input(INPUT_POST, 'company_name', FILTER_SANITIZE_STRING);
$location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
$start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
$end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
$skills = filter_input(INPUT_POST, 'skills', FILTER_SANITIZE_STRING);
$employment_type = filter_input(INPUT_POST, 'employment_type', FILTER_SANITIZE_STRING);
$achievements = filter_input(INPUT_POST, 'achievements', FILTER_SANITIZE_STRING);

// Validate required fields
if (empty($job_title) || empty($company_name) || empty($location) || empty($start_date) || empty($employment_type)) {
    echo "<script>
            alert('All required fields must be filled.');
            window.history.back();
          </script>";
    exit();
}

// Prepare and bind SQL statement
$stmt = $conn->prepare("INSERT INTO organiser_experience (job_title, company_name, location, start_date, end_date, description, skills, employment_type, achievements, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssis", $job_title, $company_name, $location, $start_date, $end_date, $description, $skills, $employment_type, $achievements, $user_id);

// Execute and handle success/error
if ($stmt->execute()) {
    echo "<script>
            alert('Experience added successfully.');
            window.location.href = '../pages/settings.php'; // Redirect to settings page
          </script>";
} else {
    echo "<script>
            alert('Error: " . addslashes($stmt->error) . "');
            window.history.back();
          </script>";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
