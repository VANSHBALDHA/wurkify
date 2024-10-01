<?php
session_start();
include('config.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('User not logged in.');
            window.location.href = '../logout.php';
          </script>";
    exit();
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Retrieve form data
$degree = $_POST['degree'];
$institution = $_POST['institution'];
$graduation_year = $_POST['graduation_year'];

// Prepare SQL query to check if education data already exists
$sql_check = "SELECT id FROM user_education WHERE user_id = ?";
if ($stmt_check = $conn->prepare($sql_check)) {
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Record exists, update it
        $sql_update = "UPDATE education SET degree = ?, institution = ?, graduation_year = ? WHERE user_id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ssii", $degree, $institution, $graduation_year, $user_id);
            if ($stmt_update->execute()) {
                echo "<script>
                        alert('Education updated successfully.');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            } else {
                echo "<script>
                        alert('Error updating record: " . $stmt_update->error . "');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            }
            $stmt_update->close();
        } else {
            echo "<script>
                    alert('Error preparing update query: " . $conn->error . "');
                    window.location.href = '../pages/settings.php'; // Redirect to the settings page
                  </script>";
        }
    } else {
        // No record exists, insert new
        $sql_insert = "INSERT INTO user_education (user_id, degree, institution, graduation_year) VALUES (?, ?, ?, ?)";
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("issi", $user_id, $degree, $institution, $graduation_year);
            if ($stmt_insert->execute()) {
                echo "<script>
                        alert('Education added successfully.');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            } else {
                echo "<script>
                        alert('Error inserting record: " . $stmt_insert->error . "');
                        window.location.href = '../pages/settings.php'; // Redirect to the settings page
                      </script>";
            }
            $stmt_insert->close();
        } else {
            echo "<script>
                    alert('Error preparing insert query: " . $conn->error . "');
                    window.location.href = '../pages/settings.php'; // Redirect to the settings page
                  </script>";
        }
    }

    $stmt_check->close();
} else {
    echo "<script>
            alert('Error preparing check query: " . $conn->error . "');
            window.location.href = '../pages/settings.php'; // Redirect to the settings page
          </script>";
}

$conn->close();
?>
