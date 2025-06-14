<?php
session_start();
include 'db.php';

// Check if user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'employer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle job posting
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $salary = trim($_POST['salary']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $job_type = trim($_POST['job_type']);
    $company = "Company Name"; // TODO: Fetch from employer profile or form input

    // Validate inputs
    if (empty($title) || empty($description) || empty($requirements) || empty($salary) || empty($location) || empty($category) || empty($job_type)) {
        $error = "All fields are required.";
    } elseif (!in_array($category, ['IT', 'Finance', 'Marketing', 'Healthcare'])) {
        $error = "Invalid category.";
    } elseif (!in_array($job_type, ['full-time', 'part-time', 'remote'])) {
        $error = "Invalid job type.";
    } else {
        // Insert job
        $sql = "INSERT INTO jobs (title, description, requirements, salary, location, category, job_type, company, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Database error: Unable to prepare insert query.";
        } else {
            $stmt->bind_param("ssssssssi", $title, $description, $requirements, $salary, $location, $category, $job_type, $company, $user_id);
            if ($stmt->execute()) {
                $success = "Job posted successfully!";
            } else {
                $error = "Error posting job.";
            }
            $stmt->close();
        }
    }
}

// Fetch posted jobs
$sql = "SELECT * FROM jobs WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "Database error: Unable to prepare job fetch query.";
} else {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $jobs_result = $stmt->get_result();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard - Job Portal</title>
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
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            resize: vertical;
            height: 100px;
        }
        .post-btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .post-btn:hover {
            background: #2980b9;
        }
        .job-list {
            margin-top: 40px;
        }
        .job-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .job-card h3 {
            color: #3498db;
        }
        .job-card p {
            color: #7f8c8d;
            margin: 10px 0;
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
            <h2>Employer Dashboard</h2>
            <ul>
                <li><a href="#" onclick="redirect('employer_dashboard.php')">Post Job</a></li>
                <li><a href="#" onclick="redirect('messages.php')">Messages</a></li>
                <li><a href="#" onclick="logout()">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <h2>Post a New Job</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" required><?php echo isset($_POST['requirements']) ? htmlspecialchars($_POST['requirements']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="salary">Salary</label>
                    <input type="text" id="salary" name="salary" value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="IT" <?php echo isset($_POST['category']) && $_POST['category'] == 'IT' ? 'selected' : ''; ?>>IT</option>
                        <option value="Finance" <?php echo isset($_POST['category']) && $_POST['category'] == 'Finance' ? 'selected' : ''; ?>>Finance</option>
                        <option value="Marketing" <?php echo isset($_POST['category']) && $_POST['category'] == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                        <option value="Healthcare" <?php echo isset($_POST['category']) && $_POST['category'] == 'Healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="job_type">Job Type</label>
                    <select id="job_type" name="job_type" required>
                        <option value="full-time" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'full-time' ? 'selected' : ''; ?>>Full-Time</option>
                        <option value="part-time" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'part-time' ? 'selected' : ''; ?>>Part-Time</option>
                        <option value="remote" <?php echo isset($_POST['job_type']) && $_POST['job_type'] == 'remote' ? 'selected' : ''; ?>>Remote</option>
                    </select>
                </div>
                <button type="submit" class="post-btn">Post Job</button>
                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
            </form>
            <div class="job-list">
                <h2>Your Posted Jobs</h2>
                <?php if (isset($jobs_result) && $jobs_result->num_rows > 0): ?>
                    <?php while ($row = $jobs_result->fetch_assoc()): ?>
                        <div class="job-card">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['company']); ?> | <?php echo htmlspecialchars($row['location']); ?></p>
                            <p>Salary: <?php echo htmlspecialchars($row['salary']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No jobs posted yet.</p>
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
