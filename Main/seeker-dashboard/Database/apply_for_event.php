<?php
session_start();
include '../Database/config.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('User not logged in.');
            window.location.href = '../logout.php';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

// Validate event ID and user ID
if ($event_id <= 0 || $user_id <= 0) {
    echo "<script>
            alert('Invalid event or user ID.');
            window.history.back();
          </script>";
    exit();
}

// Get organiser ID from event_registration table
$organiser_sql = "SELECT user_id FROM event_registration WHERE event_id = ?";
if ($organiser_stmt = $conn->prepare($organiser_sql)) {
    $organiser_stmt->bind_param("i", $event_id);
    $organiser_stmt->execute();
    $organiser_result = $organiser_stmt->get_result();
    if ($organiser_result->num_rows == 0) {
        echo "<script>
                alert('Event does not exist or has no organiser.');
                window.history.back();
              </script>";
        $organiser_stmt->close();
        $conn->close();
        exit();
    }
    $organiser_row = $organiser_result->fetch_assoc();
    $organiser_id = $organiser_row['user_id'];
    $organiser_stmt->close();
} else {
    echo "<script>
            alert('Error retrieving organiser ID: " . addslashes($conn->error) . "');
            window.history.back();
          </script>";
    $conn->close();
    exit();
}

// Check if the application already exists for the specific event
$check_sql = "SELECT * FROM event_applications WHERE user_id = ? AND event_id = ?";
if ($check_stmt = $conn->prepare($check_sql)) {
    $check_stmt->bind_param("ii", $user_id, $event_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('You have already applied for this event.');
                window.history.back();
              </script>";
        $check_stmt->close();
        $conn->close();
        exit();
    }
    $check_stmt->close();
} else {
    echo "<script>
            alert('Error checking application status: " . addslashes($conn->error) . "');
            window.history.back();
          </script>";
    $conn->close();
    exit();
}

// Insert application into the database with status defaulting to 'Pending'
$insert_sql = "INSERT INTO event_applications (user_id, event_id, organiser_id, status) VALUES (?, ?, ?, 'Pending')";
if ($insert_stmt = $conn->prepare($insert_sql)) {
    $insert_stmt->bind_param("iii", $user_id, $event_id, $organiser_id);
    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        echo "<script>
                alert('Application successful!');
                window.location.href = '../pages/events.php';
              </script>";
    } else {
        echo "<script>
                alert('Error applying for event: " . addslashes($insert_stmt->error) . "');
                window.history.back();
              </script>";
    }
} else {
    echo "<script>
            alert('Error preparing application query: " . addslashes($conn->error) . "');
            window.history.back();
          </script>";
}

$conn->close();
?>
