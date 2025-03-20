<?php
require_once '../config/config.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentIDNO = isset($_POST['idno']) ? trim($_POST['idno']) : '';

    if (empty($studentIDNO)) {
        echo json_encode(["status" => "error", "message" => "Student ID is required."]);
        exit;
    }

    // Fetch remaining sessions
    $stmt = $conn->prepare("SELECT session FROM student_session WHERE idno = ?");
    $stmt->bind_param("s", $studentIDNO);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Student not found."]);
        exit;
    }

    $student = $result->fetch_assoc();
    echo json_encode(["status" => "success", "remaining_sessions" => (int)$student['session']]);
}
?>
