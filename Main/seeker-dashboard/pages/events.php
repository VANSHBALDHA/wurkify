<?php
session_start();
include '../Database/config.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  echo "<script>alert('User not logged in.'); window.location.href = '../seeker/seekerlogin.html';</script>";
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

// Fetch all events with organizer details (updated columns)
$sql = "SELECT event_id,event_name, event_date, shift_time, dress_code, dress_code_desc, clearance_days, work, note, payment_amount, location, required_members, status
        FROM event_registration
        ORDER BY CASE status
            WHEN 'Pending' THEN 1
            WHEN 'Confirmed' THEN 2
            ELSE 3
        END";

if ($stmt = $conn->prepare($sql)) {
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $events = $result->fetch_all(MYSQLI_ASSOC);
  } else {
    $events = [];
  }
  $stmt->close();
} else {
  die('Error preparing events query: ' . $conn->error);
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
  <link rel="stylesheet" href="../css/all.min.css" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
  <!-- <link
      href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&amp;display=swap"
      rel="stylesheet"
    /> -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <title>Courses</title>
</head>
<style>
  .main-content {
    padding: 20px;
    /* background-color: #f9f9f9; */
  }

  .title h1 {
    font-size: 24px;
    margin-bottom: 20px;
  }

  .events-boxes {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: space-around;
  }

  .event-box {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #fff;
    padding: 20px;
    width: 100%;
    max-width: 350px;
    position: relative;
    margin: 10px;
  }

  .event-card-body p {
    margin: 5px 0;
  }

  .status-pending {
    color: #f39c12;
    font-weight: bold;
  }

  .status-confirmed {
    color: #27ae60;
    font-weight: bold;
  }

  .status-default {
    color: #000;
    font-weight: bold;
  }

  .apply-button {
    padding: 5px 10px;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-align: center;
    display: block;
    margin-top: 10px;
  }

  .apply-button:hover {
    background-color: #218838;
  }
</style>

<body>
  <div class="page-content">
    <div class="sidebar">
      <div class="sidebar-content">
        <div class="brand">
          <i class="fa-solid fa-xmark xmark"></i>
          <!-- <h3><?php echo htmlspecialchars($user['username']); ?></h3> -->
          <img src="../images/logo-name-transparent.png" alt="wurkify-logo" style="width: 150px; height:auto; margin-bottom:19px;" />
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
      <li style="list-style: none; text-align:center; width:100%; margin-bottom:15px;"><a href="../logout.php" class="logout-button logout-btn-sidebar">Logout <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left:10px;"></i></a></li>

    </div>
    <main>
      <div class="header">
        <i class="fa-solid fa-bars bar-item"></i>
        <!-- <div class="search">
          <input type="search" placeholder="Type A Keyword" />
        </div> -->

        <div class="profile">
          <span class="bell"><i class="fa-regular fa-bell fa-lg"></i></span>
          <div class="header-email-name">
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <span><?php echo htmlspecialchars($user['username']); ?></span>
          </div>
          <img src="<?php echo htmlspecialchars($picture_url); ?>" alt="No Image" class="header-img-round" />
        </div>
      </div>

      <div class="main-content">
        <div class="title">
          <h1>Events</h1>
        </div>
        <?php if (count($events) > 0): ?>
          <div class="event-card-box">
            <?php foreach ($events as $event): ?>
              <div class="card">
                <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
                <div class="info">
                  <div class="info-item">
                    <span>Date:</span>
                    <span><?php echo htmlspecialchars($event['event_date']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Shift Time:</span>
                    <span><?php echo htmlspecialchars($event['shift_time']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Dress Code:</span>
                    <span><?php echo htmlspecialchars($event['dress_code']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Dress Code Description:</span>
                    <span><?php echo htmlspecialchars($event['dress_code_desc']); ?></span>
                    <span></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Clearance Day:</span>
                    <span><?php echo htmlspecialchars($event['clearance_days']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Work:</span>
                    <span><?php echo htmlspecialchars($event['work']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Notes:</span>
                    <span><?php echo htmlspecialchars($event['note']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Payment Amount:</span>
                    <span><?php echo htmlspecialchars($event['payment_amount']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Location:</span>
                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Required Members:</span>
                    <span><?php echo htmlspecialchars($event['required_members']); ?></span>
                  </div>
                  <hr>
                  <div class="info-item">
                    <span>Application:</span>
                    <?php
                    $status = htmlspecialchars($event['status']);
                    if ($status === 'Pending'): ?>
                      <span class="status-pending badge-ui">
                        <?php echo $status; ?>
                      </span>
                    <?php elseif ($status === 'Confirmed'): ?>
                      <span class="status-confirmed badge-ui">
                        <?php echo $status; ?>
                      </span>
                    <?php else: ?>
                      <span class="status-default badge-ui">
                        <?php echo $status; ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <form id="apply-form-<?php echo htmlspecialchars($event['event_id']); ?>" action="../Database/apply_for_event.php" method="post">
                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                    <button type="button" onclick="confirmApply('<?php echo htmlspecialchars($event['event_id']); ?>')" class="apply-btn">
                      Apply Now
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="no-event-text">No Event Found</p>
        <?php endif; ?>

      </div>
    </main>
    <script src="../js/script.js"></script>
    <script>
      function confirmApply(eventId) {
        if (confirm('Are you sure you want to apply for this event?')) {
          document.getElementById('apply-form-' + eventId).submit();
        }
      }
    </script>
</body>

</html>