<?php
session_start();
include('../Database/config.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../seeker/seekerlogin.html');
    exit();
}

// Function to handle and log errors
function handle_error($message)
{
    echo "<script>alert('$message');</script>";
    exit();
}
// Fetch user details
$user_id = $_SESSION['user_id']; // Use 'user_id' as it's the correct session variable
// Fetch user details from 'wurkify_user' table
$sql_user = "SELECT username, email FROM `wurkify_user` WHERE user_id = ? AND role = 'organizer'";
if ($stmt_user = $conn->prepare($sql_user)) {
    $stmt_user->bind_param("i", $user_id); // Bind user_id here
    if (!$stmt_user->execute()) {
        handle_error('Error executing user query: ' . $stmt_user->error);
    }
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 1) {
        $user = $result_user->fetch_assoc();

        // Fetch the profile picture from 'organiser_profile_pictures' table
        $sql_picture = "SELECT file_name FROM organiser_profile_pictures WHERE user_id = ?";
        if ($stmt_picture = $conn->prepare($sql_picture)) {
            $stmt_picture->bind_param("i", $user_id);
            if (!$stmt_picture->execute()) {
                handle_error('Error executing profile picture query: ' . $stmt_picture->error);
            }
            $result_picture = $stmt_picture->get_result();
            if ($result_picture->num_rows === 1) {
                $picture = $result_picture->fetch_assoc();
                $picture_url = '../organiser_photos/' . $picture['file_name']; // Adjust path as needed
            } else {
                // Set default profile picture if no picture is found
                $picture_url = '../default.jpeg'; // Path to default picture
            }
            $stmt_picture->close();
        } else {
            handle_error('Error preparing profile picture query: ' . $conn->error);
        }

        // Add profile picture to user data
        $user['picture_url'] = $picture_url;
    } else {
        handle_error('User not found');
    }
    $stmt_user->close();
} else {
    handle_error('Error preparing user details query: ' . $conn->error);
}
// Fetch social media details from 'user_social' table
$sql_social = "SELECT twitter_username, facebook_username, linkedin_username, youtube_username FROM user_social WHERE user_id = ?";
if ($stmt_social = $conn->prepare($sql_social)) {
    $stmt_social->bind_param("i", $user_id);
    if (!$stmt_social->execute()) {
        handle_error('Error executing social media query: ' . $stmt_social->error);
    }
    $result_social = $stmt_social->get_result();
    if ($result_social->num_rows === 1) {
        $social_media = $result_social->fetch_assoc();
    } else {
        $social_media = [
            'twitter_username' => 'N/A',
            'facebook_username' => 'N/A',
            'linkedin_username' => 'N/A',
            'youtube_username' => 'N/A'
        ];
    }
    $stmt_social->close();
} else {
    handle_error('Error preparing social media query: ' . $conn->error);
}

// Fetch general information from 'user_general_info' table
$sql_general = "SELECT first_name, last_name, phone_number, dob, age, gender, country, state FROM organiser_general_info WHERE user_id = ?";
if ($stmt_general = $conn->prepare($sql_general)) {
    $stmt_general->bind_param("i", $user_id);
    if (!$stmt_general->execute()) {
        handle_error('Error executing general info query: ' . $stmt_general->error);
    }
    $result_general = $stmt_general->get_result();
    if ($result_general->num_rows === 1) {
        $general_info = $result_general->fetch_assoc();
    } else {
        $general_info = [
            'phone_number' => 'N/A',
            'first_name' => 'N/A',
            'last_name' => 'N/A',
            'age' => 'N/A',
            'dob' => 'N/A',
            'gender' => 'N/A',
            'country' => 'N/A',
            'state' => 'N/A'
        ];
    }
    $stmt_general->close();
} else {
    handle_error('Error preparing general info query: ' . $conn->error);
}

// Fetch experience details from 'user_experience' table
$sql_experience = "SELECT job_title, company_name, location, start_date, end_date, description, skills, employment_type, achievements FROM organiser_experience WHERE user_id = ?";
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

// Fetch skills from 'skills' table
$sql_skills = "SELECT skill_name,proficiency FROM organiser_skills WHERE user_id = ?";
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

// Fetch education details from 'user_education' table
$sql_education = "SELECT degree, institution, graduation_year FROM `organiser-education` WHERE user_id = ?";
if ($stmt_education = $conn->prepare($sql_education)) {
    $stmt_education->bind_param("i", $user_id);
    if (!$stmt_education->execute()) {
        handle_error('Error executing education query: ' . $stmt_education->error);
    }
    $result_education = $stmt_education->get_result();
    if ($result_education->num_rows > 0) {
        $education = $result_education->fetch_assoc();
    } else {
        $education = [
            'degree' => 'N/A',
            'institution' => 'N/A',
            'graduation_year' => 'N/A'
        ];
    }
    $stmt_education->close();
} else {
    handle_error('Error preparing education query: ' . $conn->error);
}

