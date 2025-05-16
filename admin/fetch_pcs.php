<?php
// filepath: d:\Xampp\htdocs\SITIn_Application\admin\fetch_pcs.php
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

// Fetch PCs for the selected lab
$query = "SELECT p.*, l.lab_name 
          FROM pcs p 
          JOIN labs l ON p.lab_id = l.lab_id 
          WHERE p.lab_id = ?
          ORDER BY p.pc_number";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $lab_id);
$stmt->execute();
$result = $stmt->get_result();

$pcs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Check if last_used exists in the result set
        if (!isset($row['last_used'])) {
            $row['last_used'] = null; // Add the field with null value if it doesn't exist
        }
        $pcs[] = $row;
    }
    echo json_encode(['success' => true, 'pcs' => $pcs]);
} else {
    echo json_encode(['success' => false, 'message' => 'No PCs found for this lab']);
}

$stmt->close();
$conn->close();
?>
