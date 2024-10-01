<?php
session_start();
include './Database/config.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  echo "<script>alert('User not logged in.'); window.location.href = '../seeker/seekerlogin.html';</script>";
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch basic user details from the 'wurkify_user' table
$sql_user = "SELECT username, email FROM wurkify_user WHERE user_id = ? AND role = 'organizer'";
if ($stmt_user = $conn->prepare($sql_user)) {
  $stmt_user->bind_param("i", $user_id);
  if ($stmt_user->execute()) {
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows === 1) {
      $user = $result_user->fetch_assoc();

      // Fetch profile picture from the 'organiser_profile_pictures' table
      $sql_picture = "SELECT file_name FROM organiser_profile_pictures WHERE user_id = ?";
      if ($stmt_picture = $conn->prepare($sql_picture)) {
        $stmt_picture->bind_param("i", $user_id);
        $stmt_picture->execute();
        $result_picture = $stmt_picture->get_result();

        if ($result_picture->num_rows === 1) {
          $picture = $result_picture->fetch_assoc();
          $profile_picture = $picture['file_name']
            ? '../organiser-dashboard/organiser_photos/' . $picture['file_name']
            : './default.jpeg';
        } else {
          $profile_picture = './default.jpeg';
        }

        $stmt_picture->close();
      } else {
        echo 'Error preparing SQL statement for profile picture: ' . $conn->error;
        exit();
      }

      // Set user details
      $username = $user['username'];
      $email = $user['email'];
    } else {
      echo 'User not found';
      exit();
    }

    $stmt_user->close();
  } else {
    echo 'Error executing user query: ' . $stmt_user->error;
    exit();
  }
} else {
  echo 'Error preparing SQL statement for user details: ' . $conn->error;
  exit();
}

// Fetch event applications and profile pictures of applicants
$sql = "SELECT u.username, u.email, gi.phone_number, gi.last_name, gi.first_name, gi.age, gi.gender, 
               u.user_id, e.event_id, e.event_name, e.event_date, e.shift_time, e.location,
               spp.file_name
        FROM event_applications ea
        JOIN wurkify_user u ON ea.user_id = u.user_id
        JOIN user_general_info gi ON u.user_id = gi.user_id
        JOIN event_registration e ON ea.event_id = e.event_id
        LEFT JOIN seeker_profile_pictures spp ON u.user_id = spp.user_id
        WHERE ea.organiser_id = ?
        ORDER BY e.event_id DESC";

if ($stmt = $conn->prepare($sql)) {
  $stmt->bind_param("i", $user_id); // Bind organizer ID
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $applications = $result->fetch_all(MYSQLI_ASSOC);
  } else {
    $applications = [];
  }
  $stmt->close();
} else {
  die('Error fetching applications: ' . $conn->error);
}

// Handle button actions if the request is made
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $applicant_id = $_POST['user_id'];
  $event_id = $_POST['event_id'];
  $action = $_POST['action']; // 'accept' or 'reject'

  // Prepare SQL for updating event applications
  if ($action === 'accept') {
    // Update the event application status to accepted
    $sql_accept = "UPDATE event_applications SET status = 'Accepted' WHERE user_id = ? AND event_id = ?";
    if ($stmt = $conn->prepare($sql_accept)) {
      $stmt->bind_param("ii", $applicant_id, $event_id);
      $stmt->execute();
      $stmt->close();
    }
  } elseif ($action === 'reject') {
    // Update the event application status to rejected
    $sql_reject = "UPDATE event_applications SET status = 'Rejected' WHERE user_id = ? AND event_id = ?";
    if ($stmt = $conn->prepare($sql_reject)) {
      $stmt->bind_param("ii", $applicant_id, $event_id);
      $stmt->execute();
      $stmt->close();
    }
  }
}
// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./css/all.min.css" />
  <link rel="stylesheet" href="./css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
  <!-- <link
      href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&amp;display=swap"
      rel="stylesheet"
    /> -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <title>Applicants</title>
  <style>
    /* Existing styles */
    .friends-box-card {
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 15px;
      margin: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
    }

    .friends-box-card-contact {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
    }

    .friends-box-card-contact i {
      cursor: pointer;
      color: #0075ff;
    }

    .friends-box-card-contact i:hover {
      color: #0056b3;
    }

    .friends-box-card-info img {
      border-radius: 50%;
      width: 80px;
      height: 80px;
      object-fit: cover;
      margin-bottom: 10px;
    }

    .friends-box-card-info h4 {
      margin: 10px 0;
      font-size: 18px;
      color: #333;
    }

    .friends-box-card-info p {
      color: #666;
    }

    .friends-box-card-footer {
      display: flex;
      justify-content: space-between;
      width: 100%;
      margin-top: 10px;
    }

    .friends-box-card-footer-buttons a {
      color: #0075ff;
      text-decoration: none;
      font-size: 14px;
      margin-right: 10px;
    }

    .friends-box-card-footer-buttons a:hover {
      text-decoration: underline;
    }

    .accept-button,
    .reject-button {
      background-color: #0075ff;
      color: white;
      padding: 5px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
    }

    .accept-button:hover {
      background-color: #0056b3;
    }

    .reject-button {
      background-color: #ff4d4d;
    }

    .reject-button:hover {
      background-color: #e60000;
    }

    /* New table styles */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: left;
    }

    th {
      background-color: #0075ff;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .table-footer {
      text-align: right;
      margin-top: 10px;
    }
  </style>

  <script>
    function confirmAction(action) {
      return confirm("Are you sure you want to " + action + " this application?");
    }
  </script>
