<?php
// filepath: d:\Xampp\htdocs\SITIn_Application\admin\get_pc_details.php
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

// Check if pc_id is provided
if (!isset($_GET['pc_id']) || empty($_GET['pc_id'])) {
    echo json_encode(['success' => false, 'message' => 'PC ID is required']);
    exit;
}

$pc_id = filter_var($_GET['pc_id'], FILTER_VALIDATE_INT);

// Fetch PC details
$query = "SELECT p.*, l.lab_name 
          FROM pcs p 
          JOIN labs l ON p.lab_id = l.lab_id 
          WHERE p.pc_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $pc_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $pc = $result->fetch_assoc();
    
    // Check if there's an active session on this PC
    $session_query = "SELECT * FROM sit_in WHERE pc_id = ? AND status = 1 AND out_time IS NULL";
    $session_stmt = $conn->prepare($session_query);
    $session_stmt->bind_param("i", $pc_id);
    $session_stmt->execute();
    $session_result = $session_stmt->get_result();
    
    if ($session_result->num_rows > 0) {
        $pc['current_user'] = $session_result->fetch_assoc();
    }
    
    echo json_encode(['success' => true, 'pc' => $pc]);
} else {
    echo json_encode(['success' => false, 'message' => 'PC not found']);
}

$stmt->close();
$conn->close();
?>
