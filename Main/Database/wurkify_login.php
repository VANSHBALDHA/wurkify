<?php
// Include database configuration file
require 'config.php';

// Start session
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the form data
    $usernameOrEmail = $_POST['usernameOrEmail'];
    $password = $_POST['password'];

    // Sanitize user inputs
    $usernameOrEmail = trim($usernameOrEmail);
    $password = trim($password);

    // Debugging: Check if POST data is coming through
    var_dump($usernameOrEmail, $password); // Ensure correct data is being passed

    // Check if inputs are empty
    if (empty($usernameOrEmail) || empty($password)) {
        echo "<script>alert('Username or Email and Password are required.'); window.history.back();</script>";
        exit();
    }

    // SQL query to check if the user exists by username or email
    $query = "SELECT * FROM `wurkify_user` WHERE (username = ? OR email = ?)";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        // Debugging: Check if SQL query executes correctly and returns results
        if ($result->num_rows === 0) {
            echo "<script>alert('No account found with the given credentials.'); window.history.back();</script>";
        } elseif ($result->num_rows === 1) {
            // Fetch user data
            $user = $result->fetch_assoc();
            // Debugging: Check fetched user data
            var_dump($user); // Ensure correct user data is being retrieved

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Debugging: Check if password verification is successful
                echo "Password verified successfully";

                // Password is correct, start the session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on user role
                if ($user['role'] == 'seeker') {
                    echo "<script>alert('Login successful! Redirecting to Seeker Dashboard.'); window.location.href = '../seeker-dashboard/index.php';</script>";
                } elseif ($user['role'] == 'organizer') {
                    echo "<script>alert('Login successful! Redirecting to Organizer Dashboard.'); window.location.href = '../organiser-dashboard/index.php';</script>";
                } else {
                    echo "<script>alert('Unknown user role.'); window.history.back();</script>";
                }
                exit();
            } else {
                // Invalid password
                echo "<script>alert('Invalid password! Please try again.'); window.history.back();</script>";
            }
        } else {
            // Unexpected number of rows (either no user found or multiple users, which is an error)
            echo "<script>alert('An error occurred: multiple users found with the same credentials.'); window.history.back();</script>";
        }

        // Close statement
        $stmt->close();
    } else {
        // Error preparing the SQL query
        echo "<script>alert('Error preparing query: " . $conn->error . "'); window.history.back();</script>";
    }

    // Close the database connection
    $conn->close();
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}
