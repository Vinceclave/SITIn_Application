<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(["success" => false, "message" => "Unauthorized."]);
    exit();
}

require_once '../config/config.php';

// Reset all student sessions to 30 (adjust the default value if needed)
$query = "UPDATE student_session SET session = 30";
if ($conn->query($query)) {
    echo json_encode(["success" => true, "message" => "Sessions have been reset successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Error resetting sessions."]);
}
?>