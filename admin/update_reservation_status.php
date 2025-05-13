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
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID or status']);
    exit;
}

// Fetch reservation data before updating
$selectStmt = $conn->prepare("SELECT idno, full_name, lab_name, purpose, reservation_date, time_slot FROM reservations WHERE reservation_id = ?");
$selectStmt->bind_param("i", $reservation_id);
$selectStmt->execute();
$selectResult = $selectStmt->get_result();
$reservationData = $selectResult->fetch_assoc();
if (!$reservationData) {
 echo json_encode(['success' => false, 'message' => 'Reservation not found']);
 exit;
}
$selectStmt->close();

$success = true;
$message = 'Reservation status updated successfully';

if ($reservationData) {
    if ($status == 'approved') {
        $idno = $reservationData['idno'];
        $full_name = $reservationData['full_name'];
        $lab = $reservationData['lab_name'];
        $reason = $reservationData['purpose'];
        $sit_date = $reservationData['reservation_date'];
        $time_slot = $reservationData['time_slot'];
        $in_time = date('Y-m-d H:i:s'); // Current timestamp for initial insertion
        $sit_in_status = 'sitting'; // Initial status for sit_in table

 // Update the reservation status to approved
        $updateStmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $updateStmt->bind_param("si", $status, $reservation_id);
 if (!$updateStmt->execute()) {
            $success = false;
 $message = 'Error updating reservation status: ' . $conn->error;
        }
 $updateStmt->close();

        // Insert into sit_in table
 if ($success) { // Only insert into sit_in if reservation status update was successful
            $insertStmt = $conn->prepare("INSERT INTO sit_in (idno, full_name, lab, reason, in_time, sit_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
 $insertStmt->bind_param("sssssss", $idno, $full_name, $lab, $reason, $in_time, $sit_date, $sit_in_status);
 if (!$insertStmt->execute()) {
                $success = false;
 $message = 'Error inserting into sit_in table: ' . $conn->error;
            }
 $insertStmt->close();
        }
    } else { // For other statuses like rejected or completed
        $updateStmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $updateStmt->bind_param("si", $status, $reservation_id);
 if (!$updateStmt->execute()) {
            $success = false;
 $message = 'Error updating reservation status: ' . $conn->error;
        }
 $updateStmt->close();
    }
}

if (!$reservationData) {
    $success = false;
    $message = "Error updating reservation status or reservation not found";
}

echo json_encode(['success' => $success, 'message' => $message]);
$conn->close();