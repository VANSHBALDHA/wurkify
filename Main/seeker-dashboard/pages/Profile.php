<?php
session_start();
include('../Database/config.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../seeker/seekerlogin.html');
    exit();
}

// User ID from session
$user_id = $_SESSION['user_id'];

// Function to handle and log errors
function handle_error($message)
{
    echo "<script>alert('$message');</script>";
    exit();
}
// Fetch user details
$user_id = $_SESSION['user_id']; // Use 'user_id' as it's the correct session variable
// Fetch user details from 'wurkify_user' table
$sql_user = "SELECT username, email FROM `wurkify_user` WHERE user_id = ?";
if ($stmt_user = $conn->prepare($sql_user)) {
    $stmt_user->bind_param("i", $user_id); // Bind user_id here
    if (!$stmt_user->execute()) {
        handle_error('Error executing user query: ' . $stmt_user->error);
    }
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 1) {
        $user = $result_user->fetch_assoc();

        // Now fetch the profile picture from 'picture_urls' table
        $sql_picture = "SELECT file_name FROM seeker_profile_pictures WHERE user_id = ?";
        if ($stmt_picture = $conn->prepare($sql_picture)) {
            $stmt_picture->bind_param("i", $user_id);
            if (!$stmt_picture->execute()) {
                handle_error('Error executing profile picture query: ' . $stmt_picture->error);
            }
            $result_picture = $stmt_picture->get_result();
            if ($result_picture->num_rows === 1) {
                $picture = $result_picture->fetch_assoc();
                $picture_url = '../seeker_photos/' . $picture['file_name'];
            } else {
                // Set default profile picture if no picture is found
                $picture_url = '../default.jpeg';
            }
            $stmt_picture->close();
        } else {
            handle_error('Error preparing profile picture query: ' . $conn->error);
        }

        // Add profile picture to user data
        $user['file_name'] = $picture_url;
    } else {
        handle_error('User not found');
    }
    $stmt_user->close();
} else {
    handle_error('Error preparing user details query: ' . $conn->error);
}


// Fetch the social media details
$sql_social = "SELECT twitter_username, facebook_username, linkedin_username, youtube_username FROM user_social WHERE user_id = ?";
if ($stmt_social = $conn->prepare($sql_social)) {
    $stmt_social->bind_param("i", $user_id);
    if (!$stmt_social->execute()) {
        handle_error('Error executing social media query: ' . $stmt_social->error);
    }
    $result_social = $stmt_social->get_result();
    $social_media = $result_social->num_rows === 1 ? $result_social->fetch_assoc() : [
        'twitter_username' => '',
        'facebook_username' => '',
        'linkedin_username' => '',
        'youtube_username' => ''
    ];
    $stmt_social->close();
} else {
    handle_error('Error preparing social media query: ' . $conn->error);
}

// Fetch general information
$sql_general = "SELECT first_name, last_name, phone_number, dob, age, gender, country, state FROM user_general_info WHERE user_id = ?";
if ($stmt_general = $conn->prepare($sql_general)) {
    $stmt_general->bind_param("i", $user_id);
    if (!$stmt_general->execute()) {
        handle_error('Error executing general info query: ' . $stmt_general->error);
    }
    $result_general = $stmt_general->get_result();
    $general_info = $result_general->num_rows === 1 ? $result_general->fetch_assoc() : [
        'phone_number' => 'N/A',
        'first_name' => 'N/A',
        'last_name' => 'N/A',
        'age' => 'N/A',
        'dob' => 'N/A',
        'gender' => 'N/A',
        'country' => 'N/A',
        'state' => 'N/A'
    ];
    $stmt_general->close();
} else {
    handle_error('Error preparing general info query: ' . $conn->error);
}

