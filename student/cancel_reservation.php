<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get the reservation ID from the URL
$reservation_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reservation_id <= 0) {
    $_SESSION['error'] = "Invalid reservation ID.";
    header("Location: reservation.php");
    exit;
}

// Get the user's student ID
$user_id = $_SESSION['user_id'];
$query = "SELECT idno FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$idno = $user['idno'];

// Verify that this reservation belongs to the current user and is still pending
$checkQuery = "SELECT * FROM reservations WHERE reservation_id = ? AND idno = ? AND status = 'pending'";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("ii", $reservation_id, $idno);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    $_SESSION['error'] = "You cannot cancel this reservation. It may not exist, may not belong to you, or is no longer pending.";
    header("Location: reservation.php");
    exit;
}

// Cancel the reservation by updating its status to "rejected"
$updateQuery = "UPDATE reservations SET status = 'rejected' WHERE reservation_id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("i", $reservation_id);

if ($updateStmt->execute()) {
    $_SESSION['success'] = "Reservation successfully cancelled.";
} else {
    $_SESSION['error'] = "Failed to cancel reservation. Please try again.";
}

// Redirect back to the reservation page
header("Location: reservation.php");
exit;
?> 