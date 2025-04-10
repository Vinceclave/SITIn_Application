<?php
session_start();
require_once '../config/config.php';

// Set Content-Type header to JSON and disable any unwanted output.
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$idno    = isset($_POST['idno']) ? $_POST['idno'] : '';
$lab     = isset($_POST['lab']) ? trim($_POST['lab']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Server-side validation.
if ($idno === '' || $lab === '' || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill out all required fields.']);
    exit;
}

// Ensure the database connection exists
if (!isset($conn)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }
}

// Insert the feedback into the database.
$query = "INSERT INTO feedback (idno, lab, date, message) VALUES (?, ?, NOW(),?)";
$stmt  = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}
$stmt->bind_param("sss", $idno, $lab, $message);
$execResult = $stmt->execute();

if ($execResult) {
    echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Feedback submission failed.']);
}

$stmt->close();
$conn->close();
?>
