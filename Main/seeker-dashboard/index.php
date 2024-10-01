<!-- <link rel="stylesheet" href="../Database/config.php"> -->
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
        $picture_url = './uploads/' . $picture['file_path'];
      } else {
        // Set default profile picture if no picture is found
        $picture_url = './default.jpeg';
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

$labelsDount = ['Red', 'Blue', 'Yellow'];
$dataDount = [300, 50, 100];
$backgroundColordonut = ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 205, 86)'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="css/all.min.css" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;500&amp;display=swap" rel="stylesheet" /> -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <title>Wurkify | dashboard</title>
  <style>
    /* Container for the Coming Soon section */
    .coming-soon-container {
      display: flex;
      justify-content: center;
      /* Horizontally center */
      align-items: center;
      /* Vertically center */
      height: 100vh;
      /* Full viewport height */
      margin-top: -100px;
    }

    /* Coming Soon Section Styles */
    .coming-soon {
      background: transparent;
      /* Darker background for better contrast */
      color: black;
      /* White text color */
      padding: 30px;
      border-radius: 15px;
      /* Rounded corners */

      max-width: 600px;
      text-align: center;
      /* Center text */
      font-family: 'Open Sans', sans-serif;
      /* Consistent font */
      margin: 0;
      /* Remove margin to use flex container margin */
    }

    .coming-soon h2 {
      font-size: 2.5rem;
      /* Larger font size */
      margin: 0 0 15px 0;
    }

    .coming-soon p {
      font-size: 1.5rem;
      /* Slightly larger font size */
      margin: 0;
    }

    .main-content .title {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .filter_part .filter_btn,
    .filter_part .filter_select {
      padding: 15px 20px;
      background-color: #1d1d1e;
      color: #fff;
      font-size: 16px;
      border: none;
      border-radius: 10px
    }

    .filter_part .filter_btn i {
      color: #a4a4aa;
      margin-right: 10px;
    }

    .filter_part .filter_select {
      margin-left: 15px;
    }
  </style>
</head>

<body>
  <div class="loader">
    <h1>Loading<span>....</span></h1>
  </div>
  <div class="page-content index-page">
    <div class="sidebar">
      <div class="brand">
        <i class="fa-solid fa-xmark xmark"></i>
        <h3 class="brand-name">Wurkify</h3>
      </div>
      <ul>
        <li><a href="index.php" class="sidebar-link"><i
              class="fa-solid fa-house fa-fw"></i><span>Dashboard</span></a></li>
        <li><a href="./pages/Profile.php" class="sidebar-link"><i
              class="fa-solid fa-user fa-fw"></i><span>Profile</span></a></li>
        <li><a href="./pages/events.php" class="sidebar-link"><i
              class="fa-solid fa-calendar-day fa-fw"></i><span>Events</span></a></li>
        <li><a href="./pages/eventstatus.php" class="sidebar-link"><i
              class="fa-solid fa-calendar-check fa-fw"></i><span>Event Status</span></a></li>
        <li><a href="./pages/paymentStatus.php" class="sidebar-link"><i
              class="fa-solid fa-credit-card fa-fw"></i><span>Applicants</span></a></li>
        <li><a href="./pages/pricing.php" class="sidebar-link"><i
              class="fa-solid fa-tag fa-fw"></i><span>Pricing</span></a></li>
        <li><a href="./pages/feedback.php" class="sidebar-link"><i
              class="fa-solid fa-comment-dots fa-fw"></i><span>Feedback</span></a></li>
        <li><a href="./pages/settings.php" class="sidebar-link"><i
              class="fa-solid fa-cog fa-fw"></i><span>Settings</span></a></li>
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
          <br>
          <img src="<?php echo htmlspecialchars($picture_url); ?>" alt="No Image"
            style="border-radius: 50%;" />
        </div>
      </div>
      <div class="main-content">
        <div class="title">
          <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
          <div class="filter_part">
            <button class="filter_btn"><i class="fa-solid fa-arrow-up-wide-short me-2"></i>Filter
              by</button>
            <select name="" id="" class="filter_select">
              <option value="">Exports</option>
            </select>
          </div>
        </div>
        <!-- Coming Soon Section -->
        <!-- <div class="coming-soon-container">
          <div class="coming-soon" id="coming-soon">
            <h2>Coming Soon</h2>
            <p>We're working hard to bring you new features. Stay tuned!</p>
          </div>
        </div> -->

        <div class="dash_event_detail">
          <div class="dash_event_detail_box left_box">
            <div class="dash_event_inr_detail_box">
              <div class="inr_detail_title">
                <div class="box_name">
                  <span><i class="fa-solid fa-calendar-days"></i></span>
                  <label>Total Event</label>
                </div>
                <div class="detail_option">
                  <i class="fa-solid fa-ellipsis"></i>
                </div>
              </div>
              <div class="box_info">
                <h3>$120,784.02</h3>
                <p>
                  <span><i class="fa-solid fa-arrow-trend-up"></i>12.3%</span>
                  +$1453.89 Today
                </p>
              </div>
              <a href="" class="view_report_btn">View Reports <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="dash_event_inr_detail_box">
              <div class="inr_detail_title">
                <div class="box_name">
                  <span><i class="fa-solid fa-calendar-check"></i></span>
                  <label>Event Completed</label>
                </div>
                <div class="detail_option">
                  <i class="fa-solid fa-ellipsis"></i>
                </div>
              </div>
              <div class="box_info">
                <h3>28,834</h3>
                <p>
                  <span><i class="fa-solid fa-arrow-trend-up"></i>20.1%</span>
                  +2676 Today
                </p>
              </div>
              <a href="" class="view_report_btn">View Reports <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="dash_event_inr_detail_box">
              <div class="inr_detail_title">
                <div class="box_name">
                  <span><i class="fa-solid fa-sack-dollar"></i></span>
                  <label>Earning</label>
                </div>
                <div class="detail_option">
                  <i class="fa-solid fa-ellipsis"></i>
                </div>
              </div>
              <div class="box_info">
                <h3>18,896</h3>
                <p>
                  <span class="down"><i class="fa-solid fa-arrow-trend-down"></i>5.6%</span>
                  -876 Today
                </p>
              </div>
              <a href="" class="view_report_btn">View Reports <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="dash_event_inr_detail_box">
              <div class="inr_detail_title">
                <div class="box_name">
                  <span><i class="fa-solid fa-ticket"></i></span>
                  <label>Refunded</label>
                </div>
                <div class="detail_option">
                  <i class="fa-solid fa-ellipsis"></i>
                </div>
              </div>
              <div class="box_info">
                <h3>2876</h3>
                <p>
                  <span><i class="fa-solid fa-arrow-trend-up"></i>13%</span>
                  +34 Today
                </p>
              </div>
              <a href="" class="view_report_btn">View Reports <i class="fa-solid fa-arrow-right"></i></a>
            </div>
          </div>
          <div class="dash_event_detail_box graph">
            <div id="chart" class="apex-chart-sizing"></div>
          </div>
        </div>
        <div class="event_activity_traffic">
          <div class="event_activity">
            <div class="activity_title">
              <h3>Recent Activity</h3>
              <select name="" id="" class="activity_select">
                <option value="">Last 24h</option>
              </select>
            </div>
            <table class="activity_table">
              <thead>
                <tr>
                  <th>Customer</th>
                  <th>Event Name</th>
                  <th>Event Location</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <p>Marvin McKinney</p>
                    <label>mckinney.jr@mail.com</label>
                  </td>
                  <td>Rock Music</td>
                  <td>Miami Beach</td>
                  <td>$155.60</td>
                </tr>
                <tr>
                  <td>
                    <p>Ronald Richards</p>
                    <label>ronalrcs@mail.com</label>
                  </td>
                  <td>Rock Music</td>
                  <td>Miami Beach</td>
                  <td>$155.60</td>
                </tr>
                <tr>
                  <td>
                    <p>Darrell Steward</p>
                    <label>steward.darelll@mail.com</label>
                  </td>
                  <td>Rock Music</td>
                  <td>Miami Beach</td>
                  <td>$155.60</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="event_traffic">
            <!-- <h3>Overall Event</h3> -->
            <canvas id="myDonutChart"></canvas>
          </div>
        </div>
      </div>
    </main>
  </div>
  <script src="./js/script.js"></script>
  <script>
    // Show the Coming Soon section when needed
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('coming-soon').style.display = 'block'; // Show the Coming Soon section
    });
  </script>
  <script>
    var options = {
      series: [{
        data: [21, 22, 10, 28, 16, 21]
      }],
      chart: {
        height: 420,
        type: 'bar',
        toolbar: {
          show: false // Disable the toolbar
        },
        events: {
          click: function(chart, w, e) {
            // Handle click events if needed
          }
        }
      },
      colors: ['#0000FF'], // Blue color for bars
      plotOptions: {
        bar: {
          columnWidth: '45%',
          distributed: true,
        }
      },
      dataLabels: {
        enabled: false,
      },
      legend: {
        show: false
      },
      xaxis: {
        categories: [
          ['Jan'],
          ['Feb'],
          ['Mar'],
          ['Apr'],
          ['May'],
          ['Jun'],
        ],
        labels: {
          style: {
            colors: '#20c6aa', // Set to blue or any other color you want
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#20c6aa', // Set to blue or any other color you want
            fontSize: '12px'
          }
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
  </script>
  <script>
    // Pass PHP data to JavaScript
    var labels = <?php echo json_encode($labelsDount); ?>;
    var data = <?php echo json_encode($dataDount); ?>;
    var backgroundColor = <?php echo json_encode($backgroundColordonut); ?>;

    // Chart.js Donut chart configuration
    var ctx = document.getElementById('myDonutChart').getContext('2d');
    var myDonutChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          label: 'datas',
          data: data,
          backgroundColor: backgroundColor,
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true, // Show the legend
            position: 'bottom' // Position the legend at the bottom
          }
        }
      }
    });
  </script>
</body>

</html>