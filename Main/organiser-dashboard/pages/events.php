<?php
session_start();
include('../Database/config.php'); // Include your database connection file

// Check if the user is logged in 
if (!isset($_SESSION['user_id'])) {
  header('Location: ../seeker/seekerlogin.html');
  exit();
}

$user_id = $_SESSION['user_id'];
// Fetch basic user details from the 'wurkify_user' table
$sql_user = "SELECT username, email FROM `wurkify_user` WHERE user_id = ? AND role = 'organizer'";
if ($stmt_user = $conn->prepare($sql_user)) {
  $stmt_user->bind_param("i", $user_id);
  if ($stmt_user->execute()) {
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows === 1) {
      $user = $result_user->fetch_assoc();

      // Fetch profile picture from the 'profile_pictures' table
      $sql_picture = "SELECT file_name FROM organiser_profile_pictures WHERE user_id = ?";
      if ($stmt_picture = $conn->prepare($sql_picture)) {
        $stmt_picture->bind_param("i", $user_id);
        $stmt_picture->execute();
        $result_picture = $stmt_picture->get_result();

        if ($result_picture->num_rows === 1) {
          $picture = $result_picture->fetch_assoc();
          $profile_picture = $picture['file_name']
            ? '../organiser_photos/' . $picture['file_name']
            : '../default.jpeg';
        } else {
          $profile_picture = '../default.jpeg';
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
  <title>Projects</title>
</head>

<body>
  <div class="page-content">
    <div class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-xmark xmark"></i>
        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
      </div>
      <ul>
        <li><a href="../index.php" class="sidebar-link"><i class="fa-solid fa-tachometer-alt fa-fw"></i><span>Dashboard</span></a></li>
        <li><a href="./Profile.php" class="sidebar-link"><i class="fa-solid fa-user fa-fw"></i><span>Profile</span></a></li>
        <li><a href="./events.php" class="sidebar-link"><i class="fa-solid fa-calendar-day fa-fw"></i><span>Events</span></a></li>
        <li><a href="./eventstatus.php" class="sidebar-link"><i class="fa-solid fa-calendar-check fa-fw"></i><span>Event Status</span></a></li>
        <li><a href="../Applicants.php" class="sidebar-link"><i class="fa-solid fa-credit-card fa-fw"></i><span>Applicants</span></a></li>
        <li><a href="./pricing.php" class="sidebar-link"><i class="fa-solid fa-tags fa-fw"></i><span>Pricing</span></a></li>
        <li><a href="./feedback.php" class="sidebar-link"><i class="fa-solid fa-comment-dots fa-fw"></i><span>Feedback</span></a></li>
        <li><a href="./settings.php" class="sidebar-link"><i class="fa-solid fa-cog fa-fw"></i><span>Settings</span></a></li>
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
          <h1>Projects</h1>
        </div>
        <div class="">
          <!-- <div class="box">
            <div class="box-section1">
              <div class="box-title">
                <h2>Event Registration Form</h2>
                <p>Submit the details of your event</p>
              </div>
            </div>
            <div class="general-info-section2">
              <form action="../Database/event_register.php" method="post">
                <label for="event_name" style="display: block; margin-bottom: 5px;">Event Name</label>
                <input type="text" name="event_name" id="event_name" placeholder="Enter event name"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required />

                <label for="event_date" style="display: block; margin-bottom: 5px;">Event Date</label>
                <input type="date" name="event_date" id="event_date"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required />

                <label for="shift_time" style="display: block; margin-bottom: 5px;">Shift Time</label>
                <input type="text" name="shift_time" id="shift_time" placeholder="Enter shift time"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required />

                <label for="dress_code" style="display: block; margin-bottom: 5px;">Dress Code</label>
                <select name="dress_code" id="dress_code" onchange="toggleDressCodeDetails()"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required>
                  <option value="" disabled selected>Is there a dress code?</option>
                  <option value="No">No</option>
                  <option value="Yes">Yes</option>
                </select>

                <div id="dress_code_details" style="display:none;">
                  <label for="dress_code_desc" style="display: block; margin-bottom: 5px;">If Yes, describe</label>
                  <input type="text" name="dress_code_desc" id="dress_code_desc" placeholder="Describe the dress code"
                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" />
                </div>

                <label for="payment_amount" style="display: block; margin-bottom: 5px;">Payment Amount</label>
                <input type="number" name="payment_amount" id="payment_amount" placeholder="Enter payment amount"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required oninput="validateNumber(this)" />

                <label for="clearance_days" style="display: block; margin-bottom: 5px;">Payment Clearance (Days)</label>
                <input type="number" name="clearance_days" id="clearance_days" placeholder="Enter days for payment clearance"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required oninput="validateNumber(this)" />

                <label for="work" style="display: block; margin-bottom: 5px;">Work</label>
                <input type="text" name="work" id="work" placeholder="Enter work description"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required />

                <label for="location" style="display: block; margin-bottom: 5px;">Location</label>
                <input type="text" name="location" id="location" placeholder="Select location"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required />


                <label for="required_members" style="display: block; margin-bottom: 5px;">Required Member Count</label>
                <input type="number" name="required_members" id="required_members" placeholder="Enter required members count"
                  style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                  required oninput="validateNumber(this)" />

                <label for="note" style="display: block; margin-bottom: 5px;">Additional Notes</label>
                <textarea name="note" id="note" placeholder="Enter any additional details" rows="4"
                  style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"></textarea>

                <input type="submit" value="Submit Event"
                  style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;" />
              </form>
            </div>
          </div> -->
          <div class="general-feedback-form-start">
            <div class="feedback-form create-event-form-organizer">
              <div class="create-event-title">
                <h3>Submit the details of your event</h3>
              </div>
              <hr class="feedback-form-head-row">
              <form action="../Database/event_register.php" method="post">
                <div class="add-event-form-input">
                  <div class="form-group">
                    <label for="event_name">Event Name</label>
                    <input type="text" id="event_name" name="event_name" placeholder="Enter event name" class="event-create-input" required>
                  </div>
                  <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date" placeholder="Enter event name" class="event-create-input" required>
                  </div>
                  <div class="form-group">
                    <label for="shift_time">Shift Time</label>
                    <input type="time" id="shift_time" name="shift_time" placeholder="Enter event name" class="event-create-input" required>
                  </div>
                </div>
                <div class="add-event-form-input">
                  <div class="form-group">
                    <label for="dress_code">Dress Code</label>
                    <div class="custom-select event-create-dress-code">
                      <select name="dress_code" id="dress_code" onchange="toggleDressCodeDetails()" required>
                        <option value="" disabled selected>Is there a dress code?</option>
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group" id="dress_code_details" style="display:none;">
                    <label for="dress_code_desc">If Yes, describe</label>
                    <input type="text" id="dress_code_desc" name="dress_code_desc" placeholder="Describe the dress code" class="event-create-input">
                  </div>
                  <div class="form-group">
                    <label for="payment_amount">Payment Amount</label>
                    <input type="number" id="payment_amount" name="payment_amount" placeholder="Enter payment amount" class="event-create-input" required oninput="validateNumber(this)">
                  </div>
                  <div class="form-group">
                    <label for="clearance_days">Payment Clearance (Days)</label>
                    <input type="number" id="clearance_days" name="clearance_days" placeholder="Enter days for payment clearance" class="event-create-input" required oninput="validateNumber(this)">
                  </div>
                </div>
                <div class="add-event-form-input">
                  <div class="form-group">
                    <label for="work">Work</label>
                    <input type="text" id="work" name="work" placeholder="Enter work description" class="event-create-input" required>
                  </div>
                  <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="Enter location" class="event-create-input" required>
                  </div>
                  <div class="form-group">
                    <label for="required_members">Required Member Count</label>
                    <input type="number" id="required_members" name="required_members" placeholder="Enter required member count" class="event-create-input" required oninput="validateNumber(this)">
                  </div>
                </div>
                <div class="form-group">
                  <label for="note">Additional Notes</label>
                  <textarea id="note" name="note" class="describe_text" placeholder="Enter any additional notes" required></textarea>
                </div>
                <!-- <input type="submit" value="Submit Event" /> -->
                <div class="submit-new-event-btn">
                  <button type="submit">Submit Event</button>
                </div>
              </form>
            </div>
          </div>
    </main>
  </div>
  <script src="../js/main.js"></script>
  <script src="../js/dresscode.js"></script>
</body>

</html>