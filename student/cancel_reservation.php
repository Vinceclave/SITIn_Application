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

// Begin transaction
$conn->begin_transaction();

try {
    // Get reservation details for PC update
    $getReservationQuery = "SELECT lab_name, pc_number FROM reservations WHERE reservation_id = ?";
    $getReservationStmt = $conn->prepare($getReservationQuery);
    $getReservationStmt->bind_param("i", $reservation_id);
    $getReservationStmt->execute();
    $reservationResult = $getReservationStmt->get_result();
    
    if ($reservationResult->num_rows === 0) {
        throw new Exception("Reservation not found");
    }
    
    $reservation = $reservationResult->fetch_assoc();
    $lab_name = $reservation['lab_name'];
    $pc_number = $reservation['pc_number'];
    
    // Check and prepare the query to verify reservation ownership and status
    $checkStmt = $conn->prepare("SELECT reservation_id FROM reservations WHERE reservation_id = ? AND idno = ? AND status = 'pending'");
    if (!$checkStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $checkStmt->bind_param("ii", $reservation_id, $idno);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows !== 1) {
        throw new Exception("You cannot cancel this reservation. It may not exist, may not belong to you, or is no longer pending.");
    }
    $checkStmt->close();
    
    // Update the reservation status to rejected
    $updateStmt = $conn->prepare("UPDATE reservations SET status = 'rejected' WHERE reservation_id = ?");
    if (!$updateStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $updateStmt->bind_param("i", $reservation_id);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Update PC availability if needed
    // Get lab ID
    $labQuery = "SELECT lab_id FROM labs WHERE lab_name = ?";
    $labStmt = $conn->prepare($labQuery);
    $labStmt->bind_param("s", $lab_name);
    $labStmt->execute();
    $labResult = $labStmt->get_result();
    
    if ($labResult->num_rows > 0) {
        $lab_id = $labResult->fetch_assoc()['lab_id'];
        
        // Check if there are any other pending/approved reservations for this PC
        $otherReservationsQuery = "SELECT COUNT(*) as count FROM reservations 
                                  WHERE lab_name = ? AND pc_number = ? 
                                  AND status IN ('pending', 'approved') 
                                  AND reservation_id != ?";
        $otherReservationsStmt = $conn->prepare($otherReservationsQuery);
        $otherReservationsStmt->bind_param("sii", $lab_name, $pc_number, $reservation_id);
        $otherReservationsStmt->execute();
        $otherReservationsResult = $otherReservationsStmt->get_result();
        $reservationsCount = $otherReservationsResult->fetch_assoc()['count'];
        
        // Check if there are any active sit-in sessions for this PC
        $activeSessionsQuery = "SELECT COUNT(*) as count FROM sit_in 
                               WHERE lab = ? AND pc_number = ? 
                               AND status = 1 AND out_time IS NULL";
        $activeSessionsStmt = $conn->prepare($activeSessionsQuery);
        $activeSessionsStmt->bind_param("si", $lab_name, $pc_number);
        $activeSessionsStmt->execute();
        $activeSessionsResult = $activeSessionsStmt->get_result();
        $sessionsCount = $activeSessionsResult->fetch_assoc()['count'];
        
        // If no other reservations or active sessions, update PC status to available
        if ($reservationsCount == 0 && $sessionsCount == 0) {
            // Check if PC exists in the pcs table
            $checkPcQuery = "SELECT pc_id FROM pcs WHERE lab_id = ? AND pc_number = ?";
            $checkPcStmt = $conn->prepare($checkPcQuery);
            $checkPcStmt->bind_param("ii", $lab_id, $pc_number);
            $checkPcStmt->execute();
            $checkPcResult = $checkPcStmt->get_result();
            
            if ($checkPcResult->num_rows > 0) {
                // Update PC status to available
                $updatePcQuery = "UPDATE pcs SET status = 'available' WHERE lab_id = ? AND pc_number = ?";
                $updatePcStmt = $conn->prepare($updatePcQuery);
                $updatePcStmt->bind_param("ii", $lab_id, $pc_number);
                $updatePcStmt->execute();
            } else {
                // Insert the PC with status available
                $insertPcQuery = "INSERT INTO pcs (lab_id, pc_number, status) VALUES (?, ?, 'available')";
                $insertPcStmt = $conn->prepare($insertPcQuery);
                $insertPcStmt->bind_param("ii", $lab_id, $pc_number);
                $insertPcStmt->execute();
            }
        }
    }
    
    // Commit the transaction
    $conn->commit();
    
    $_SESSION['success'] = "Reservation cancelled successfully.";
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

// Redirect to the reservation page
header("Location: reservation.php");
exit;