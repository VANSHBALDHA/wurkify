<?php
session_start();
include '../Database/config.php'; // Include your database connection file

// Check if the organizer is logged in
if (!isset($_SESSION['id'])) {
    echo "<script>
            alert('Organizer not logged in.');
            window.location.href = '../organizer/organizerlogin.html';
          </script>";
    exit();
}

$organizer_id = $_SESSION['id']; // Use 'id' as the session variable

// Get parameters from the GET request
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Validate action, user ID, and event ID
if (!in_array($action, ['confirm', 'reject']) || $user_id <= 0 || $event_id <= 0) {
    echo "<script>
            alert('Invalid action or parameters.');
            window.history.back();
          </script>";
    exit();
}

// Update the application status
$status = $action === 'confirm' ? 1 : 2;
$sql = "UPDATE event_applications SET status = ? WHERE user_id = ? AND event_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iii", $status, $user_id, $event_id);
    if ($stmt->execute()) {
        $stmt->close();
        echo "<script>
                alert('Application $action successfully!');
                window.location.href = 'view_applications.php?event_id=" . $event_id . "';
              </script>";
    } else {
        echo "<script>
                alert('Error updating application status: " . addslashes($stmt->error) . "');
                window.history.back();
              </script>";
    }
} else {
    echo "<script>
            alert('Error preparing update query: " . addslashes($conn->error) . "');
            window.history.back();
          </script>";
}

$conn->close();
?>