// Fetch experience details
$sql_experience = "SELECT job_title, company_name, location, start_date, end_date, description, skills, employment_type, achievements FROM user_experience WHERE user_id = ?";
if ($stmt_experience = $conn->prepare($sql_experience)) {
    $stmt_experience->bind_param("i", $user_id);
    if (!$stmt_experience->execute()) {
        handle_error('Error executing experience query: ' . $stmt_experience->error);
    }
    $result_experience = $stmt_experience->get_result();
    $experiences = [];
    while ($row = $result_experience->fetch_assoc()) {
        $experiences[] = $row;
    }
    $stmt_experience->close();
} else {
    handle_error('Error preparing experience query: ' . $conn->error);
}

// Fetch skills
$sql_skills = "SELECT skill_name FROM skills WHERE user_id = ?";
if ($stmt_skills = $conn->prepare($sql_skills)) {
    $stmt_skills->bind_param("i", $user_id);
    if (!$stmt_skills->execute()) {
        handle_error('Error executing skills query: ' . $stmt_skills->error);
    }
    $result_skills = $stmt_skills->get_result();
    $skills = [];
    while ($row = $result_skills->fetch_assoc()) {
        $skills[] = $row;
    }
    $stmt_skills->close();
} else {
    handle_error('Error preparing skills query: ' . $conn->error);
}

// Fetch education details
$sql_education = "SELECT degree, institution, graduation_year FROM user_education WHERE user_id = ?";
if ($stmt_education = $conn->prepare($sql_education)) {
    $stmt_education->bind_param("i", $user_id);
    if (!$stmt_education->execute()) {
        handle_error('Error executing education query: ' . $stmt_education->error);
    }
    $result_education = $stmt_education->get_result();
    $education = $result_education->num_rows > 0 ? $result_education->fetch_assoc() : [
        'degree' => 'N/A',
        'institution' => 'N/A',
        'graduation_year' => 'N/A'
    ];
    $stmt_education->close();
} else {
    handle_error('Error preparing education query: ' . $conn->error);
}

// Fetch body criteria
$sql_body_criteria = "SELECT height, weight FROM body_criteria WHERE user_id = ?";
if ($stmt_body_criteria = $conn->prepare($sql_body_criteria)) {
    $stmt_body_criteria->bind_param("i", $user_id);
    if (!$stmt_body_criteria->execute()) {
        handle_error('Error executing body criteria query: ' . $stmt_body_criteria->error);
    }
    $result_body_criteria = $stmt_body_criteria->get_result();
    $body_criteria = $result_body_criteria->num_rows === 1 ? $result_body_criteria->fetch_assoc() : [
        'height' => 'N/A',
        'weight' => 'N/A'
    ];
    $stmt_body_criteria->close();
} else {
    handle_error('Error preparing body criteria query: ' . $conn->error);
}

// Assign variables
$height = $body_criteria['height'];
$weight = $body_criteria['weight'];
// Fetch identification info
$sql_identification = "SELECT aadhar, pan, address_line1, address_line2, city, state, zipcode FROM identification_info WHERE user_id = ?";
if ($stmt_identification = $conn->prepare($sql_identification)) {
    $stmt_identification->bind_param("i", $user_id);
    if (!$stmt_identification->execute()) {
        handle_error('Error executing identification info query: ' . $stmt_identification->error);
    }
    $result_identification = $stmt_identification->get_result();
    $identification_info = $result_identification->num_rows === 1 ? $result_identification->fetch_assoc() : [
        'aadhar' => 'N/A',
        'pan' => 'N/A',
        'address_line1' => 'N/A',
        'address_line2' => 'N/A',
        'city' => 'N/A',
        'state' => 'N/A',
        'zipcode' => 'N/A'
    ];
    $stmt_identification->close();
} else {
    handle_error('Error preparing identification info query: ' . $conn->error);
}

