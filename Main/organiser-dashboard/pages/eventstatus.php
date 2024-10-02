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

// Fetch events registered by the user, including new fields and sort by status
$sql = "SELECT event_id, event_name, event_date, shift_time, dress_code, dress_code_desc, clearance_days, work, note, payment_amount, location, required_members, status 
        FROM event_registration 
        WHERE user_id = ?
        ORDER BY CASE status
            WHEN 'Pending' THEN 1
            WHEN 'Completed' THEN 2
            ELSE 3
        END";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
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
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Event Status</title>
    <style>
        .main-content {
            padding: 20px;
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
            transition: box-shadow 0.3s ease;
        }

        .event-box:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .event-card-body p {
            margin: 0 0 10px;
        }

        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }

        .status-Completed {
            color: #f39c12;
            font-weight: bold;
        }

        .status-default {
            color: #000;
            font-weight: bold;
        }

        .status-select {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }

        .update-button,
        .delete-button {
            background-color: #0075ff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .update-button:hover,
        .delete-button:hover {
            background-color: #005bb5;
        }

        .delete-button {
            background-color: #e74c3c;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }

        .status-pending {
            color: #f39c12;
            /* Orange color for Pending */
            font-weight: bold;
        }

        .status-completed {
            color: #28a745;
            /* Green color for Completed */
            font-weight: bold;
        }

        .status-default {
            color: #000;
            font-weight: bold;
        }
    </style>
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
        <li style="list-style: none; text-align:center; width:100%; margin-bottom:15px;"><a href="../logout.php" class="logout-button logout-btn-sidebar">Logout <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left:10px;"></i></a></li>
        </div>
        <main>
            <div class="header">
                <i class="fa-solid fa-bars bar-item"></i>
                <!-- <div class="search">
                    <input type="search" placeholder="Type A Keyword">
                </div> -->
                <div class="profile">
                    <span class="bell"><i class="fa-regular fa-bell fa-lg"></i></span>
                    <div class="header-email-name">
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="header-img-round" />
                </div>
            </div>
            <div class="main-content">
                <div class="title">
                    <h1>Event Status</h1>
                </div>
                <div class="">
                    <?php if (count($events) > 0): ?>
                        <div class="event-card-box">
                            <?php foreach ($events as $event): ?>
                                <div class="card organization-user-event-status">
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
                                            <span>Note:</span>
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
                                            <span>Status:</span>
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
                                        <hr>
                                        <div class="custom-select event-status-update-btn">
                                            <form action="../Database/update_event_status.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                                                <div class="event-status-option">
                                                    <select name="status" onchange="this.form.submit()" required>
                                                        <option value="Pending" <?php echo $event['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="Completed" <?php echo $event['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="pure-button-primary">Update</button>
                                            </form>
                                            <div class="delete-update-event-btn">
                                                <form action="delete_event.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                                                    <button type="submit" class="pure-button-red" onclick="return confirm('Are you sure you want to delete this event?');">Delete</button>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- <div class="event-box">
                                <div class="event-card-body">
                                    <h4><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                    <p><strong>Shift Time:</strong> <?php echo htmlspecialchars($event['shift_time']); ?></p>
                                    <p><strong>Dress Code:</strong> <?php echo htmlspecialchars($event['dress_code']); ?></p>
                                    <p><strong>Dress Code Description:</strong> <?php echo htmlspecialchars($event['dress_code_desc']); ?></p>
                                    <p><strong>Clearance Day:</strong> <?php echo htmlspecialchars($event['clearance_days']); ?></p>
                                    <p><strong>Work:</strong> <?php echo htmlspecialchars($event['work']); ?></p>
                                    <p><strong>Notes:</strong> <?php echo htmlspecialchars($event['note']); ?></p>
                                    <p><strong>Payment Amount:</strong> ₹<?php echo htmlspecialchars($event['payment_amount']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                    <p><strong>Required Members:</strong> <?php echo htmlspecialchars($event['required_members']); ?></p>
                                    <p class="<?php echo strtolower($event['status']) == 'pending' ? 'status-pending' : 'status-completed'; ?>">
                                        Status: <?php echo htmlspecialchars($event['status']); ?>
                                    </p>
                                    <form action="../Database/update_event_status.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                            <option value="Pending" <?php echo $event['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Completed" <?php echo $event['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                        <button type="submit" class="update-button">Update</button>
                                    </form>
                                    <form action="delete_event.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                                        <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this event?');">Delete</button>
                                    </form>
                                </div>
                            </div> -->

                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No events found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="../js/script.js"></script>
</body>

</html>