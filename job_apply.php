<?php
session_start();
include 'db.php';

// Check if user is logged in and is a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'seeker') {
    header("Location: login.php");
    exit;
}

// Validate job ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: job_search.php");
    exit;
}

$job_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch job details
$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "Database error: Unable to prepare job query.";
} else {
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job_result = $stmt->get_result();
    if ($job_result->num_rows == 0) {
        header("Location: job_search.php");
        exit;
    }
    $job = $job_result->fetch_assoc();
    $stmt->close();
}

// Handle application submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cover_letter = trim($_POST['cover_letter']);
    $resume_path = '';

    // Validate inputs
    if (empty($cover_letter)) {
        $error = "Cover letter is required.";
    } elseif (!isset($_FILES['resume']) || $_FILES['resume']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Resume is required.";
    } elseif ($_FILES['resume']['error'] == 0) {
        // Validate file type and size
        $allowed_types = ['application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (!in_array($_FILES['resume']['type'], $allowed_types)) {
            $error = "Only PDF files are allowed.";
        } elseif ($_FILES['resume']['size'] > $max_size) {
            $error = "Resume file size exceeds 5MB.";
        } else {
            // Ensure uploads directory exists
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $resume_path = $upload_dir . time() . '_' . basename($_FILES['resume']['name']);
            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
                $error = "Failed to upload resume.";
            }
        }
    }

    // Submit application if no errors
    if (!$error) {
        $sql = "INSERT INTO applications (job_id, user_id, cover_letter, resume, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Database error: Unable to prepare application query.";
        } else {
            $stmt->bind_param("iiss", $job_id, $user_id, $cover_letter, $resume_path);
            if ($stmt->execute()) {
                $success = "Application submitted successfully!";
            } else {
                $error = "Error submitting application.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Job - Job Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: #f4f4f9;
        }
        .apply-container {
            display: flex;
            min-height: 100vh;
            margin-top: 60px;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
        }
        .sidebar h2 {
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
        }
        .sidebar ul li a:hover {
            color: #3498db;
        }
        .content {
            margin-left: 250px;
            padding: 40px;
            flex: 1;
        }
        .content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .job-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .job-details h3 {
            color: #3498db;
        }
        .job-details p {
            color: #7f8c8d;
            margin: 10px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group textarea, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            resize: vertical;
            height: 150px;
        }
        .apply-btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .apply-btn:hover {
            background: #2980b9;
        }
        .error, .success {
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="apply-container">
        <div class="sidebar">
            <h2>Job Seeker Dashboard</h2>
            <ul>
                <li><a href="#" onclick="redirect('seeker_dashboard.php')">Profile</a></li>
                <li><a href="#" onclick="redirect('job_search.php')">Search Jobs</a></li>
                <li><a href="#" onclick="redirect('messages.php')">Messages</a></li>
                <li><a href="#" onclick="logout()">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <h2>Apply for <?php echo htmlspecialchars($job['title']); ?></h2>
            <div class="job-details">
                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                <p><?php echo htmlspecialchars($job['company']); ?> | <?php echo htmlspecialchars($job['location']); ?></p>
                <p>Salary: <?php echo htmlspecialchars($job['salary']); ?></p>
                <p>Category: <?php echo htmlspecialchars($job['category']); ?></p>
                <p>Job Type: <?php echo htmlspecialchars($job['job_type']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($job['description']); ?></p>
                <p><strong>Requirements:</strong> <?php echo htmlspecialchars($job['requirements']); ?></p>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="cover_letter">Cover Letter</label>
                    <textarea id="cover_letter" name="cover_letter" required><?php echo isset($_POST['cover_letter']) ? htmlspecialchars($_POST['cover_letter']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="resume">Upload Resume (PDF, max 5MB)</label>
                    <input type="file" id="resume" name="resume" accept=".pdf" required>
                </div>
                <button type="submit" class="apply-btn">Submit Application</button>
                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
        function logout() {
            redirect('login.php');
        }
    </script>
</body>
</html>
