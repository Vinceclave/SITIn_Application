<?php
session_start();
require_once '../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized access");
}

echo "<h1>Leaderboard Debug Information</h1>";

// Check database connection
echo "<h2>Database Connection</h2>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Database connection successful</p>";
}

// Check leaderboard table structure
echo "<h2>Leaderboard Table Structure</h2>";
$result = $conn->query("DESCRIBE leaderboard");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error checking table structure: " . $conn->error . "</p>";
}

// Check leaderboard data
echo "<h2>Leaderboard Data</h2>";
$result = $conn->query("SELECT * FROM leaderboard ORDER BY total_points DESC LIMIT 5");
if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Sessions</th><th>Points</th><th>Last Updated</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['idno']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_sessions']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_updated']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in leaderboard table</p>";
    }
} else {
    echo "<p style='color: red;'>Error fetching leaderboard data: " . $conn->error . "</p>";
}

// Check source data
echo "<h2>Source Data Check</h2>";
$result = $conn->query("
    SELECT 
        s.idno,
        s.full_name,
        COUNT(DISTINCT s.sit_in_id) as total_sessions,
        COALESCE(SUM(lp.points), 0) as total_points
    FROM sit_in s
    LEFT JOIN lab_points lp ON s.sit_in_id = lp.sit_in_id
    GROUP BY s.idno, s.full_name
    ORDER BY total_points DESC
    LIMIT 5
");

if ($result) {
    if ($result->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Sessions</th><th>Points</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['idno']) . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_sessions']) . "</td>";
            echo "<td>" . htmlspecialchars($row['total_points']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No source data found</p>";
    }
} else {
    echo "<p style='color: red;'>Error checking source data: " . $conn->error . "</p>";
}

// Add refresh button
echo "<p><a href='debug.php' style='padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Refresh Debug Info</a></p>";
?> 