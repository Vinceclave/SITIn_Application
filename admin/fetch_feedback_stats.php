<?php
require_once '../config/config.php';

// Check if user is logged in as admin
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get total feedback count
$total_query = "SELECT COUNT(*) as total FROM feedback";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_feedback = $total_row['total'];

// Get recent feedback (last 30 days)
$recent_query = "SELECT COUNT(*) as recent FROM feedback WHERE date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$recent_result = $conn->query($recent_query);
$recent_row = $recent_result->fetch_assoc();
$recent_feedback = $recent_row['recent'];

// Get top labs with most feedback
$top_labs_query = "SELECT lab, COUNT(*) as count FROM feedback GROUP BY lab ORDER BY count DESC LIMIT 5";
$top_labs_result = $conn->query($top_labs_query);
$top_labs = [];
while ($row = $top_labs_result->fetch_assoc()) {
    $top_labs[] = [
        'lab' => $row['lab'],
        'count' => $row['count']
    ];
}

// Return the results as JSON
echo json_encode([
    'total' => $total_feedback,
    'recent' => $recent_feedback,
    'top_labs' => $top_labs
]);
?>