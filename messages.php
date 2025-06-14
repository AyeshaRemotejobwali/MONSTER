<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? '';
$error = '';
$success = '';

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_email = trim($_POST['recipient']);
    $message = trim($_POST['message']);

    // Validate inputs
    if (empty($recipient_email) || empty($message)) {
        $error = "Recipient email and message are required.";
    } elseif (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Fetch recipient ID
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error = "Database error: Unable to prepare query.";
        } else {
            $stmt->bind_param("s", $recipient_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $recipient = $result->fetch_assoc();
                $recipient_id = $recipient['id'];

                // Insert message
                $sql = "INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($sql);
                if (!$insert_stmt) {
                    $error = "Database error: Unable to prepare insert query.";
                } else {
                    $insert_stmt->bind_param("iis", $user_id, $recipient_id, $message);
                    if ($insert_stmt->execute()) {
                        $success = "Message sent successfully!";
                    } else {
                        $error = "Error sending message.";
                    }
                    $insert_stmt->close();
                }
            } else {
                $error = "Recipient email not found.";
            }
            $stmt->close();
        }
    }
}

// Fetch messages
$sql = "SELECT m.*, u.name AS sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.recipient_id = ? 
        ORDER BY m.created_at DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error = "Database error: Unable to prepare message fetch query.";
} else {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Job Portal</title>
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
        .messages-container {
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
        .message-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .message-card {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .message-card:last-child {
            border-bottom: none;
        }
        .message-card p {
            color: #7f8c8d;
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
        .send-btn {
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .send-btn:hover {
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
    <div class="messages-container">
        <div class="sidebar">
            <h2><?php echo htmlspecialchars($user_type == 'employer' ? 'Employer' : 'Job Seeker'); ?> Dashboard</h2>
            <ul>
                <li><a href="#" onclick="redirect('<?php echo htmlspecialchars($user_type == 'employer' ? 'employer_dashboard.php' : 'seeker_dashboard.php'); ?>')"><?php echo htmlspecialchars($user_type == 'employer' ? 'Post Job' : 'Profile'); ?></a></li>
                <li><a href="#" onclick="redirect('messages.php')">Messages</a></li>
                <li><a href="#" onclick="logout()">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <h2>Messages</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="recipient">Recipient Email</label>
                    <input type="email" id="recipient" name="recipient" value="<?php echo isset($_POST['recipient']) ? htmlspecialchars($_POST['recipient']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>
                <button type="submit" class="send-btn">Send Message</button>
                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
            </form>
            <div class="message-list">
                <h3>Your Messages</h3>
                <?php if (isset($messages_result) && $messages_result->num_rows > 0): ?>
                    <?php while ($row = $messages_result->fetch_assoc()): ?>
                        <div class="message-card">
                            <p><strong><?php echo htmlspecialchars($row['sender_name']); ?></strong>: <?php echo htmlspecialchars($row['message']); ?></p>
                            <p><?php echo htmlspecialchars($row['created_at']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No messages found.</p>
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
