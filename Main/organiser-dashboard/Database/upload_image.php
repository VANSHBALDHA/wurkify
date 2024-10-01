<?php
session_start(); // Start the session at the beginning of the script

// Database connection setup
$host = 'localhost'; // Your database host
$db = 'wurkify_service'; // Your database name
$user = 'root'; // Your database user
$pass = ''; // Your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "<script>
            alert('Connection failed: " . addslashes($e->getMessage()) . "');
            window.history.back();
          </script>";
    exit();
}

// Retrieve user_id from session
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} else {
    echo "<script>
            alert('User not logged in.');
            window.history.back();
          </script>";
    exit();
}

// Check if a file was uploaded
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
    $fileName = $_FILES['profile_picture']['name'];
    $fileSize = $_FILES['profile_picture']['size'];
    $fileType = $_FILES['profile_picture']['type'];
    $fileNameCmps = explode('.', $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    if (in_array($fileExtension, $allowedExtensions) && $fileSize <= $maxFileSize) {
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../organiser_photos/';
        $dest_path = $uploadFileDir . $newFileName;
        
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        // Check if there's an existing file for this user
        $sql = "SELECT file_path FROM organiser_profile_pictures WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $existingFile = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingFile) {
            // Delete the old file
            $oldFilePath = $existingFile['file_path'];
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Remove the old record from the database
            $sql = "DELETE FROM organiser_profile_pictures WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Insert the new file record into the database
            $sql = "INSERT INTO organiser_profile_pictures (user_id, file_name, file_path) VALUES (:user_id, :file_name, :file_path)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':file_name' => $newFileName,
                ':file_path' => $dest_path
            ]);
            echo "<script>
                    alert('File is successfully uploaded.');
                    window.history.back();
                  </script>";
        } else {
            echo "<script>
                    alert('There was an error moving the uploaded file.');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('Invalid file type or file size exceeds the limit.');
                window.history.back();
              </script>";
    }
} else {
    echo "<script>
            alert('No file was uploaded or there was an upload error.');
            window.history.back();
          </script>";
}
?>