</head>

<body>
  <div class="page-content">
    <div class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-xmark xmark"></i>
        <h3><?php echo htmlspecialchars($username); ?></h3>
      </div>
      <ul>
        <li><a href="index.php" class="sidebar-link"><i class="fa-solid fa-house fa-fw"></i><span>Dashboard</span></a></li>
        <li><a href="./pages/Profile.php" class="sidebar-link"><i class="fa-solid fa-user fa-fw"></i><span>Profile</span></a></li>
        <li><a href="./pages/events.php" class="sidebar-link"><i class="fa-solid fa-calendar-day fa-fw"></i><span>Events</span></a></li>
        <li><a href="./pages/eventstatus.php" class="sidebar-link"><i class="fa-solid fa-calendar-check fa-fw"></i><span>Event Status</span></a></li>
        <li><a href="Applicants.php" class="sidebar-link"><i class="fa-solid fa-credit-card fa-fw"></i><span>Applicants</span></a></li>
        <li><a href="./pages/pricing.php" class="sidebar-link"><i class="fa-solid fa-tag fa-fw"></i><span>Pricing</span></a></li>
        <li><a href="./pages/feedback.php" class="sidebar-link"><i class="fa-solid fa-comment-dots fa-fw"></i><span>Feedback</span></a></li>
        <li><a href="./pages/settings.php" class="sidebar-link"><i class="fa-solid fa-cog fa-fw"></i><span>Settings</span></a></li>
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
          <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="No Image" style="border-radius: 50%;" />
        </div>
      </div>
      <div class="main-content">
        <div class="title">
          <h1>Event Applications</h1>
        </div>
        <table>
          <thead>
            <tr>
              <th>Applicant Name</th>
              <th>Email</th>
              <th>Phone Number</th>
              <th>Event Name</th>
              <th>Event Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <!-- <tbody>
            <?php if (!empty($applications)): ?>
              <?php foreach ($applications as $application): ?>
                <tr>
                  <td><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></td>
                  <td><?php echo htmlspecialchars($application['email']); ?></td>
                  <td><?php echo htmlspecialchars($application['phone_number']); ?></td>
                  <td><?php echo htmlspecialchars($application['event_name']); ?></td>
                  <td><?php echo htmlspecialchars($application['event_date']); ?></td>
                  <td>
                    <form method="POST" onsubmit="return confirmAction('accept');">
                      <input type="hidden" name="user_id" value="<?php echo $application['user_id']; ?>">
                      <input type="hidden" name="event_id" value="<?php echo $application['event_id']; ?>">
                      <button type="submit" name="action" value="accept" class="accept-button">Accept</button>
                    </form>
                    <form method="POST" onsubmit="return confirmAction('reject');">
                      <input type="hidden" name="user_id" value="<?php echo $application['user_id']; ?>">
                      <input type="hidden" name="event_id" value="<?php echo $application['event_id']; ?>">
                      <button type="submit" name="action" value="reject" class="reject-button">Reject</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6">No applications found.</td>
              </tr>
            <?php endif; ?>
          </tbody> -->
          <tbody>
            <tr>
              <td>vansh patel</td>
              <td>vansh@gmail.com</td>
              <td>9737392505</td>
              <td>react.js developwer</td>
              <td>12-10-2024</td>
              <td>
                <form method="POST" onsubmit="return confirmAction('accept');">
                  <!-- <input type="hidden" name="user_id" value="<?php echo $application['user_id']; ?>"> -->
                  <!-- <input type="hidden" name="event_id" value="<?php echo $application['event_id']; ?>"> -->
                  <button type="submit" name="action" value="accept" class="accept-button">Accept</button>
                </form>
                <form method="POST" onsubmit="return confirmAction('reject');">
                  <!-- <input type="hidden" name="user_id" value="<?php echo $application['user_id']; ?>"> -->
                  <!-- <input type="hidden" name="event_id" value="<?php echo $application['event_id']; ?>"> -->
                  <button type="submit" name="action" value="reject" class="reject-button">Reject</button>
                </form>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="table-footer">
          <p>Total Applications: <?php echo count($applications); ?></p>
        </div>
      </div>
    </main>
  </div>
</body>

</html>