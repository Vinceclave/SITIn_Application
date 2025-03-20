<?php
session_start();
require_once '../config/config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to submit feedback.";
    header("Location: ../login.php");
    exit;
}

// Ensure the database connection exists
if (!isset($conn)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }
}

// Process the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = isset($_POST['idno']) ? trim($_POST['idno']) : '';
    $lab = isset($_POST['lab']) ? trim($_POST['lab']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate input
    if (empty($idno) || empty($lab) || empty($message)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: history.php");
        exit;
    }

    // Insert feedback into the database
    $query = "INSERT INTO feedback (idno, lab, date, message) VALUES (?, ?, NOW(), ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: history.php");
        exit;
    }
    $stmt->bind_param("sss", $idno, $lab, $message);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Feedback submitted successfully.";
    } else {
        $_SESSION['error'] = "Failed to submit feedback. Please try again.";
    }

    $stmt->close();
    header("Location: history.php");
    exit;
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: history.php");
    exit;
}
?>
