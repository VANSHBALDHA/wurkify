<?php
include 'config.php';

// Start session and check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('User not logged in.'); window.location.href = '../seeker/seekerlogin.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Sanitize and retrieve form data
$event_name = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_STRING);
$event_date = filter_input(INPUT_POST, 'event_date', FILTER_SANITIZE_STRING);
$shift_time = filter_input(INPUT_POST, 'shift_time', FILTER_SANITIZE_STRING);
$dress_code = filter_input(INPUT_POST, 'dress_code', FILTER_SANITIZE_STRING);
$dress_code_desc = filter_input(INPUT_POST, 'dress_code_desc', FILTER_SANITIZE_STRING);
$payment_amount = filter_input(INPUT_POST, 'payment_amount', FILTER_VALIDATE_FLOAT);
$clearance_days = filter_input(INPUT_POST, 'clearance_days', FILTER_VALIDATE_INT);
$work = filter_input(INPUT_POST, 'work', FILTER_SANITIZE_STRING);
$location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
$required_members = filter_input(INPUT_POST, 'required_members', FILTER_VALIDATE_INT);
$note = filter_input(INPUT_POST, 'note', FILTER_SANITIZE_STRING);

// Function to handle error and redirect back
function handle_error($message) {
    echo "<script>alert('Error: $message'); window.history.back();</script>";
    exit();
}

// Validate required fields
if (empty($event_name) || empty($event_date) || empty($shift_time) || empty($payment_amount) || empty($clearance_days) || empty($work) || empty($location) || empty($required_members)) {
    handle_error('All required fields must be filled.');
}

// Prepare and bind the SQL statement
$sql = "INSERT INTO event_registration 
        (user_id, event_name, event_date, shift_time, dress_code, dress_code_desc, payment_amount, clearance_days, work, location, required_members, note, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("isssssssssss", $user_id, $event_name, $event_date, $shift_time, $dress_code, $dress_code_desc, $payment_amount, $clearance_days, $work, $location, $required_members, $note);
    
    if ($stmt->execute()) {
        echo "<script>alert('Event registered successfully!'); window.location.href = '../pages/events.php';</script>";
    } else {
        handle_error($stmt->error);
    }

    $stmt->close();
} else {
    handle_error($conn->error);
}

// Close connections
$conn->close();
?>
