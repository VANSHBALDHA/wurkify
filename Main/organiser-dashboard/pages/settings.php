<?php
session_start();
include('../Database/config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../seeker/seekerlogin.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables with default empty values
$account_number = '';
$ifsc_code = '';
$bank_name = '';
$branch_name = '';
$account_holder_name = '';
$upi_id = '';
$upi_number = '';
$picture_url = '../default.jpeg'; // Default picture
//Fetch user details from 'wurkify_user' table
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

// Fetch user bank details
$sql_bank = "SELECT account_number, ifsc_code, bank_name, branch_name, account_holder_name, upi_id, upi_number 
             FROM `organiser_accounts` 
             WHERE user_id = ?";
if ($stmt_bank = $conn->prepare($sql_bank)) {
    $stmt_bank->bind_param("i", $user_id);
    if ($stmt_bank->execute()) {
        $result_bank = $stmt_bank->get_result();

        if ($result_bank->num_rows === 1) {
            $bank_details = $result_bank->fetch_assoc();
            // Assign fetched data to variables
            $account_number = $bank_details['account_number'];
            $ifsc_code = $bank_details['ifsc_code'];
            $bank_name = $bank_details['bank_name'];
            $branch_name = $bank_details['branch_name'];
            $account_holder_name = $bank_details['account_holder_name'];
            $upi_id = $bank_details['upi_id'];
            $upi_number = $bank_details['upi_number'];
        }
    } else {
        echo 'Error executing bank query: ' . $stmt_bank->error;
        exit();
    }
    $stmt_bank->close();
} else {
    echo 'Error preparing bank query: ' . $conn->error;
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
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <!-- <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&display=swap" rel="stylesheet" /> -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <title>Settings</title>
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
                    <img src="<?php echo htmlspecialchars($picture_url); ?>" alt="No Image" style="border-radius: 50%;" />
                </div>
            </div>
            <div class="main-content">
                <div class="title">
                    <h1>Settings</h1>
                </div>
                <!-- <div class="main-content-boxes"> -->
                    <!-- <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>Experience Section</h2>
                                <p>Fill in your work experience details</p>
                            </div>
                        </div>
                        <div class="general-info-section2">
                            <form action="../Database/experience.php" method="post">
                                <label for="job_title" style="display: block; margin-bottom: 5px;">Job Title</label>
                                <input type="text" name="job_title" id="job_title" placeholder="Enter your job title"
                                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                                    required />

                                <label for="company_name" style="display: block; margin-bottom: 5px;">Company Name</label>
                                <input type="text" name="company_name" id="company_name" placeholder="Enter the company name"
                                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                                    required />

                                <label for="location" style="display: block; margin-bottom: 5px;">Location</label>
                                <input type="text" name="location" id="location" placeholder="Enter the job location"
                                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                                    required />

                                <label for="start_date" style="display: block; margin-bottom: 5px;">Start Date</label>
                                <input type="date" name="start_date" id="start_date"
                                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                                    required />

                                <label for="end_date" style="display: block; margin-bottom: 5px;">End Date</label>
                                <input type="date" name="end_date" id="end_date"
                                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" />

                                <label for="description" style="display: block; margin-bottom: 5px;">Job Description</label>
                                <textarea name="description" id="description" placeholder="Describe your job responsibilities" rows="4"
                                    style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                                    required></textarea>

                                <label for="skills" style="display: block; margin-bottom: 5px;">Skills Utilized</label>
                                <input type="text" name="skills" id="skills" placeholder="List the skills you used"
                                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                                    required />

                                <label for="employment_type" style="display: block; margin-bottom: 5px;">Employment Type</label>
                                <select name="employment_type" id="employment_type"
                                    style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;"
                                    required>
                                    <option value="" disabled selected>Select employment type</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Internship">Internship</option>
                                    <option value="Freelance">Freelance</option>
                                </select>


                                <input type="submit" value="Save Experience"
                                    style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;" />
                            </form>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>General Info</h2>
                                <p>General Information About Your Account</p>
                            </div>
                        </div>
                        <div class="general-info-section2">
                            <form action="../Database/generalinfo.php" method="post">
                                <label for="first-name" style="display: block; margin-bottom: 5px;">First Name</label>
                                <input type="text" name="first_name" placeholder="First Name" id="first-name" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="last-name" style="display: block; margin-bottom: 5px;">Last Name</label>
                                <input type="text" name="last_name" placeholder="Last Name" id="last-name" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="email" style="display: block; margin-bottom: 5px;">Email</label>
                                <div class="email" style="margin-bottom: 10px;">
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>"
                                        style="width:100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;"
                                        required />

                                </div>

                                <label for="phone-number" style="display: block; margin-bottom: 5px;">Phone Number</label>
                                <input type="tel" name="phone_number" placeholder="Phone Number" id="phone-number" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" pattern="\d+" title="Please enter a valid phone number" required />

                                <label for="dob" style="display: block; margin-bottom: 5px;">Date of Birth</label>
                                <input type="date" name="dob" id="dob" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="gender" style="display: block; margin-bottom: 5px;">Gender</label>
                                <select name="gender" id="gender" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>

                                <label for="age" style="display: block; margin-bottom: 5px;">Age</label>
                                <input type="number" name="age" placeholder="Age" id="age" min="0" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="country" style="display: block; margin-bottom: 5px;">Country</label>
                                <input type="text" name="country" placeholder="Country" id="country" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="state" style="display: block; margin-bottom: 5px;">State</label>
                                <input type="text" name="state" placeholder="State" id="state" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <input type="submit" value="Save Changes" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;" />
                            </form>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>Identification Info</h2>
                                <p>Your Identification and Address Details</p>
                            </div>
                        </div>

                        <div class="general-info-section2">
                            <form action="../Database/submit_identification.php" method="post">
                                <div class="person-info-details">
                                    <div class="person-info">
                                        <label for="aadhar" style="font-weight: normal;">Aadhar Card Number</label>
                                        <input type="text" name="aadhar" id="aadhar" placeholder="XXXX-XXXX-XXXX" pattern="\d{4}-\d{4}-\d{4}" title="Please enter a valid Aadhar number in XXXX-XXXX-XXXX format" style="background: white; width:100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                    </div>
                                    <span style="color: green;">Format: 1234-5678-9000</span>
                                </div>
                                <br>

                                <div class="person-info-details">
                                    <div class="person-info">
                                        <label for="pan" style="font-weight: normal;">PAN Card Number</label>
                                        <input type="text" name="pan" id="pan" placeholder="XXXXXXX123" title="Please enter a valid PAN number (e.g., ABCDE1234F)" style="background: white; width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                    </div>
                                    <span style="color: green;">Format: ABCP1234Z</span>
                                </div>
                                <br>

                                <div class="person-info-details">
                                    <div class="person-info">
                                        <label for="address" style="font-weight: normal;">Home Address</label>
                                        <input type="text" name="address_line1" placeholder="Street Address" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                        <input type="text" name="address_line2" placeholder="Apt/Suite (optional)" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">
                                        <input type="text" name="city" placeholder="City" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                        <input type="text" name="state" placeholder="State" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                        <input type="text" name="zipcode" placeholder="Zip Code" pattern="\d{5,6}" title="Please enter a valid Zip Code" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                    </div>
                                    <span style="color: green;">Not Submitted</span>
                                </div>


                                <input type="submit" value="Submit Identification Info" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                            </form>
                        </div>
                    </div>

                    <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>Social Info</h2>
                                <p>Social Media Information</p>
                            </div>
                        </div>
                        <div class="social-info-section2">
                            <form id="socialForm" action="../Database/update_social_info.php" method="post" onsubmit="return validateForm()">
                                <div class="social-info-icon">
                                    <i class="fa-brands fa-twitter"></i>
                                    <input type="text" name="twitter_username" placeholder="Twitter Username" />
                                </div>
                                <div class="social-info-icon">
                                    <i class="fa-brands fa-facebook-f"></i>
                                    <input type="text" name="facebook_username" placeholder="Facebook Username" />
                                </div>
                                <div class="social-info-icon">
                                    <i class="fa-brands fa-linkedin"></i>
                                    <input type="text" name="linkedin_username" placeholder="Linkedin Username " />
                                </div>
                                <div class="social-info-icon">
                                    <i class="fa-brands fa-instagram"></i>
                                    <input type="text" name="youtube_username" placeholder="Instagram Username" />
                                </div>
                                <br>
                                <input type="submit" value="Update Social Info" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-size: 16px; cursor: pointer;" />

                            </form>
                        </div>
                    </div> -->

                    <!-- <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>Education</h2>
                                <p>Provide Your Educational Background</p>
                            </div>
                        </div>
                        <div class="general-info-section2">
                            <form action="../Database/education.php" method="post">
                                <label for="degree" style="display: block; margin-bottom: 5px;">Degree</label>
                                <input type="text" name="degree" placeholder="Degree" id="degree" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="institution" style="display: block; margin-bottom: 5px;">Institution</label>
                                <input type="text" name="institution" placeholder="Institution" id="institution" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="graduation-year" style="display: block; margin-bottom: 5px;">Graduation Year</label>
                                <input type="number" name="graduation_year" placeholder="Graduation Year" id="graduation-year" min="1900" max="2030" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <input type="submit" value="Save Changes" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;" />
                            </form>
                        </div>
                    </div> -->

                    <!-- <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>Skills</h2>
                                <p>List Your Skills and Proficiency Levels</p>
                            </div>
                        </div>
                        <div class="general-info-section2">
                            <form action="../Database/skills.php" method="post">
                                <label for="skill-name" style="display: block; margin-bottom: 5px;">Skill Name</label>
                                <input type="text" name="skill_name" placeholder="Skill Name" id="skill-name" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="proficiency" style="display: block; margin-bottom: 5px;">Proficiency</label>
                                <select name="proficiency" id="proficiency" style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required>
                                    <option value="" disabled selected>Select Proficiency Level</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>

                                <input type="submit" value="Add Skill" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;" />
                            </form>
                        </div>
                    </div> -->

                    <!-- <div class="box" style="padding: 20px; border-radius: 8px; max-width: 100%; box-sizing: border-box;">
                        <div class="box-section1" style="margin-bottom: 20px;">
                            <div class="box-title">
                                <h2 style="margin: 0; color: #333;">Bank Account Management</h2>
                                <p style="margin: 0; color: #666;">Submit Correct Details</p>
                            </div>
                        </div>
                        <div class="general-info-section2">
                            <?php if (empty($account_number)): ?>
                                <form action="../Database/manage_bank_account.php" method="POST">
                                    <label for="account_number" style="display: block; margin-bottom: 5px;">Bank Account Number</label>
                                    <input type="text" name="account_number" id="account_number" placeholder="Bank Account Number" required style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" onkeypress="restrictToNumbers(event)">

                                    <label for="ifsc_code" style="display: block; margin-bottom: 5px;">IFSC Code</label>
                                    <input type="text" name="ifsc_code" id="ifsc_code" placeholder="IFSC Code" required style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="bank_name" style="display: block; margin-bottom: 5px;">Bank Name</label>
                                    <input type="text" name="bank_name" id="bank_name" placeholder="Bank Name" required style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="branch_name" style="display: block; margin-bottom: 5px;">Branch Name</label>
                                    <input type="text" name="branch_name" id="branch_name" placeholder="Branch Name" required style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="account_holder_name" style="display: block; margin-bottom: 5px;">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" id="account_holder_name" placeholder="Account Holder Name" required style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="upi_id" style="display: block; margin-bottom: 5px;">UPI ID</label>
                                    <input type="text" name="upi_id" id="upi_id" placeholder="UPI ID" required style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="upi_number" style="display: block; margin-bottom: 5px;">UPI Number</label>
                                    <input type="text" name="upi_number" id="upi_number" placeholder="UPI Number" required style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <div style="display: flex; justify-content: flex-start; margin-top: 20px;">
                                        <button type="submit" style="padding: 10px 20px; background-color: #0075ff; color: white; border: none; border-radius: 5px; cursor: pointer;">Submit</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <form method="POST">
                                    <label for="account_number" style="display: block; margin-bottom: 5px;">Bank Account Number</label>
                                    <input type="text" name="account_number" id="account_number" placeholder="Bank Account Number" value="<?php echo htmlspecialchars($account_number); ?>" readonly style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="ifsc_code" style="display: block; margin-bottom: 5px;">IFSC Code</label>
                                    <input type="text" name="ifsc_code" id="ifsc_code" placeholder="IFSC Code" value="<?php echo htmlspecialchars($ifsc_code); ?>" readonly style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="bank_name" style="display: block; margin-bottom: 5px;">Bank Name</label>
                                    <input type="text" name="bank_name" id="bank_name" placeholder="Bank Name" value="<?php echo htmlspecialchars($bank_name); ?>" readonly style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="branch_name" style="display: block; margin-bottom: 5px;">Branch Name</label>
                                    <input type="text" name="branch_name" id="branch_name" placeholder="Branch Name" value="<?php echo htmlspecialchars($branch_name); ?>" readonly style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="account_holder_name" style="display: block; margin-bottom: 5px;">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" id="account_holder_name" placeholder="Account Holder Name" value="<?php echo htmlspecialchars($account_holder_name); ?>" readonly style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="upi_id" style="display: block; margin-bottom: 5px;">UPI ID</label>
                                    <input type="text" name="upi_id" id="upi_id" placeholder="UPI ID" value="<?php echo htmlspecialchars($upi_id); ?>" readonly style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <label for="upi_number" style="display: block; margin-bottom: 5px;">UPI Number</label>
                                    <input type="text" name="upi_number" id="upi_number" placeholder="UPI Number" value="<?php echo htmlspecialchars($upi_number); ?>" readonly style="background: white;width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;">

                                    <div style="display: flex; justify-content: flex-start; margin-top: 20px;">
                                        <button type="button" style="padding: 10px 20px; background-color: #0075ff; color: white; border: none; border-radius: 5px; cursor: pointer;" onclick="window.location.href='settings.php';">Change</button>
                                    </div>
                                    <p style="margin: 0; color: red;">Contact Wurkify to change account details</p>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div> -->

                    <!-- <div class="box">
                        <div class="box-section1">
                            <div class="box-title">
                                <h2>Body Criteria</h2>
                                <p>Provide Your Height and Weight</p>
                            </div>
                        </div>
                        <div class="general-info-section2">
                            <form action="../Database/process_body_criteria.php" method="post">
                                <label for="height" style="display: block; margin-bottom: 5px;">Height (cm)</label>
                                <input type="number" name="height" id="height" placeholder="Height in cm" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <label for="weight" style="display: block; margin-bottom: 5px;">Weight (kg)</label>
                                <input type="number" name="weight" id="weight" placeholder="Weight in kg" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; margin-bottom: 10px;" required />

                                <input type="submit" value="Submit" style="background-color: #0075ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;" />
                            </form>
                        </div>
                    </div> -->
                <!-- </div> -->

                <!-- My Work Start Form Start-->
                <div class="settings-form-start">
                    <!-- Experience Section -->
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form">
                            <h3>Fill in your work experience details</h3>
                            <hr class="feedback-form-head-row">
                            <form action="../Database/experience.php" method="post">
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="job_title">Job Title</label>
                                        <input type="text" id="job_title" name="job_title" placeholder="Enter your job title" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="company_name">Company Name</label>
                                        <input type="text" id="company_name" name="company_name" placeholder="Enter the company name" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="location">Location</label>
                                        <input type="text" id="location" name="location" placeholder="Enter the job location" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="skills">Skills Utilized</label>
                                        <input type="text" id="skills" name="skills" placeholder="List the skilss you used" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="start_date">Start Date</label>
                                        <input type="date" id="start_date" name="start_date" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_date">End Date</label>
                                        <input type="date" id="end_date" name="end_date" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="employment_type">Employment Type</label>
                                        <div class="custom-select event-create-dress-code">
                                            <select name="employment_type" id="employment_type" required>
                                                <option value="" disabled selected>Select employment type</option>
                                                <option value="Full-time">Full-time</option>
                                                <option value="Part-time">Part-time</option>
                                                <option value="Contract">Contract</option>
                                                <option value="Internship">Internship</option>
                                                <option value="Freelance">Freelance</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="description">Job Description</label>
                                    <textarea id="description" name="description" class="describe_text" placeholder="Describe your job responsibilities" required></textarea>
                                </div>
                                <div class="submit-new-event-btn">
                                    <button type="submit">Save Experience</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- General Info -->
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form" style="height:100%">
                            <h3>General Information About Your Account</h3>
                            <hr class="feedback-form-head-row">
                            <form action="../Database/generalinfo.php" method="post">
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="first-name">First Name</label>
                                        <input type="text" id="first-name" name="first-name" placeholder="Enter first name" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="last-name">Last Name</label>
                                        <input type="text" id="last-name" name="last-name" placeholder="Enter last name" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="form-group general-info-email">
                                    <label for="email">Email</label>
                                    <input type="email" name="email"
                                        id="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" class="setting-create-profile" disabled>
                                </div>
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="phone-number">Phone Number</label>
                                        <input type="tel" id="phone-number" name="phone_number" placeholder="Enter phone number" class="setting-create-profile" pattern="\d+" title="Please enter a valid phone number" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="dob">Date of Birth</label>
                                        <input type="date" id="dob" name="dob" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <div class="custom-select event-create-dress-code">
                                            <select name="gender" id="gender" required>
                                                <option value="" disabled selected>Select Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="age">Age</label>
                                        <input type="number" id="age" name="age" min="0" placeholder="Enter age" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" id="country" name="country" placeholder="Enter country" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" id="state" name="state" placeholder="Enter state" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="submit-new-event-btn">
                                    <button type="submit">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Identification Info -->
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form height-weight-form">
                            <h3>Your Identification and Address Details</h3>
                            <hr class="feedback-form-head-row">
                            <form action="../Database/submit_identification.php" method="post">
                                <div class="work-experience-details-input">
                                    <div class="form-group">
                                        <label for="aadhar">Aadhar Card Number</label>
                                        <input type="text" id="aadhar" name="aadhar" placeholder="xxxx-xxxx-xxxx" pattern="\d{4}-\d{4}-\d{4}" title="Please enter a valid Aadhar number in XXXX-XXXX-XXXX format" class="setting-create-profile" required>
                                        <h5 style="color:#cecece">Format: 1234-5678-9000</h5>
                                    </div>
                                    <div class="form-group">
                                        <label for="pan">PAN Card Number</label>
                                        <input type="text" id="pan" name="pan" placeholder="xxxxx1234x" pattern="^[A-Z]{5}[0-9]{4}[A-Z]$" oninvalid="this.setCustomValidity('Please enter a valid PAN number (e.g., ABCDE1234F)')" oninput="this.setCustomValidity('')" title="Please enter a valid PAN number (e.g., ABCDE1234F)" class="setting-create-profile" required>
                                        <h5 style="color:#cecece">Format: ABCPD1234Z</h5>
                                    </div>
                                </div>
                                <div class="form-group street-address-field">
                                    <label for="address">Home Address</label>
                                    <input type="text" id="address_line1" name="address_line1" placeholder="Enter street address" class="setting-create-profile" required>
                                    <input type="text" id="address_line2" name="address_line2" placeholder="Apt/Suite (optional)" class="setting-create-profile">
                                    <div class="Identification-city-state">
                                        <input type=" text" id="city" name="city" placeholder="Enter city" class="setting-create-profile" required>
                                        <input type=" text" id="state" name="state" placeholder="Enter state" class="setting-create-profile" required>
                                        <input type=" text" id="zipcode" name="zipcode" placeholder="Enter zip code" pattern="\d{5,6}" title="Please enter a valid Zip Code" class="setting-create-profile" required>
                                    </div>
                                </div>
                                <div class="submit-new-event-btn">
                                    <button type="submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Social Media Information -->
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form height-weight-form">
                            <h3>Social Media Information</h3>
                            <hr class="feedback-form-head-row">
                            <form id="socialForm" action="../Database/update_social_info.php" method="post" onsubmit="return validateForm()">
                                <div class="social-media-inputs">
                                    <div class="form-group">
                                        <label for="twitter_username">Twitter username</label>
                                        <div class="social-info-icon">
                                            <i class="fa-brands fa-twitter"></i>
                                            <input type="text" name="twitter_username" id="twitter_username" placeholder="Twitter Username" class="setting-create-profile social-icon-input-field" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="facebook_username">Facebook username</label>
                                        <div class="social-info-icon">
                                            <i class="fa-brands fa-facebook-f"></i>
                                            <input type="text" name="facebook_username" id="facebook_username" placeholder="Facebook Username" class="setting-create-profile social-icon-input-field" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="linkedin_username">Linkedin username</label>
                                        <div class="social-info-icon">
                                            <i class="fa-brands fa-linkedin"></i>
                                            <input type="text" name="linkedin_username" id="linkedin_username" placeholder="Linkedin Username" class="setting-create-profile social-icon-input-field" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="youtube_username">Instagram username</label>
                                        <div class="social-info-icon">
                                            <i class="fa-brands fa-instagram"></i>
                                            <input type="text" name="youtube_username" id="youtube_username" placeholder="Instagram Username" class="setting-create-profile social-icon-input-field" required>
                                        </div>
                                    </div>
                                    <div class="submit-new-event-btn">
                                        <button type="submit">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="education-skills-body-form">
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form height-weight-form">
                            <h3>Provide Your Educational Background</h3>
                            <hr class="feedback-form-head-row">
                            <form action="../Database/education.php" method="post">
                                <div class="education-details-form">
                                    <div class="form-group">
                                        <label for="degree">Degree</label>
                                        <input type="text" id="degree" name="degree" placeholder="Enter your degree" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="institution">Institution</label>
                                        <input type="text" id="institution" name="institution" placeholder="Enter institution" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="graduation-year">Graduation Year</label>
                                        <input type="text" id="graduation-year" name="graduation_year" placeholder="Enter graduation year" min="1900" max="2030" class="setting-create-profile" required>
                                    </div>
                                    <div class="submit-new-event-btn">
                                        <button type="submit">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form height-weight-form">
                            <h3>List Your Skills and Proficiency Levels</h3>
                            <hr class="feedback-form-head-row">
                            <form action="../Database/skills.php" method="post">
                                <div class="education-details-form">
                                    <div class="form-group">
                                        <label for="skill-name">Skill Name</label>
                                        <input type="text" id="skill-name" name="skill-name" placeholder="Enter your skill" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="proficiency">Proficiency</label>
                                        <div class="custom-select event-create-dress-code">
                                            <select name="proficiency" id="proficiency" required>
                                                <option value="" disabled selected>Select Proficiency Level</option>
                                                <option value="beginner">Beginner</option>
                                                <option value="intermediate">Intermediate</option>
                                                <option value="advanced">Advanced</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="submit-new-event-btn">
                                        <button type="submit">Add Skill</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form height-weight-form">
                            <h3>Provide Your Height and Weight</h3>
                            <hr class="feedback-form-head-row">
                            <form action="../Database/process_body_criteria.php" method="post">
                                <div class="education-details-form">
                                    <div class="form-group">
                                        <label for="height">Height (cm)
                                        </label>
                                        <input type="number" id="height" name="height" placeholder="Enter your degree" class="setting-create-profile" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="weight">Weight (kg)
                                        </label>
                                        <input type="number" id="weight" name="weight" placeholder="Enter institution" class="setting-create-profile" required>
                                    </div>
                                    <div class="submit-new-event-btn">
                                        <button type="submit">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="settings-form-start bank-account-details-form">
                    <div class="general-feedback-form-start work-exp-detaiils-form">
                        <div class="feedback-form">
                            <h3>Fill bank account correct details</h3>
                            <hr class="feedback-form-head-row">
                            <?php if (empty($account_number)): ?>
                                <form action="../Database/manage_bank_account.php" method="post">
                                    <div class="education-details-form">
                                        <div class="form-group">
                                            <label for="account_number">Bank Account Number</label>
                                            <input type="text" id="account_number" name="account_number" placeholder="Enter bank account number" class="setting-create-profile" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ifsc_code">IFSC Code</label>
                                            <input type="text" id="ifsc_code" name="ifsc_code" placeholder="Enter your bank IFSC code" class="setting-create-profile" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="bank_name">Bank Name</label>
                                            <input type="text" id="bank_name" name="bank_name" placeholder="Enter bank name" class="setting-create-profile" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="branch_name">Branch Name</label>
                                            <input type="text" id="branch_name" name="branch_name" placeholder="Enter branch name" class="setting-create-profile" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="account_holder_name">Account Holder Name</label>
                                            <input type="text" id="account_holder_name" name="account_holder_name" placeholder="Enter account holder name" class="setting-create-profile" required>
                                        </div>
                                        <div class="work-experience-details-input">
                                            <div class="form-group">
                                                <label for="upi_id">UPI ID</label>
                                                <input type="text" id="upi_id" name="upi_id" placeholder="Enter valid UPI ID" class="setting-create-profile" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="upi_number">UPI Number</label>
                                                <input type="text" id="upi_number" name="upi_number" placeholder="Enter valid UPI number" class="setting-create-profile" required>
                                            </div>
                                        </div>
                                        <div class="submit-new-event-btn">
                                            <button type="submit">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?><form method="post">
                                    <div class="education-details-form">
                                        <div class="form-group">
                                            <label for="account_number">Bank Account Number</label>
                                            <input type="text" id="account_number" name="account_number" value="<?php echo htmlspecialchars($account_number); ?>" readonly class="setting-create-profile">
                                        </div>
                                        <div class="form-group">
                                            <label for="ifsc_code">IFSC Code</label>
                                            <input type="text" id="ifsc_code" name="ifsc_code" value="<?php echo htmlspecialchars($ifsc_code); ?>" readonly class="setting-create-profile">
                                        </div>
                                        <div class="form-group">
                                            <label for="bank_name">Bank Name</label>
                                            <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($bank_name); ?>" readonly class="setting-create-profile">
                                        </div>
                                        <div class="form-group">
                                            <label for="branch_name">Branch Name</label>
                                            <input type="text" id="branch_name" name="branch_name" value="<?php echo htmlspecialchars($branch_name); ?>" readonly class="setting-create-profile">
                                        </div>
                                        <div class="form-group">
                                            <label for="account_holder_name">Account Holder Name</label>
                                            <input type="text" id="account_holder_name" name="account_holder_name" value="<?php echo htmlspecialchars($account_holder_name); ?>" readonly class="setting-create-profile">
                                        </div>
                                        <div class="work-experience-details-input">
                                            <div class="form-group">
                                                <label for="upi_id">UPI ID</label>
                                                <input type="text" id="upi_id" name="upi_id" value="<?php echo htmlspecialchars($upi_id); ?>" readonly class="setting-create-profile">
                                            </div>
                                            <div class="form-group">
                                                <label for="upi_number">UPI Number</label>
                                                <input type="text" id="upi_number" name="upi_number" value="<?php echo htmlspecialchars($upi_number); ?>" readonly class="setting-create-profile">
                                            </div>
                                        </div>
                                        <p style="margin: 0; color: red;">Contact Wurkify to change account details</p>
                                    </div>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- My Work Start Form End-->
            </div>
    </div>

    </main>
    </div>
    <script src="../js/main.js"></script>
    <script>
        function restrictToNumbers(event) {
            if (event.key < '0' || event.key > '9') {
                event.preventDefault();
            }
        }
    </script>
    <script>
        function validateExperienceForm() {
            var startDate = document.getElementById('start_date').value;
            var endDate = document.getElementById('end_date').value;
            var currentlyWorking = document.getElementById('currently_working').checked;

            if (!currentlyWorking && endDate && new Date(endDate) < new Date(startDate)) {
                alert("End date cannot be earlier than the start date.");
                return false;
            }
            return true;
        }

        function toggleEndDate() {
            var currentlyWorking = document.getElementById('currently_working').checked;
            document.getElementById('end_date').disabled = currentlyWorking;
            if (currentlyWorking) {
                document.getElementById('end_date').value = '';
            }
        }
    </script>
</body>

</html>