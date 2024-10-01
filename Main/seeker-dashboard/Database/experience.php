<?php
// Include the database configuration file
include 'config.php'; // Adjust the path as needed

// Start session to access user ID
session_start();

// Check if user ID is set in the session
if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id']; // Retrieve the user ID from the session

// Check if the user ID exists in the wurkify-user table
$user_check_stmt = $conn->prepare("SELECT user_id FROM wurkify_user WHERE user_id = ?");
$user_check_stmt->bind_param("i", $user_id);
$user_check_stmt->execute();
$user_check_stmt->store_result();

if ($user_check_stmt->num_rows == 0) {
    echo "<script>
            alert('Error: User ID does not exist');
            window.location.href = '../pages/settings.php'; // Redirect to settings page
          </script>";
    $user_check_stmt->close();
    $conn->close();
    exit;
}

$user_check_stmt->close();

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO user_experience (job_title, company_name, location, start_date, end_date, description, skills, employment_type, achievements, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssis", $job_title, $company_name, $location, $start_date, $end_date, $description, $skills, $employment_type, $achievements, $user_id);

// Set parameters and execute
$job_title = $_POST['job_title'];
$company_name = $_POST['company_name'];
$location = $_POST['location'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$description = $_POST['description'];
$skills = $_POST['skills'];
$employment_type = $_POST['employment_type'];
$achievements = $_POST['achievements'];

if ($stmt->execute()) {
    echo "<script>
            alert('Record created successfully');
            window.location.href = '../pages/settings.php'; // Redirect to settings page
          </script>";
} else {
    echo "<script>
            alert('Error: " . $stmt->error . "');
            window.location.href = '../pages/settings.php'; // Redirect to settings page
          </script>";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
