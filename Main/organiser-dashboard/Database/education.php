<?php
session_start();
include('config.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('User not logged in.');
            window.location.href = '../seeker/seekerlogin.html';
          </script>";
    exit();
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Retrieve and sanitize form data
$degree = filter_input(INPUT_POST, 'degree', FILTER_SANITIZE_STRING);
$institution = filter_input(INPUT_POST, 'institution', FILTER_SANITIZE_STRING);
$graduation_year = filter_input(INPUT_POST, 'graduation_year', FILTER_SANITIZE_NUMBER_INT);

// Check if the required fields are not empty
if (empty($degree) || empty($institution) || empty($graduation_year)) {
    echo "<script>
            alert('All fields are required.');
            window.location.href = '../pages/settings.php'; // Redirect to the settings page
          </script>";
    exit();
}

// Prepare SQL query to check if education data already exists
$sql_check = "SELECT id FROM `organiser-education` WHERE user_id = ?";
if ($stmt_check = $conn->prepare($sql_check)) {
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Record exists, update it
        $sql_update = "UPDATE `organiser-education` SET degree = ?, institution = ?, graduation_year = ? WHERE user_id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssii", $degree, $institution, $graduation_year, $user_id);
            if ($stmt_update->execute()) {
                echo "<script>
                        alert('Education updated successfully.');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            } else {
                echo "<script>
                        alert('Error updating record: " . addslashes($stmt_update->error) . "');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            }
            $stmt_update->close();
        } else {
            echo "<script>
                    alert('Error preparing update query: " . addslashes($conn->error) . "');
                    window.location.href = '../pages/settings.php'; // Redirect to the settings page
                  </script>";
        }
    } else {
        // No record exists, insert new
        $sql_insert = "INSERT INTO `organiser-education` (user_id, degree, institution, graduation_year) VALUES (?, ?, ?, ?)";
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("issi", $user_id, $degree, $institution, $graduation_year);
            if ($stmt_insert->execute()) {
                echo "<script>
                        alert('Education added successfully.');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            } else {
                echo "<script>
                        alert('Error inserting record: " . addslashes($stmt_insert->error) . "');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            }
            $stmt_insert->close();
        } else {
            echo "<script>
                    alert('Error preparing insert query: " . addslashes($conn->error) . "');
                    window.location.href = '../pages/settings.php'; // Redirect to the settings page
                  </script>";
        }
    }

    $stmt_check->close();
} else {
    echo "<script>
            alert('Error preparing check query: " . addslashes($conn->error) . "');
            window.location.href = '../pages/settings.php'; // Redirect to the settings page
          </script>";
}

$conn->close();
?>
