<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Search - Job Portal</title>
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
        header {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        nav .logo {
            font-size: 24px;
            font-weight: bold;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
        }
        nav ul li a:hover {
            color: #3498db;
        }
        .search-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-form input, .search-form select {
            padding: 10px;
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background: #2980b9;
        }
        .job-list {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
        .job-card button {
            padding: 8px 16px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .job-card button:hover {
            background: #2980b9;
        }
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Job Portal</div>
            <ul>
                <li><a href="#" onclick="redirect('index.php')">Home</a></li>
                <li><a href="#" onclick="redirect('job_search.php')">Search Jobs</a></li>
                <li><a href="#" onclick="redirect('login.php')">Login</a></li>
            </ul>
        </nav>
    </header>
    <div class="search-container">
        <form action="" method="GET" class="search-form">
            <input type="text" name="keyword" placeholder="Job title or keywords">
            <input type="text" name="location" placeholder="Location">
            <select name="category">
                <option value="">All Categories</option>
                <option value="IT">IT</option>
                <option value="Finance">Finance</option>
                <option value="Marketing">Marketing</option>
                <option value="Healthcare">Healthcare</option>
            </select>
            <select name="job_type">
                <option value="">All Job Types</option>
                <option value="full-time">Full-Time</option>
                <option value="part-time">Part-Time</option>
                <option value="remote">Remote</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="job-list">
        <h2>Job Listings</h2>
        <?php
        include 'db.php';
        $sql = "SELECT * FROM jobs WHERE 1=1";
        $params = [];
        $types = '';
        if (!empty($_GET['keyword'])) {
            $sql .= " AND title LIKE ?";
            $params[] = '%' . $_GET['keyword'] . '%';
            $types .= 's';
        }
        if (!empty($_GET['location'])) {
            $sql .= " AND location LIKE ?";
            $params[] = '%' . $_GET['location'] . '%';
            $types .= 's';
        }
        if (!empty($_GET['category'])) {
            $sql .= " AND category = ?";
            $params[] = $_GET['category'];
            $types .= 's';
        }
        if (!empty($_GET['job_type'])) {
            $sql .= " AND job_type = ?";
            $params[] = $_GET['job_type'];
            $types .= 's';
        }
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="job-card">';
                echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                echo '<p>' . htmlspecialchars($row['company']) . ' | ' . htmlspecialchars($row['location']) . '</p>';
                echo '<p>Salary: ' . htmlspecialchars($row['salary']) . '</p>';
                echo '<button onclick="redirect(\'job_apply.php?id=' . $row['id'] . '\')">Apply Now</button>';
                echo '</div>';
            }
        } else {
            echo '<p>No jobs found.</p>';
        }
        $stmt->close();
        $conn->close();
        ?>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
