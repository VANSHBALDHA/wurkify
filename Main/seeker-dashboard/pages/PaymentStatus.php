<?php
session_start();
include('../Database/config.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ../seeker/seekerlogin.html');
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details and ensure the role is 'seeker'
$sql_user = "SELECT username, email FROM wurkify_user WHERE user_id = ? AND role = 'seeker'";
if ($stmt_user = $conn->prepare($sql_user)) {
  $stmt_user->bind_param("i", $user_id); // Bind user_id here
  if (!$stmt_user->execute()) {
    handle_error('Error executing user query: ' . $stmt_user->error);
  }
  $result_user = $stmt_user->get_result();
  if ($result_user->num_rows === 1) {
    $user = $result_user->fetch_assoc();

    // Fetch profile picture URL
    $sql_picture = "SELECT file_path FROM seeker_profile_pictures WHERE user_id = ?";
    if ($stmt_picture = $conn->prepare($sql_picture)) {
      $stmt_picture->bind_param("i", $user_id);
      if (!$stmt_picture->execute()) {
        handle_error('Error executing profile picture query: ' . $stmt_picture->error);
      }

      $result_picture = $stmt_picture->get_result();
      if ($result_picture->num_rows === 1) {
        $picture = $result_picture->fetch_assoc();
        $picture_url = '../uploads/' . $picture['file_path'];
      } else {
        // Set default profile picture if no picture is found
        $picture_url = '../default.jpeg';
      }
      $stmt_picture->close();
    } else {
      handle_error('Error preparing profile picture query: ' . $conn->error);
    }

    // Add profile picture to user data
    $user['picture_url'] = $picture_url;
  } else {
    handle_error('User not found or not a seeker');
  }
  $stmt_user->close();
} else {
  handle_error('Error preparing user details query: ' . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
  <!-- <link
      href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&amp;display=swap"
      rel="stylesheet"
    /> -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <title>Friends</title>
</head>

<body>
  <div class="page-content">
    <div class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-xmark xmark"></i>
        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
      </div>
      <ul>
        <li>
          <a href="../index.php" class="sidebar-link">
            <i class="fa-solid fa-tachometer-alt fa-fw"></i>
            <span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="./Profile.php" class="sidebar-link">
            <i class="fa-solid fa-user fa-fw"></i><span>Profile</span>
          </a>
        </li>
        <li>
          <a href="./events.php" class="sidebar-link">
            <i class="fa-solid fa-calendar-day fa-fw"></i><span>Events</span>
          </a>
        </li>
        <li>
          <a href="./eventstatus.php" class="sidebar-link">
            <i class="fa-solid fa-calendar-check fa-fw"></i><span>Event Status</span>
          </a>
        </li>
        <li>
          <a href="./PaymentStatus.php" class="sidebar-link">
            <i class="fa-solid fa-credit-card fa-fw"></i><span>Payment Status</span>
          </a>
        </li>
        <li>
          <a href="./pricing.php" class="sidebar-link">
            <i class="fa-solid fa-tags fa-fw"></i><span>Pricing</span>
          </a>
        </li>
        <li>
          <a href="./feedback.php" class="sidebar-link">
            <i class="fa-solid fa-comment-dots fa-fw"></i><span>Feedback</span>
          </a>
        </li>
        <li>
          <a href="./settings.php" class="sidebar-link">
            <i class="fa-solid fa-cog fa-fw"></i><span>Settings</span>
          </a>
        </li>
      </ul>
    </div>
    <main>
      <div class="header">
        <i class="fa-solid fa-bars bar-item"></i>
        <div class="search">
          <input type="search" placeholder="Type A Keyword" />
        </div>

        <div class="profile">
          <span class="bell"><i class="fa-regular fa-bell fa-lg"></i></span>
          <img src="<?php echo htmlspecialchars($picture_url); ?>" alt="No Image" style="border-radius: 50%;" />
        </div>
      </div>
      <div class="main-content">
        <div class="title">
          <h1>Payments History</h1>
        </div>
      </div>
    </main>
  </div>
  <script src="../js/script.js"></script>
</body>

</html>