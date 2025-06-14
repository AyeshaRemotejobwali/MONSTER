<?php
session_start();
include 'db.php';

// Check if user is logged in and is a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'seeker') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $skills = trim($_POST['skills']);
    $experience = trim($_POST['experience']);
    $resume_path = '';

    // Validate inputs
    if (empty($skills) || empty($experience)) {
        $error = "Skills and experience are required.";
    } elseif (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        // Ensure the resumes directory exists
        $upload_dir = 'resumes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Validate file type and size (e.g., PDF only, max 5MB)
        $allowed_types = ['application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        if (!in_array($_FILES['resume']['type'], $allowed_types)) {
            $error = "Only PDF files are allowed.";
        } elseif ($_FILES['resume']['size'] > $max_size) {
            $error = "Resume file size exceeds 5MB.";
        } else {
            $resume_path = $upload_dir . time() . '_' . basename($_FILES['resume']['name']);
            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
                $error = "Failed to upload resume.";
            }
        }
    }

    // Update profile if no errors
    if (!$error) {
        $sql = "UPDATE users SET skills = ?, experience = ?, resume = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $skills, $experience, $resume_path, $user_id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile.";
        }
        $stmt->close();
    }
}

// Fetch user profile
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard - Job Portal</title>
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
        .dashboard-container {
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            resize: vertical;
            height: 100px;
        }
        .upload-btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .upload-btn:hover {
            background: #2980b9;
        }
        .profile-info {
            margin-top: 40px;
        }
        .profile-info h3 {
            color: #3498db;
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
    <div class="dashboard-container">
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
            <h2>Update Profile</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="skills">Skills</label>
                    <textarea id="skills" name="skills"><?php echo isset($user['skills']) ? htmlspecialchars($user['skills']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="experience">Experience</label>
                    <textarea id="experience" name="experience"><?php echo isset($user['experience']) ? htmlspecialchars($user['experience']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="resume">Upload Resume (PDF, max 5MB)</label>
                    <input type="file" id="resume" name="resume" accept=".pdf">
                </div>
                <button type="submit" class="upload-btn">Update Profile</button>
                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
            </form>
            <div class="profile-info">
                <h2>Your Profile</h2>
                <?php if ($user): ?>
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <p>Skills: <?php echo htmlspecialchars($user['skills'] ?: 'Not set'); ?></p>
                    <p>Experience: <?php echo htmlspecialchars($user['experience'] ?: 'Not set'); ?></p>
                    <?php if ($user['resume']): ?>
                        <p><a href="<?php echo htmlspecialchars($user['resume']); ?>" target="_blank">View Resume</a></p>
                    <?php else: ?>
                        <p>No resume uploaded.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="error">Error loading profile.</p>
                <?php endif; ?>
            </div>
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
