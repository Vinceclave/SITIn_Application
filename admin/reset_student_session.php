<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(["success" => false, "message" => "Unauthorized."]);
    exit();
}

require_once '../config/config.php';

// Check if idno is provided
if (!isset($_POST['idno']) || empty($_POST['idno'])) {
    echo json_encode(["success" => false, "message" => "Student ID is required."]);
    exit();
}

$idno = $_POST['idno'];

// First, check if the student exists and get current session value
$check_query = "SELECT session FROM student_session WHERE idno = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("s", $idno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Student not found in the sessions table
    // Create a new entry with default 30 sessions
    $insert_query = "INSERT INTO student_session (idno, session) VALUES (?, 30)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("s", $idno);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true, 
            "message" => "Student sessions created successfully.",
            "old_value" => 0,
            "new_value" => 30
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Failed to create sessions for student."
        ]);
    }
} else {
    // Get the current session value before update
    $old_value = $result->fetch_assoc()['session'];
    
    // Reset the sessions to 30
    $update_query = "UPDATE student_session SET session = 30 WHERE idno = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("s", $idno);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true, 
            "message" => "Student sessions reset successfully.",
            "old_value" => $old_value,
            "new_value" => 30
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Failed to reset sessions for student."
        ]);
    }
}
?> 