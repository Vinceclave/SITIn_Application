php
<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['reservation_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$reservation_id = filter_var($_POST['reservation_id'], FILTER_VALIDATE_INT);
$status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

if ($reservation_id === false || !in_array($status, ['pending', 'approved', 'rejected', 'completed'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
$stmt->bind_param("si", $status, $reservation_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reservation status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating reservation status']);
}

$stmt->close();
$conn->close();
?>