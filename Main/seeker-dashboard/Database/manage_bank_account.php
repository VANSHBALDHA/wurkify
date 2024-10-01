<?php
session_start();

// Include the database configuration file
include 'config.php';

// Initialize a variable for the message
$message = "";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../seeker/seekerlogin.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if bank details already exist for the user
$sql = "SELECT id FROM user_accounts WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User already has bank details
        $message = "Bank details already submitted.";
        // Redirect to the settings page with a message
        header("Location: ../pages/settings.php?message=" . urlencode($message));
        exit();
    }
    $stmt->close();
} else {
    $message = "Error checking existing bank details: " . $conn->error;
    // Redirect to the settings page with an error message
    header("Location: ../pages/settings.php?message=" . urlencode($message));
    exit();
}

// Retrieve and validate form data
$account_number = trim($_POST['account_number']);
$ifsc_code = trim($_POST['ifsc_code']);
$bank_name = trim($_POST['bank_name']);
$branch_name = trim($_POST['branch_name']);
$account_holder_name = trim($_POST['account_holder_name']);
$upi_id = trim($_POST['upi_id']);
$upi_number = trim($_POST['upi_number']);

// Validate required fields
if (empty($account_number) || empty($ifsc_code) || empty($bank_name) || empty($branch_name) || empty($account_holder_name)) {
    $message = "All fields are required.";
    header("Location: ../pages/settings.php?message=" . urlencode($message));
    exit();
}

// Optionally, validate specific formats (e.g., account number, IFSC code, UPI ID, etc.)
// Example: Validate UPI ID format (if needed)
// if (!preg_match('/^[a-zA-Z0-9@.]+$/', $upi_id)) {
//     $message = "Invalid UPI ID format.";
//     header("Location: ../pages/settings.php?message=" . urlencode($message));
//     exit();
// }

// Prepare and bind
$sql = "INSERT INTO user_accounts (user_id, account_number, ifsc_code, bank_name, branch_name, account_holder_name, upi_id, upi_number) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssssssss", $user_id, $account_number, $ifsc_code, $bank_name, $branch_name, $account_holder_name, $upi_id, $upi_number);

    // Execute the query
    if ($stmt->execute()) {
        $message = "Account details have been saved successfully.";
        // Redirect to the settings page with a success message
        header("Location: ../pages/settings.php?message=" . urlencode($message));
        exit();
    } else {
        $message = "Error: " . $stmt->error;
        // Redirect to the settings page with an error message
        header("Location: ../pages/settings.php?message=" . urlencode($message));
        exit();
    }

    // Close statement
    $stmt->close();
} else {
    $message = "Error preparing the query: " . $conn->error;
    // Redirect to the settings page with an error message
    header("Location: ../pages/settings.php?message=" . urlencode($message));
    exit();
}

// Close connection
$conn->close();
?>
