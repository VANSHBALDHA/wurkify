<?php
session_start();
include('../Database/config.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../seeker/seekerlogin.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $status = $_POST['status'];

    if ($stmt = $conn->prepare("UPDATE event_registration SET status = ? WHERE event_id = ?")) {
        $stmt->bind_param("si", $status, $event_id);
        if ($stmt->execute()) {
            echo "<script>alert('Event status updated successfully.'); window.location.href='../pages/eventstatus.php';</script>";
        } else {
            echo "<script>alert('Error updating event status: " . $stmt->error . "'); window.location.href='eventstatus.php';</script>";
        }
        $stmt->close();
    } else {
        echo "Error preparing SQL statement: " . $conn->error;
    }
}
$conn->close();
?>
