<?php
$host = 'localhost';
$dbname = 'dbhlt78ki0e5me';
$username = 'uxgukysg8xcbd';
$password = '6imcip8yfmic';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
