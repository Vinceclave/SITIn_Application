<?php
session_start();
require_once '../config/config.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Validate if reservation ID is set and is a positive integer
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || intval($_GET['id']) <= 0) {
    $_SESSION['error'] = "Invalid request. Missing or incorrect reservation ID.";
    header("Location: reservation.php");
    exit;
}

// Sanitize the reservation ID
$reservation_id = intval($_GET['id']);

// Fetch user details securely
if (!$stmt = $conn->prepare("SELECT idno FROM users WHERE id = ?")) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: reservation.php");
    exit;
}

$user_id = $_SESSION['user_id'];
if (!$stmt->bind_param("i", $user_id)) {
    $_SESSION['error'] = "Binding parameters failed: " . $stmt->error;
    header("Location: reservation.php");
    exit;
}

if (!$stmt->execute()) {
    $_SESSION['error'] = "Execute failed: " . $stmt->error;
    header("Location: reservation.php");
    exit;
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !isset($user['idno'])) {
    $_SESSION['error'] = "User ID not found.";
    header("Location: reservation.php");
    exit;
}

$idno = $user['idno'];

// Check and prepare the query to verify reservation ownership and status
if (!$checkStmt = $conn->prepare("SELECT reservation_id FROM reservations WHERE reservation_id = ? AND idno = ? AND status = 'pending'")) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: reservation.php");
    exit;
}

if (!$checkStmt->bind_param("ii", $reservation_id, $idno)) {
    $_SESSION['error'] = "Binding parameters failed: " . $checkStmt->error;
    header("Location: reservation.php");
    exit;
}

if (!$checkStmt->execute()) {
    $_SESSION['error'] = "Execute failed: " . $checkStmt->error;
    header("Location: reservation.php");
    exit;
}

$checkResult = $checkStmt->get_result();
if ($checkResult->num_rows !== 1) {
    $_SESSION['error'] = "You cannot cancel this reservation. It may not exist, may not belong to you, or is no longer pending.";
    header("Location: reservation.php");
    $checkStmt->close();
    exit;
}
$checkStmt->close();
// Prepare and execute the update query to cancel the reservation
if (!$updateStmt = $conn->prepare("UPDATE reservations SET status = 'rejected' WHERE reservation_id = ?")) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: reservation.php");
    exit;
}

if (!$updateStmt->bind_param("i", $reservation_id)) {
    $_SESSION['error'] = "Binding parameters failed: " . $updateStmt->error;
    header("Location: reservation.php");
    exit;
}

if ($updateStmt->execute()) {
    $_SESSION['success'] = "Reservation cancelled successfully.";
} else {
    $_SESSION['error'] = "Failed to cancel reservation: " . $updateStmt->error;
}

$updateStmt->close();

// Close the database connection
$conn->close();

// Redirect to the reservation page
header("Location: reservation.php");
exit;