// Fetch body criteria from 'body_criteria' table
$sql_body_criteria = "SELECT height, weight FROM organiser_body_criteria WHERE user_id = ?";
if ($stmt_body_criteria = $conn->prepare($sql_body_criteria)) {
    $stmt_body_criteria->bind_param("i", $user_id);
    if (!$stmt_body_criteria->execute()) {
        handle_error('Error executing body criteria query: ' . $stmt_body_criteria->error);
    }
    $result_body_criteria = $stmt_body_criteria->get_result();
    if ($result_body_criteria->num_rows === 1) {
        $body_criteria = $result_body_criteria->fetch_assoc();
        $height = $body_criteria['height'];
        $weight = $body_criteria['weight'];
    } else {
        $height = 'N/A';
        $weight = 'N/A';
    }
    $stmt_body_criteria->close();
} else {
    handle_error('Error preparing body criteria query: ' . $conn->error);
}

// Fetch identification info from 'identification_info' table
$sql_identification = "SELECT aadhar, pan, address_line1, address_line2, city, state, zipcode FROM organiser_identification_info WHERE user_id = ?";
if ($stmt_identification = $conn->prepare($sql_identification)) {
    $stmt_identification->bind_param("i", $user_id);
    if (!$stmt_identification->execute()) {
        handle_error('Error executing identification info query: ' . $stmt_identification->error);
    }
    $result_identification = $stmt_identification->get_result();
    if ($result_identification->num_rows === 1) {
        $identification_info = $result_identification->fetch_assoc();
    } else {
        $identification_info = [
            'aadhar' => 'N/A',
            'pan' => 'N/A',
            'address_line1' => 'N/A',
            'address_line2' => 'N/A',
            'city' => 'N/A',
            'state' => 'N/A',
            'zipcode' => 'N/A'
        ];
    }
    $stmt_identification->close();
} else {
    handle_error('Error preparing identification info query: ' . $conn->error);
}

