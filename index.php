<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal - Home</title>
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
            z-index: 1000;
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
            font-size: 16px;
        }
        nav ul li a:hover {
            color: #3498db;
        }
        .hero {
            background: url('https://via.placeholder.com/1200x400') no-repeat center/cover;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-top: 60px;
        }
        .hero h1 {
            font-size: 48px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .search-bar {
            max-width: 600px;
            margin: 20px auto;
            display: flex;
            gap: 10px;
        }
        .search-bar input {
            padding: 10px;
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-bar button {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background: #2980b9;
        }
        .featured-jobs, .categories {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .featured-jobs h2, .categories h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .job-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .job-card h3 {
            font-size: 20px;
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
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .category-card {
            background: #3498db;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
        }
        .category-card:hover {
            background: #2980b9;
        }
        footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 32px;
            }
            .search-bar {
                flex-direction: column;
            }
            nav ul {
                flex-direction: column;
                gap: 10px;
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
                <li><a href="#" onclick="redirect('signup.php')">Signup</a></li>
            </ul>
        </nav>
    </header>
    <section class="hero">
        <h1>Find Your Dream Job Today</h1>
    </section>
    <div class="search-bar">
        <input type="text" placeholder="Job title or keywords">
        <input type="text" placeholder="Location">
        <button onclick="redirect('job_search.php')">Search Jobs</button>
    </div>
    <section class="featured-jobs">
        <h2>Featured Jobs</h2>
        <?php
        include 'db.php';
        $sql = "SELECT * FROM jobs LIMIT 5";
        $result = $conn->query($sql);
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
        $conn->close();
        ?>
    </section>
    <section class="categories">
        <h2>Trending Categories</h2>
        <div class="category-grid">
            <div class="category-card" onclick="redirect('job_search.php?category=IT')">IT</div>
            <div class="category-card" onclick="redirect('job_search.php?category=Finance')">Finance</div>
            <div class="category-card" onclick="redirect('job_search.php?category=Marketing')">Marketing</div>
            <div class="category-card" onclick="redirect('job_search.php?category=Healthcare')">Healthcare</div>
        </div>
    </section>
    <footer>
        <p>&copy; 2025 Job Portal. All rights reserved.</p>
    </footer>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