// Profile photo upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    $upload_dir = '../seeker_photos/';
    $upload_file = $upload_dir . basename($file['name']);
    $file_type = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($file_type, $allowed_types) && $file['size'] < 5000000) {
        if (move_uploaded_file($file['tmp_name'], $upload_file)) {
            // Retrieve current profile picture path
            $sql_current_pic = "SELECT picture_url FROM `wurkify_user` WHERE id = ?";
            if ($stmt_current_pic = $conn->prepare($sql_current_pic)) {
                $stmt_current_pic->bind_param("i", $user_id);
                if (!$stmt_current_pic->execute()) {
                    handle_error('Error retrieving current profile picture: ' . $stmt_current_pic->error);
                }
                $result_current_pic = $stmt_current_pic->get_result();
                $current_pic = $result_current_pic->fetch_assoc();
                $old_pic_path = '../seeker_photos/' . $current_pic['picture_url'];

                // Update profile picture path in database
                $sql_update_pic = "UPDATE `wurkify_user` SET picture_url = ? WHERE id = ?";
                if ($stmt_update_pic = $conn->prepare($sql_update_pic)) {
                    $new_pic_name = basename($file['name']);
                    $stmt_update_pic->bind_param("si", $new_pic_name, $user_id);
                    if ($stmt_update_pic->execute()) {
                        // Delete old picture if exists
                        if (file_exists($old_pic_path) && $current_pic['picture_url'] != 'default.jpeg') {
                            unlink($old_pic_path);
                        }
                        echo "<script>alert('Profile picture updated successfully');</script>";
                    } else {
                        handle_error('Error updating profile picture: ' . $stmt_update_pic->error);
                    }
                    $stmt_update_pic->close();
                } else {
                    handle_error('Error preparing profile picture update: ' . $conn->error);
                }
                $stmt_current_pic->close();
            } else {
                handle_error('Error preparing current profile picture query: ' . $conn->error);
            }
        } else {
            handle_error('Error moving uploaded file');
        }
    } else {
        handle_error('Invalid file type or size too large');
    }
}

// Close database connection
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
    <title>Profile</title>
</head>

