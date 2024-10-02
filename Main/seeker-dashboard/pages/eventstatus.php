<?php
session_start();
include('../Database/config.php'); // Include your database connection file

// Function to handle errors
function handle_error($message)
{
    echo "<script>
            alert('$message');
            window.history.back();
          </script>";
    exit();
}

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

// Fetch events that the user has applied for
$sql = "SELECT e.event_name, e.event_date, e.shift_time, e.dress_code, e.dress_code_desc, 
               e.payment_amount, e.clearance_days, e.work, e.location, e.required_members, e.note, ea.status
        FROM event_registration e
        JOIN event_applications ea ON e.event_id = ea.event_id
        WHERE ea.user_id = ?"; // Filter based on user_id from event_applications

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    handle_error('Error fetching applied events: ' . $conn->error);
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
    <title>Applied Events</title>
</head>

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
                <!-- Sidebar links -->
                <li><a href="../index.php" class="sidebar-link"><i class="fa-solid fa-tachometer-alt fa-fw"></i><span>Dashboard</span></a></li>
                <li><a href="./Profile.php" class="sidebar-link"><i class="fa-solid fa-user fa-fw"></i><span>Profile</span></a></li>
                <li><a href="./events.php" class="sidebar-link"><i class="fa-solid fa-calendar-day fa-fw"></i><span>Events</span></a></li>
                <li><a href="./eventstatus.php" class="sidebar-link"><i class="fa-solid fa-calendar-check fa-fw"></i><span>Event Status</span></a></li>
                <li><a href="./PaymentStatus.php" class="sidebar-link"><i class="fa-solid fa-credit-card fa-fw"></i><span>Payment Status</span></a></li>
                <li><a href="./pricing.php" class="sidebar-link"><i class="fa-solid fa-tags fa-fw"></i><span>Pricing</span></a></li>
                <li><a href="./feedback.php" class="sidebar-link"><i class="fa-solid fa-comment-dots fa-fw"></i><span>Feedback</span></a></li>
                <li><a href="./settings.php" class="sidebar-link"><i class="fa-solid fa-cog fa-fw"></i><span>Settings</span></a></li>
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
                    <img src="<?php echo htmlspecialchars($user['picture_url']); ?>" alt="Profile Picture" class="header-img-round" />
                </div>
            </div>
            <div class="main-content">
                <div class="title">
                    <h1>Applied Events</h1>
                </div>
                <!-- <div class="courses-boxes">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <div class="courses-box">
                                <div class="courses-card-body">
                                    <h4><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                    <p>Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
                                    <p>Shift Time: <?php echo htmlspecialchars($event['shift_time']); ?></p>
                                    <p>Dress Code: <?php echo htmlspecialchars($event['dress_code']); ?></p>
                                    <?php if ($event['dress_code_desc']): ?>
                                        <p>Dress Code Description: <?php echo htmlspecialchars($event['dress_code_desc']); ?></p>
                                    <?php endif; ?>
                                    <p>Payment Amount: <?php echo htmlspecialchars($event['payment_amount']); ?></p>
                                    <p>Clearance Days: <?php echo htmlspecialchars($event['clearance_days']); ?></p>
                                    <p>Work: <?php echo htmlspecialchars($event['work']); ?></p>
                                    <p>Location: <?php echo htmlspecialchars($event['location']); ?></p>
                                    <p>Required Members: <?php echo htmlspecialchars($event['required_members']); ?></p>
                                    <p>Note: <?php echo htmlspecialchars($event['note']); ?></p>
                                    <p>Status: <?php echo htmlspecialchars($event['status']); ?></p>
                                </div>
                                <div class="courses-card-footer">
                                    <span><i class="fa-regular fa-user"></i> Applied</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No events applied yet.</p>
                    <?php endif; ?>
                </div> -->
                <?php if (!empty($events)): ?>
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
                                    <?php if ($event['dress_code_desc']): ?>
                                        <div class="info-item">
                                            <span>Dress Code Description:</span>
                                            <span><?php echo htmlspecialchars($event['dress_code_desc']); ?></span>
                                            <span></span>
                                        </div>
                                        <hr>
                                    <?php endif; ?>
                                    <div class="info-item">
                                        <span>Payment Amount:</span>
                                        <span><?php echo htmlspecialchars($event['payment_amount']); ?></span>
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
                                        <span>Note:</span>
                                        <span><?php echo htmlspecialchars($event['note']); ?></span>
                                    </div>
                                    <hr>
                                    <div class="info-item">
                                        <span>Status:</span>
                                        <span><?php echo htmlspecialchars($event['status']); ?></span>
                                    </div>
                                    <hr>
                                    <div class="event-status-applied">
                                        <div class="event-status-information">
                                            <i class="fa-regular fa-user"></i><span>Applied</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-event-text">No Events Applied Yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="../js/script.js"></script>
</body>

</html>