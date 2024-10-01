<?php
// Include the configuration file
include('config.php');

// Start session and check if the user is logged in
session_start();
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

    // Check if the user already has social media data
    $sql_check = "SELECT COUNT(*) FROM user_social WHERE id = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param('i', $user_id);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            // Data exists, perform an update
            $updates = [];
            $params = [];
            $types = '';

            // Build the update query dynamically
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
                header("Location: ../pages/settings.php?update=not_modified");
                exit();
            }

            // Append the user_id to the parameters
            $params[] = $user_id;
            $types .= 'i';

            // Build the final SQL query
            $sql_update = "UPDATE user_social SET " . implode(", ", $updates) . " WHERE id = ?";

            // Prepare the statement
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param($types, ...$params);

                // Execute the statement
                if ($stmt_update->execute()) {
                    // Redirect to settings page with success message
                    header("Location: ../pages/settings.php?update=success");
                } else {
                    // Redirect to settings page with error message
                    header("Location: ../pages/settings.php?update=error&message=" . urlencode($stmt_update->error));
                }

                $stmt_update->close();
            } else {
                // Redirect to settings page with error message
                header("Location: ../pages/settings.php?update=error&message=" . urlencode($conn->error));
            }
        } else {
            // Data does not exist, perform an insert
            $sql_insert = "INSERT INTO user_social (id, twitter_username, facebook_username, linkedin_username, youtube_username) VALUES (?, ?, ?, ?, ?)";

            if ($stmt_insert = $conn->prepare($sql_insert)) {
                $stmt_insert->bind_param('issss', $user_id, $twitter_username, $facebook_username, $linkedin_username, $youtube_username);

                // Execute the statement
                if ($stmt_insert->execute()) {
                    // Redirect to settings page with success message
                    header("Location: ../pages/settings.php?update=success");
                } else {
                    // Redirect to settings page with error message
                    header("Location: ../pages/settings.php?update=error&message=" . urlencode($stmt_insert->error));
                }

                $stmt_insert->close();
            } else {
                // Redirect to settings page with error message
                header("Location: ../pages/settings.php?update=error&message=" . urlencode($conn->error));
            }
        }

        $conn->close();
    } else {
        // Handle error in the check query
        header("Location: ../pages/settings.php?update=error&message=" . urlencode($conn->error));
    }
} else {
    echo "Invalid request.";
}
?>
