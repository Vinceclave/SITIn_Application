<?php
session_start();
require_once '../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// First, update the leaderboard table to ensure it has the latest data
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

if (!$conn->query($updateQuery)) {
    error_log('Error updating leaderboard: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error updating leaderboard: ' . $conn->error]);
    exit;
}

// Now query the leaderboard table for the top students
$query = "SELECT 
            idno,
            full_name,
            total_sessions,
            total_points
          FROM leaderboard
          ORDER BY total_points DESC, total_sessions DESC
          LIMIT 10";

$result = $conn->query($query);

if (!$result) {
    error_log('Error fetching leaderboard data: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching leaderboard data: ' . $conn->error]);
    exit;
}

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Log the results for debugging
error_log('Leaderboard results: ' . print_r($data, true));

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($data);
?> 