<?php
// filepath: d:\Xampp\htdocs\SITIn_Application\admin\fetch_active_sessions.php
require_once '../config/config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Check if lab_id is provided
if (!isset($_GET['lab_id']) || empty($_GET['lab_id'])) {
    echo json_encode(['success' => false, 'message' => 'Lab ID is required']);
    exit;
}

$lab_id = filter_var($_GET['lab_id'], FILTER_VALIDATE_INT);

// Fetch active sessions for the selected lab
$query = "SELECT s.*, p.pc_number, l.lab_name 
          FROM sit_in s 
          JOIN pcs p ON s.pc_id = p.pc_id 
          JOIN labs l ON p.lab_id = l.lab_id 
          WHERE p.lab_id = ? AND s.status = 1 AND s.out_time IS NULL
          ORDER BY p.pc_number";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $lab_id);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    echo json_encode(['success' => true, 'sessions' => $sessions]);
} else {
    echo json_encode(['success' => true, 'sessions' => []]);
}

$stmt->close();
$conn->close();
?>
