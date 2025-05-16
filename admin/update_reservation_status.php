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
$selectStmt = $conn->prepare("SELECT idno, full_name, lab_name, pc_number, purpose, reservation_date, time_slot FROM reservations WHERE reservation_id = ?");
$selectStmt->bind_param("i", $reservation_id);
$selectStmt->execute();
$selectResult = $selectStmt->get_result();
$reservationData = $selectResult->fetch_assoc();
$selectStmt->close();

$success = true;
$message = 'Reservation status updated successfully';

// Begin transaction
$conn->begin_transaction();

try {
    if ($reservationData && $status == 'approved') {
        $idno = $reservationData['idno'];
        $full_name = $reservationData['full_name'];
        $lab = $reservationData['lab_name'];
        $pc_number = $reservationData['pc_number'];
        $reason = $reservationData['purpose'];
        $sit_date = $reservationData['reservation_date'];
        $time_slot = $reservationData['time_slot'];
        $in_time = date('Y-m-d H:i:s'); // Current timestamp

        // Get PC ID
        $pcQuery = "SELECT p.pc_id FROM pcs p JOIN labs l ON p.lab_id = l.lab_id WHERE l.lab_name = ? AND p.pc_number = ?";
        $pcStmt = $conn->prepare($pcQuery);
        $pcStmt->bind_param("si", $lab, $pc_number);
        $pcStmt->execute();
        $pcResult = $pcStmt->get_result();
        $pc_id = null;        if ($pcResult->num_rows > 0) {
            $pcData = $pcResult->fetch_assoc();
            $pc_id = $pcData['pc_id'];
            
            // Try to update with last_used, if it fails, try without it
            $updatePcQuery = "UPDATE pcs SET status = 'unavailable', last_used = NOW() WHERE pc_id = ?";
            $updatePcStmt = $conn->prepare($updatePcQuery);
            $updatePcStmt->bind_param("i", $pc_id);
            
            // Try to execute the query with last_used
            if (!$updatePcStmt->execute()) {
                // If it fails with "unknown column", try without last_used
                if ($conn->errno == 1054) { // 1054 is the MySQL error for "Unknown column"
                    $updatePcQuery = "UPDATE pcs SET status = 'unavailable' WHERE pc_id = ?";
                    $updatePcStmt = $conn->prepare($updatePcQuery);
                    $updatePcStmt->bind_param("i", $pc_id);
                    $updatePcStmt->execute();
                }
            }
        }
        
        // Insert into sit_in table
        $insertStmt = $conn->prepare("INSERT INTO sit_in (idno, full_name, lab, pc_number, pc_id, reason, purpose, in_time, sit_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $insertStmt->bind_param("ssssissss", $idno, $full_name, $lab, $pc_number, $pc_id, $reason, $reason, $in_time, $sit_date);
        if(!$insertStmt->execute()){
            throw new Exception('Error inserting into sit_in table: ' . $conn->error);
        }
        
        // Update in_time with correct time
        $in_time_stmt = $conn->prepare("UPDATE sit_in SET in_time = ? WHERE idno = ? AND sit_date = ?");
        $in_time_stmt->bind_param("sss", $time_slot, $idno, $sit_date);
        $in_time_stmt->execute();
        
        // Decrement student session count
        $deductQuery = "UPDATE student_session SET session = session - 1 WHERE idno = ? AND session > 0";
        $deduct_stmt = $conn->prepare($deductQuery);
        $deduct_stmt->bind_param("s", $idno);
        $deduct_stmt->execute();
        
        // Update reservation status
        $updateStmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $updateStmt->bind_param("si", $status, $reservation_id);
        $updateStmt->execute();
    }
    else {
        // For rejected or completed reservations
        $updateStmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $updateStmt->bind_param("si", $status, $reservation_id);
        $updateStmt->execute();
        
        // If rejecting a reservation, we can free up the PC
        if ($status == 'rejected' && $reservationData) {
            $lab = $reservationData['lab_name'];
            $pc_number = $reservationData['pc_number'];
            
            // Get PC ID
            $pcQuery = "SELECT p.pc_id FROM pcs p JOIN labs l ON p.lab_id = l.lab_id WHERE l.lab_name = ? AND p.pc_number = ?";
            $pcStmt = $conn->prepare($pcQuery);
            $pcStmt->bind_param("si", $lab, $pc_number);
            $pcStmt->execute();
            $pcResult = $pcStmt->get_result();
            
            if ($pcResult->num_rows > 0) {
                $pcData = $pcResult->fetch_assoc();
                $pc_id = $pcData['pc_id'];
                
                // Update PC status back to available
                $updatePcQuery = "UPDATE pcs SET status = 'available' WHERE pc_id = ?";
                $updatePcStmt = $conn->prepare($updatePcQuery);
                $updatePcStmt->bind_param("i", $pc_id);
                $updatePcStmt->execute();
            }
        }
    }

    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $success = false;
    $message = $e->getMessage();
}

if(!$reservationData){
    $success = false;
    $message = "Error updating reservation status or reservation not found";
}

echo json_encode(['success' => $success, 'message' => $message]);
$conn->close();