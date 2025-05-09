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

// Fetch reservation data before updating
$selectStmt = $conn->prepare("SELECT idno, full_name, lab_name, purpose, reservation_date, time_slot FROM reservations WHERE reservation_id = ?");
$selectStmt->bind_param("i", $reservation_id);
$selectStmt->execute();
$selectResult = $selectStmt->get_result();
$reservationData = $selectResult->fetch_assoc();
$selectStmt->close();

$success = true;
$message = 'Reservation status updated successfully';

if ($reservationData && $status == 'approved') {
    $idno = $reservationData['idno'];
    $full_name = $reservationData['full_name'];
    $lab = $reservationData['lab_name'];
    $reason = $reservationData['purpose'];
    $sit_date = $reservationData['reservation_date'];
    $time_slot = $reservationData['time_slot'];
    $in_time = date('Y-m-d H:i:s'); // Current timestamp

    // Insert into sit_in table
    $insertStmt = $conn->prepare("INSERT INTO sit_in (idno, full_name, lab, reason, in_time, sit_date, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $insertStmt->bind_param("ssssss", $idno, $full_name, $lab, $reason, $in_time, $sit_date);
    if(!$insertStmt->execute()){
        $success = false;
        $message = 'Error inserting into sit_in table';
    }else{
        // Update in_time with correct time
        $in_time_stmt = $conn->prepare("UPDATE sit_in SET in_time = ? WHERE idno = ? AND sit_date = ?");
        $in_time_stmt->bind_param("sss",$time_slot, $idno, $sit_date);
        $in_time_stmt->execute();
        $in_time_stmt->close();
    }
    $insertStmt->close();
}
else {
    if($success){
        $updateStmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $updateStmt->bind_param("si", $status, $reservation_id);
        $updateStmt->execute();
        $updateStmt->close();
    }
}

if(!$reservationData){
    $success = false;
    $message = "Error updating reservation status or reservation not found";
}

echo json_encode(['success' => $success, 'message' => $message]);
$conn->close();
?>
?>