// Profile photo upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    $upload_dir = '../organiser_photos/';
    $upload_file = $upload_dir . basename($file['name']);
    $file_type = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($file_type, $allowed_types) && $file['size'] < 5000000) {
        if (move_uploaded_file($file['tmp_name'], $upload_file)) {
            // Update the database with new profile picture
            $sql_update = "UPDATE `wurkify_user` SET picture_url = ? WHERE id = ?";
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("si", $file['name'], $user_id);
                if (!$stmt_update->execute()) {
                    handle_error('Error updating profile picture: ' . $stmt_update->error);
                } else {
                    echo "<script>alert('Profile picture updated successfully');</script>";
                }
                $stmt_update->close();
            } else {
                handle_error('Error preparing update profile picture query: ' . $conn->error);
            }
        } else {
            handle_error('Error uploading file');
        }
    } else {
        handle_error('Invalid file type or size');
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
    <style>
        .popup-message {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px;
            border-radius: 5px;
            color: #fff;
            font-size: 1em;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.5s;
        }

        .popup-message.success {
            background-color: #4CAF50;
        }

        .popup-message.error {
            background-color: #f44336;
        }

        .popup-message.info {
            background-color: #2196F3;
        }

        .popup-message.show {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .profile-box {
                width: 100%;
                padding: 15px;
            }

            .profile-info {
                padding: 10px;
            }

            .social-media-links i {
                font-size: 2em;
                /* Reduce icon size for smaller screens */
            }

            form#upload-photo-form input[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="page-content">
        <div class="sidebar">
            <div class="sidebar-content">
                <div class="brand">
                    <i class="fa-solid fa-xmark xmark"></i>
                    <!-- <h3> <?php echo htmlspecialchars($user['username']); ?></h3> -->
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
                    <h1>Profile</h1>
                </div>

                <!-- <div class="profile-box" style="width: 100%; max-width: 400px; margin: 0 auto; padding: 20px;">
                    <div class="profile-info" style="text-align: center; padding: 20px; background-color: #f9f9f9; border-radius: 10px; border: 1px solid #ddd; box-sizing: border-box;">

                        <div style="position: relative; display: inline-block;">
                            <img src="<?php echo htmlspecialchars($picture_url); ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                            <a href="#" onclick="document.getElementById('upload-photo-form').style.display='block'; return false;" style="position: absolute; bottom: 0; right: 0; background: #fff; border-radius: 50%; padding: 5px; border: 1px solid #ddd; color: #333; text-decoration: none;">
                                <i class="fa-solid fa-pencil-alt fa-lg"></i>
                            </a>
                        </div>
                        <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($user['username']); ?></h3>

                        <div class="social-media-links" style="margin: 20px 0; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                            <a href="https://twitter.com/<?php echo htmlspecialchars($social_media['twitter_username']); ?>" target="_blank" rel="noopener noreferrer" style="color: #1DA1F2;">
                                <i class="fa-brands fa-twitter fa-2x"></i>
                            </a>
                            <a href="https://facebook.com/<?php echo htmlspecialchars($social_media['facebook_username']); ?>" target="_blank" rel="noopener noreferrer" style="color: #1877F2;">
                                <i class="fa-brands fa-facebook-f fa-2x"></i>
                            </a>
                            <a href="https://youtube.com/user/<?php echo htmlspecialchars($social_media['youtube_username']); ?>" target="_blank" rel="noopener noreferrer" style="color: #FF0000;">
                                <i class="fa-brands fa-youtube fa-2x"></i>
                            </a>
                            <a href="https://linkedin.com/in/<?php echo htmlspecialchars($social_media['linkedin_username']); ?>" target="_blank" rel="noopener noreferrer" style="color: #0A66C2;">
                                <i class="fa-brands fa-linkedin fa-2x"></i>
                            </a>
                        </div>

                        <form action="../Database/upload_image.php" method="post" enctype="multipart/form-data" id="upload-photo-form" style="display: none; margin-top: 20px; padding: 15px; border-radius: 10px; background-color: #fff; border: 1px solid #ddd;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px; width: 100%;">
                                
                                <input type="file" name="profile_picture" accept="image/*" style="padding: 5px; border-radius: 5px; border: 1px solid #ddd; width: 80%; max-width: 300px;">

                                
                                <input type="submit" value="Change Profile Picture" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 80%; max-width: 300px;">
                            </div>
                        </form>

                        <a href="../logout.php" style="color: #0075ff; text-decoration: none; margin-top: 20px; display: inline-block;">LogOut</a>
                    </div>
                </div> -->

                <!-- My Profile Name and Image Card  -->
                <div class="profile-img-and-info">
                    <div class="profile-img-info">
                        <div class="card-container">
                            <div class="image-container">
                                <img class="round" src="<?php echo htmlspecialchars($picture_url); ?>" alt="Profile Picture" />
                                <a href="#" onclick="document.getElementById('upload-photo-form').style.display='block'; return false;" class="edit-icon">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            </div>
                            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                            <div class="info-container">
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                                <span><?php echo htmlspecialchars($general_info['phone_number']); ?></span>
                            </div>
                            <div class="social-icons">
                                <a href="https://twitter.com/<?php echo htmlspecialchars($social_media['twitter_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://facebook.com/<?php echo htmlspecialchars($social_media['facebook_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                                <a href="https://youtube.com/user/<?php echo htmlspecialchars($social_media['youtube_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer">
                                    <i class="fa-brands fa-youtube"></i>
                                </a>
                                <a href="https://linkedin.com/in/<?php echo htmlspecialchars($social_media['linkedin_username']); ?>" target="_blank" class="icon" rel="noopener noreferrer">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                            <div class="choose-img-box">
                                <form action="../Database/upload_image.php" method="post" enctype="multipart/form-data" id="upload-photo-form" style="display: none;" class="upload-form">
                                    <div class="upload-form-container">
                                        <label for="file-upload" class="custom-file-upload">
                                            Choose File
                                        </label>
                                        <input id="file-upload" type="file" name="profile_picture" accept="image/*" required>
                                        <input type="submit" value="Change" class="submit-button">
                                    </div>
                                </form>
                            </div>
                            <!-- <a href="../logout.php" class="logout-button">Logout</a> -->
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
                    <!-- <div class="profile-basic-details-form">
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
                    </div> -->
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
                                <ul class="skills-list">
                                    <?php
                                    if (!empty($skills)) {
                                        foreach ($skills as $skill) {
                                            $skill_name = htmlspecialchars($skill['skill_name']);
                                            $proficiency = htmlspecialchars($skill['proficiency']);
                                            echo '<li class="skill-item">'
                                                . '<strong class="skill-name">' . $skill_name . '</strong>'
                                                . '<span class="skill-proficiency">Proficiency: ' . $proficiency . '</span>'
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

                <div class="profile-info-section2" style="max-width: 1200px; margin: 0 auto; padding: 20px; box-sizing: border-box;">
                    <!-- General Information Section -->
                    <!-- <div class="row" style="margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <h4 style="margin-bottom: 20px; color: #0075ff;">General Information</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Full Name:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($general_info['first_name']); ?> <?php echo htmlspecialchars($general_info['last_name']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Date Of Birth:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($general_info['dob']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Gender:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($general_info['gender']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Age:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($general_info['age']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Country:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($general_info['country']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">State:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($general_info['state']); ?></span>
                            </div>
                        </div>
                    </div> -->

                    <!-- Contact Information Section -->
                    <!-- <div class="row" style="margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <h4 style="margin-bottom: 20px; color: #0075ff;">Contact Information</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Email:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Phone:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($general_info['phone_number']); ?></span>
                            </div>
                        </div>
                    </div> -->

                    <!-- Identification Information Section -->
                    <!-- <div class="row" style="margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <h4 style="margin-bottom: 20px; color: #0075ff;">Identification Information</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Aadhar:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($identification_info['aadhar']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">PAN:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($identification_info['pan']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Address Line 1:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($identification_info['address_line1']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Address Line 2:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($identification_info['address_line2']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">City:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($identification_info['city']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">State:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($identification_info['state']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Zipcode:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($identification_info['zipcode']); ?></span>
                            </div>
                        </div>
                    </div> -->

                    <!-- Education Information Section -->
                    <!-- <div class="row" style="margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <h4 style="margin-bottom: 20px; color: #0075ff;">Education Information</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Degree:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($education['degree']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Institute:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($education['institution']); ?></span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Graduation Year:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($education['graduation_year']); ?></span>
                            </div>
                        </div>
                    </div> -->

                    <!-- Body Metrics Section -->
                    <!-- <div class="row" style="margin-bottom: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <h4 style="margin-bottom: 20px; color: #0075ff;">Body Metrics</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Height:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($height); ?> cm</span>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <h5 style="margin: 5px 0; color: #555;">Weight:</h5>
                                <span style="font-size: 1em; color: #333;"><?php echo htmlspecialchars($weight); ?> kg</span>
                            </div>
                        </div>
                    </div> -->
                </div>

                <div class="main-content-boxes profile-main-content-boxes" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
                    <!-- Skills Section -->
                    <!-- <div class="box" style="margin-bottom: 30px; background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <div class="box-section1" style="border-bottom: 2px solid #0075ff; padding: 20px;">
                            <div class="box-title">
                                <h2 style="margin: 0; color: #0075ff;">My Skills</h2>
                                <p style="margin: 0; font-size: 1.1em; color: #555;">Complete Skills List</p>
                            </div>
                        </div>
                        <div class="profile-skills" style="padding: 20px;">
                            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 20px;">
                                <?php
                                if (!empty($skills)) {
                                    foreach ($skills as $skill) {
                                        $skill_name = htmlspecialchars($skill['skill_name']);
                                        $proficiency = htmlspecialchars($skill['proficiency']);
                                        echo '<li style="flex: 1 1 calc(33% - 20px); box-sizing: border-box; border-radius: 8px; padding: 15px; background-color: #ffffff; color: #333333; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border: 1px solid #e0e0e0; transition: all 0.3s ease; font-family: Arial, sans-serif; text-align: center;">'
                                            . '<strong style="display: block; margin-bottom: 5px;">' . $skill_name . '</strong>'
                                            . '<span style="font-size: 0.9em; color: #777777;">Proficiency: ' . $proficiency . '</span>'
                                            . '</li>';
                                    }
                                } else {
                                    echo '<li style="flex: 1 1 calc(33% - 20px); box-sizing: border-box; border-radius: 8px; padding: 15px; background-color: #ffffff; color: #333333; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border: 1px solid #e0e0e0; transition: all 0.3s ease; font-family: Arial, sans-serif; text-align: center;">No skills found</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div> -->

                    <!-- Experiences Section -->
                    <!-- <div class="box latest-activities" style="background-color: #f9f9f9; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <div class="box-section1" style="border-bottom: 2px solid #0075ff; padding: 20px;">
                            <div class="box-title">
                                <h2 style="margin: 0; color: #0075ff;">Latest Experiences</h2>
                                <p style="margin: 0; font-size: 1.1em; color: #555;">Recent Experiences Added by the User</p>
                            </div>
                        </div>
                        <div class="profile-latest-activities" style="padding: 20px;">
                            <?php foreach ($experiences as $experience): ?>
                                <div class="profile-latest-activities-row" style="display: flex; align-items: center; margin-bottom: 20px; padding: 15px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border: 1px solid #e0e0e0;">
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
                    </div> -->
                </div>

            </div>
        </main>

    </div>
    <script src="../js/main.js"></script>
    <script>
        function showPopupMessage(message, type) {
            let popup = document.createElement('div');
            popup.className = 'popup-message ' + type;
            popup.innerText = message;
            document.body.appendChild(popup);

            setTimeout(function() {
                document.body.removeChild(popup);
            }, 3000);
        }

        // Example usage of showPopupMessage
        <?php if (isset($popup_message)) { ?>
            showPopupMessage("<?php echo htmlspecialchars($popup_message); ?>", "<?php echo htmlspecialchars($popup_message_type); ?>");
        <?php } ?>
    </script>
</body>

</html>