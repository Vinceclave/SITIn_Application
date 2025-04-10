<?php
session_start();
include '../includes/conn.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Read the SQL file
$sql = file_get_contents('sql/create_leaderboard_table.sql');

try {
    // Execute the SQL to create the table
    $conn->exec($sql);
    
    // Populate the leaderboard table with initial data
    $updateQuery = "INSERT INTO leaderboard (idno, full_name, total_sessions, total_points, last_updated)
                    SELECT 
                        s.idno,
                        s.full_name,
                        COUNT(DISTINCT s.sit_in_id) as total_sessions,
                        COALESCE(SUM(lp.points), 0) as total_points,
                        NOW() as last_updated
                    FROM sit_in s
                    LEFT JOIN lab_points lp ON s.sit_in_id = lp.sit_in_id
                    GROUP BY s.idno, s.full_name
                    ON DUPLICATE KEY UPDATE
                        full_name = VALUES(full_name),
                        total_sessions = VALUES(total_sessions),
                        total_points = VALUES(total_points),
                        last_updated = VALUES(last_updated)";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute();
    
    echo "Leaderboard table created and populated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 