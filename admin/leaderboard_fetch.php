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

// Query the sit_in table directly for the leaderboard data
$query = "SELECT 
            idno,
            full_name,
            COUNT(DISTINCT sit_in_id) as total_sessions,
            0 as total_points
          FROM sit_in
          WHERE status = 0 
          GROUP BY idno, full_name
          ORDER BY total_sessions DESC
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