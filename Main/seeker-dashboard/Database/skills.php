<?php
session_start();
include('../Database/config.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('User not logged in');
            window.location.href = '../seeker/seekerlogin.html';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if required POST fields are set
if (!isset($_POST['skill_name']) || !isset($_POST['proficiency'])) {
    echo "<script>
            alert('Required fields are missing');
            window.location.href = '../pages/settings.php'; // Redirect to settings page
          </script>";
    exit();
}

$skill_name = trim($_POST['skill_name']);
$proficiency = trim($_POST['proficiency']);

// Check for empty values
if (empty($skill_name) || empty($proficiency)) {
    echo "<script>
            alert('Skill name and proficiency must not be empty');
            window.location.href = '../pages/settings.php'; // Redirect to settings page
          </script>";
    exit();
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO skills (user_id, skill_name, proficiency) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $skill_name, $proficiency);

if ($stmt->execute()) {
    echo "<script>
            alert('Skill added successfully');
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