<body>
    <div class="page-content">
        <div class="sidebar">
            <div class="brand">
                <i class="fa-solid fa-xmark xmark"></i>
                <h3> <?php echo htmlspecialchars($user['username']); ?></h3>
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
                    <h1>Profile</h1>
                </div>

                <!-- <div class="profile-box">
                    <div class="profile-info" style="text-align: center; padding: 20px; background-color: #f9f9f9; border-radius: 10px; border: 1px solid #ddd;">
                        <div style="position: relative; display: inline-block;">
                            <img src="<?php echo htmlspecialchars($picture_url); ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                            <a href="#" onclick="document.getElementById('upload-photo-form').style.display='block'; return false;" style="position: absolute; bottom: 0; right: 0; background: #fff; border-radius: 50%; padding: 5px; border: 1px solid #ddd; color: #333; text-decoration: none;">
                                <i class="fa-solid fa-pencil-alt fa-lg"></i>
                            </a>
                        </div>
                        <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($user['username']); ?></h3>
                        <div class="social-media-links" style="margin: 20px 0;">
                            <?php if (!empty($social_media['twitter_username'])): ?>
                                <a href="https://twitter.com/<?php echo htmlspecialchars($social_media['twitter_username']); ?>" target="_blank" rel="noopener noreferrer" style="margin: 0 10px; color: #1DA1F2;">
                                    <i class="fa-brands fa-twitter fa-3x"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($social_media['facebook_username'])): ?>
                                <a href="https://facebook.com/<?php echo htmlspecialchars($social_media['facebook_username']); ?>" target="_blank" rel="noopener noreferrer" style="margin: 0 10px; color: #1877F2;">
                                    <i class="fa-brands fa-facebook-f fa-3x"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($social_media['youtube_username'])): ?>
                                <a href="https://youtube.com/user/<?php echo htmlspecialchars($social_media['youtube_username']); ?>" target="_blank" rel="noopener noreferrer" style="margin: 0 10px; color: #FF0000;">
                                    <i class="fa-brands fa-youtube fa-3x"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($social_media['linkedin_username'])): ?>
                                <a href="https://linkedin.com/in/<?php echo htmlspecialchars($social_media['linkedin_username']); ?>" target="_blank" rel="noopener noreferrer" style="margin: 0 10px; color: #0A66C2;">
                                    <i class="fa-brands fa-linkedin fa-3x"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <form action="../Database/upload_image.php" method="post" enctype="multipart/form-data" id="upload-photo-form" style="display: none; margin-top: 20px; padding: 15px; border-radius: 10px; background-color: #fff; border: 1px solid #ddd;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                <input type="file" name="profile_picture" accept="image/*" style="padding: 5px; border-radius: 5px; border: 1px solid #ddd; width: 80%;">

                                <input type="submit" value="Change Profile Picture" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                            </div>
                        </form>


                        <a href="../logout.php" style="color: #0075ff; text-decoration: none; margin-top: 20px; display: inline-block;">LogOut</a>
                    </div>

                    <div class="profile-info-section2">
                        <div class="row">
                            <div class="general-information">
                                <h4>General Information</h4>
                                <div>
                                    <h5>Full Name:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($general_info['first_name']); ?> <?php echo htmlspecialchars($general_info['last_name']); ?></span>
                                </div>

                                <div>
                                    <h5>Date Of Birth:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($general_info['dob']); ?></span>
                                </div>
                            </div>
                            <div>
                                <h5>Gender:&nbsp;</h5>
                                <span><?php echo htmlspecialchars($general_info['gender']); ?></span>
                            </div>
                            <div>
                                <h5>Age:&nbsp;</h5>
                                <span><?php echo htmlspecialchars($general_info['age']); ?></span>
                            </div>
                            <div>
                                <h5>Country:&nbsp;</h5>
                                <span><?php echo htmlspecialchars($general_info['country']); ?></span>
                            </div>
                            <div>
                                <h5>State:&nbsp;</h5>
                                <span><?php echo htmlspecialchars($general_info['state']); ?></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="general-information">
                                <h4>Contact Information</h4>
                                <div>
                                    <h5>Email:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div>
                                    <h5>Phone:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($general_info['phone_number']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="general-information">
                                <h4>Identification Information</h4>
                                <div>
                                    <h5>Aadhar:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($identification_info['aadhar']); ?></span>
                                </div>
                                <div>
                                    <h5>PAN:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($identification_info['pan']); ?></span>
                                </div>
                                <div>
                                    <h5>Address Line 1:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($identification_info['address_line1']); ?></span>
                                </div>
                                <div>
                                    <h5>Address Line 2:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($identification_info['address_line2']); ?></span>
                                </div>
                                <div>
                                    <h5>City:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($identification_info['city']); ?></span>
                                </div>
                                <div>
                                    <h5>State:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($identification_info['state']); ?></span>
                                </div>
                                <div>
                                    <h5>Zipcode:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($identification_info['zipcode']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="general-information">
                                <h4>Education Information</h4>
                                <div>
                                    <h5>Degree:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($education['degree']); ?></span>
                                </div>
                                <div>
                                    <h5>Institute:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($education['institution']); ?></span>
                                </div>
                                <div>
                                    <h5>Graduation Year:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($education['graduation_year']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="general-information">
                                <h4>Body Metrics</h4>
                                <div>
                                    <h5>Height:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($height); ?> cm</span>
                                </div>
                                <div>
                                    <h5>Weight:&nbsp;</h5>
                                    <span><?php echo htmlspecialchars($weight); ?> kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
                <!-- <div class="main-content-boxes profile-main-content-boxes">
                    <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>My Skills</h2>
                                <p>Complete Skills List</p>
                            </div>
                        </div>
                        <div class="profile-skills" style="padding: 20px; max-width: 1200px; margin: 0 auto;">

                            <div class="row" style="display: flex; flex-wrap: wrap; gap: 15px;">
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 15px; width: 100%;">
                                    <?php
                                    if (!empty($skills)) {
                                        foreach ($skills as $skill) {
                                            echo '<li style="flex: 1 1 calc(50% - 15px); box-sizing: border-box; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background-color: #f9f9f9; color: #333;">' . htmlspecialchars($skill['skill_name']) . '</li>';
                                        }
                                    } else {
                                        echo '<li style="flex: 1 1 calc(50% - 15px); box-sizing: border-box; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background-color: #f9f9f9; color: #333;">No skills found</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="box latest-activities">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>Latest Experiences</h2>
                                <p>Recent Experiences Added by the User</p>
                            </div>
                        </div>
                        <div class="profile-latest-activities">
                            <?php foreach ($experiences as $experience): ?>
                                <div class="profile-latest-activities-row">
                                    <div class="row-info">
                                        <img src="../images/experience.avif" alt="" />
                                        <div>
                                            <span><?php echo htmlspecialchars($experience['employment_type']); ?></span>
                                            <h4><?php echo htmlspecialchars($experience['job_title']); ?> at <?php echo htmlspecialchars($experience['company_name']); ?></h4>
                                        </div>
                                    </div>
                                    <div class="row-history">
                                        <h4><?php echo htmlspecialchars(date('j F Y', strtotime($experience['start_date']))); ?></h4>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div> -->


                <!-- My Profile Name and Image Card Start -->
                <div class="profile-img-and-info">
                    <div class="profile-img-info">
                        <div class="card-container">
                            <div class="image-container">
                                <img class="round" src="<?php echo htmlspecialchars($picture_url); ?>" alt="Profile Picture" />
                                <a href="#" onclick="document.getElementById('upload-photo-form').style.display='block'; return false;" class="edit-icon"><i class="fas fa-pencil-alt"></i></a>
                            </div>
                            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                            <div class="social-icons">
                                <a href="https://twitter.com/<?php echo htmlspecialchars($social_media['twitter_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                                <a href="https://facebook.com/<?php echo htmlspecialchars($social_media['facebook_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="https://youtube.com/user/<?php echo htmlspecialchars($social_media['youtube_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer"><i class="fa-brands fa-youtube"></i></a>
                                <a href="https://linkedin.com/in/<?php echo htmlspecialchars($social_media['linkedin_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                            <div class="choose-img-box">
                                <form action="../Database/upload_image.php" method="post" enctype="multipart/form-data" id="upload-photo-form" style="display: none;" class="upload-form">
                                    <div class="upload-form-container">
                                        <input type="file" name="profile_picture" accept="image/*" class="file-input" required>
                                        <input type="submit" value="Change" class="submit-button">
                                    </div>
                                </form>
                            </div>

                            <a href="../logout.php" class="logout-button">Logout</a>
                        </div>
                    </div>

                    <!--My General Information Section -->
                    <div class="profile-basic-details-form">
                        <div class="general-info-head">
                            <h3>General Information</h3>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <h5>Full Name :</h5>
                                <span><?php echo htmlspecialchars($general_info['first_name']); ?> <?php echo htmlspecialchars($general_info['last_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>City :</h5>
                                <span><?php echo htmlspecialchars($identification_info['city']); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <h5>Date Of Birth :</h5>
                                <span><?php echo htmlspecialchars($general_info['dob']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>State :</h5>
                                <span><?php echo htmlspecialchars($general_info['state']); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <h5>Gender :</h5>
                                <span><?php echo htmlspecialchars($general_info['gender']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>Country :</h5>
                                <span><?php echo htmlspecialchars($general_info['country']); ?></span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <h5>Age :</h5>
                                <span><?php echo htmlspecialchars($general_info['age']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>Zip-Code :</h5>
                                <span><?php echo htmlspecialchars($identification_info['zipcode']); ?></span>
                            </div>
                        </div>
                        <div class="contact-info-row">
                            <div class="info-item">
                                <h5>Address Line 1 :</h5>
                                <span><?php echo htmlspecialchars($identification_info['address_line1']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>Address Line 2 :</h5>
                                <span><?php echo htmlspecialchars($identification_info['address_line2']); ?></span>
                            </div>
                        </div>

                    </div>

                    <!-- Contact Information Section -->
                    <div class="profile-basic-details-form">
                        <div class="general-info-head">
                            <h3>Contact Information</h3>
                        </div>
                        <div class="contact-info-row">
                            <div class="info-item">
                                <h5>Email :</h5>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>Phone :</h5>
                                <span><?php echo htmlspecialchars($general_info['phone_number']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-img-and-info">
                    <!-- Identification Information Section -->
                    <div class="profile-basic-details-form">
                        <div class="general-info-head">
                            <h3>Identification Information</h3>
                        </div>
                        <div class="contact-info-row">
                            <div class="info-item">
                                <h5>Aadhar No. :</h5>
                                <span><?php echo htmlspecialchars($identification_info['aadhar']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>PAN No. :</h5>
                                <span><?php echo htmlspecialchars($identification_info['pan']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Education Information Section -->
                    <div class="profile-basic-details-form">
                        <div class="general-info-head">
                            <h3>Education Information</h3>
                        </div>
                        <div class="contact-info-row">
                            <div class="info-item">
                                <h5>Degree :</h5>
                                <span><?php echo htmlspecialchars($education['degree']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>Institute :</h5>
                                <span><?php echo htmlspecialchars($education['institution']); ?></span>
                            </div>
                            <div class="info-item">
                                <h5>Graduation Year :</h5>
                                <span><?php echo htmlspecialchars($education['graduation_year']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Body Metrics Section -->
                    <div class="profile-basic-details-form">
                        <div class="general-info-head">
                            <h3>Body Metrics</h3>
                        </div>
                        <div class="contact-info-row">
                            <div class="info-item">
                                <h5>Height :</h5>
                                <span><?php echo htmlspecialchars($height); ?> Cm.</span>
                            </div>
                            <div class="info-item">
                                <h5>Weight :</h5>
                                <span><?php echo htmlspecialchars($weight); ?> Kg.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-info-skills-exp">
                    <!-- Skills Section -->
                    <div class="profile-basic-details-form">
                        <div class="general-info-head">
                            <h3>My Skills</h3>
                            <h5>Complete Skills List</h6>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <ul class="skills-list skil-list-profile-page">
                                    <?php
                                    if (!empty($skills)) {
                                        foreach ($skills as $skill) {
                                            echo '<li class="skill-item">'
                                                . '<strong class="skill-name">' . htmlspecialchars($skill['skill_name']) . '</strong>'
                                                . '</li>';
                                        }
                                    } else {
                                        echo '<li class="skill-item">No skills found</li>';
                                    }
                                    ?>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <!-- Experiences Section -->
                    <div class="profile-basic-details-form">
                        <div class="general-info-head">
                            <h3>Latest Experiences</h3>
                            <h5>Recent Experiences Added by the User</h6>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <?php foreach ($experiences as $experience): ?>
                                    <div class="profile-latest-activities-row profile-latest-exp-box">
                                        <div class="row-info" style="flex: 1; display: flex; align-items: center;">
                                            <img src="../images/experience.avif" alt="" style="width: 80px; height: 80px; border-radius: 50%; margin-right: 15px; object-fit: cover;" />
                                            <div>
                                                <span style="display: block; font-size: 0.9em; color: #777777;"><?php echo htmlspecialchars($experience['employment_type']); ?></span>
                                                <h4 style="margin: 5px 0; color: #333;"><?php echo htmlspecialchars($experience['job_title']); ?> at <?php echo htmlspecialchars($experience['company_name']); ?></h4>
                                            </div>
                                        </div>
                                        <div class="row-history" style="flex-shrink: 0;">
                                            <h4 style="margin: 0; color: #0075ff;"><?php echo htmlspecialchars(date('j F Y', strtotime($experience['start_date']))); ?></h4>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Profile Name and Image Card  End-->
            </div>
        </main>

    </div>
    <script src="../js/script.js"></script>
</body>

</html>