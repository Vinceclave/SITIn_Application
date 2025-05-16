<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    // New branch to end an active session without updating sit_date
    if (isset($_POST['action']) && $_POST['action'] === 'end') {
        $idno = isset($_POST['idno']) ? $_POST['idno'] : '';
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // 1. Check if student has an active session
            $checkQuery = "SELECT sit_in_id, pc_id FROM sit_in WHERE idno = ? AND out_time IS NULL AND status = 1";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param("s", $idno);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'There is no active session to end.'
                ]);
                exit;
            }
            
            $sessionData = $result->fetch_assoc();
            $pcId = $sessionData['pc_id'];
            
            // 2. Update the sit_in record to end the session
            $updateQuery = "UPDATE sit_in SET out_time = NOW(), status = 0 WHERE idno = ? AND out_time IS NULL";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("s", $idno);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                // 3. Deduct one session from student_session table
                $deductQuery = "UPDATE student_session SET session = session - 1 WHERE idno = ? AND session > 0";
                $stmt = $conn->prepare($deductQuery);
                $stmt->bind_param("s", $idno);
                $stmt->execute();
                  // 4. If there's a PC ID, update its status to available
                if ($pcId) {
                    // Check if the last_used column exists
                    $columnCheck = $conn->query("SHOW COLUMNS FROM `pcs` LIKE 'last_used'");
                    $lastUsedExists = $columnCheck->num_rows > 0;
                    
                    if ($lastUsedExists) {
                        $updatePcQuery = "UPDATE pcs SET status = 'available', last_used = NOW() WHERE pc_id = ?";
                    } else {
                        $updatePcQuery = "UPDATE pcs SET status = 'available' WHERE pc_id = ?";
                    }
                    
                    $pcStmt = $conn->prepare($updatePcQuery);
                    $pcStmt->bind_param("i", $pcId);
                    $pcStmt->execute();
                }
                
                // 5. Get the updated remaining sessions count
                $sessionQuery = "SELECT session FROM student_session WHERE idno = ?";
                $stmt = $conn->prepare($sessionQuery);
                $stmt->bind_param("s", $idno);
                $stmt->execute();
                $result = $stmt->get_result();
                $remainingSessions = $result->num_rows > 0 ? $result->fetch_assoc()['session'] : 0;
                
                // Commit the transaction
                $conn->commit();
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Session ended successfully.',
                    'remaining_sessions' => $remainingSessions
                ]);
            } else {
                throw new Exception('Failed to update session status');
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to end the session: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    $idno = isset($_POST['idno']) ? $_POST['idno'] : '';
    $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';
    $lab = isset($_POST['lab']) ? $_POST['lab'] : '';
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

    // Check if the student has any pending session (out_time is NULL)
    $checkQuery = "SELECT * FROM sit_in WHERE idno = ? AND out_time IS NULL";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Student already has a pending sit-in session
        echo json_encode([
            'status' => 'error',
            'message' => 'This student already has a pending sit-in session.'
        ]);    } else {
        // Get PC details if provided
        $pcNumber = isset($_POST['pc_number']) ? $_POST['pc_number'] : null;
        
        // Start a transaction
        $conn->begin_transaction();
        
        try {
            // Insert new sit-in record with sit_date
            $insertQuery = "INSERT INTO sit_in (idno, full_name, lab, pc_number, reason, purpose, in_time, out_time, status, sit_date) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, 1, CURDATE())";
            $purpose = isset($_POST['purpose']) ? $_POST['purpose'] : $reason;
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ssssss", $idno, $full_name, $lab, $pcNumber, $reason, $purpose);
            $stmt->execute();
            
            $sitInId = $conn->insert_id;
            
            // If PC number is provided, update the PC status
            if ($pcNumber) {
                // Find the PC in the specified lab
                $findPcQuery = "SELECT p.pc_id FROM pcs p 
                                JOIN labs l ON p.lab_id = l.lab_id 
                                WHERE l.lab_name = ? AND p.pc_number = ?";
                $pcStmt = $conn->prepare($findPcQuery);
                $pcStmt->bind_param("ss", $lab, $pcNumber);
                $pcStmt->execute();
                $pcResult = $pcStmt->get_result();
                  if ($pcResult->num_rows > 0) {
                    $pcData = $pcResult->fetch_assoc();
                    $pcId = $pcData['pc_id'];
                    
                    // Instead of updating sit_in directly, use the stored procedure if available
                    try {
                        // Check if the stored procedure exists
                        $checkProcedure = "SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'update_sitin_pc_id'";
                        $procedureResult = $conn->query($checkProcedure);
                        
                        if ($procedureResult && $procedureResult->num_rows > 0) {
                            // Use the stored procedure
                            $callProc = "CALL update_sitin_pc_id(?, ?, ?)";
                            $procStmt = $conn->prepare($callProc);
                            $procStmt->bind_param("iis", $sitInId, $pcNumber, $lab);
                            $procStmt->execute();
                        } else {
                            // Fallback to direct update
                            $updateSitInQuery = "UPDATE sit_in SET pc_id = ? WHERE sit_in_id = ?";
                            $updateStmt = $conn->prepare($updateSitInQuery);
                            $updateStmt->bind_param("ii", $pcId, $sitInId);
                            $updateStmt->execute();
                        }
                    } catch (Exception $e) {
                        // Fallback to direct update if procedure call fails
                        $updateSitInQuery = "UPDATE sit_in SET pc_id = ? WHERE sit_in_id = ?";
                        $updateStmt = $conn->prepare($updateSitInQuery);
                        $updateStmt->bind_param("ii", $pcId, $sitInId);
                        $updateStmt->execute();
                    }
                      // Update the PC status to unavailable
                    // Check if the last_used column exists
                    $columnCheck = $conn->query("SHOW COLUMNS FROM `pcs` LIKE 'last_used'");
                    $lastUsedExists = $columnCheck->num_rows > 0;
                    
                    if ($lastUsedExists) {
                        $updatePcQuery = "UPDATE pcs SET status = 'unavailable', last_used = NOW() WHERE pc_id = ?";
                    } else {
                        $updatePcQuery = "UPDATE pcs SET status = 'unavailable' WHERE pc_id = ?";
                    }
                      $updatePcStmt = $conn->prepare($updatePcQuery);
                    $updatePcStmt->bind_param("i", $pcId);
                    
                    if (!$updatePcStmt->execute()) {
                        // If it fails with "unknown column", try without last_used
                        if ($conn->errno == 1054) { // 1054 is the MySQL error for "Unknown column"
                            $updatePcQuery = "UPDATE pcs SET status = 'unavailable' WHERE pc_id = ?";
                            $updatePcStmt = $conn->prepare($updatePcQuery);
                            $updatePcStmt->bind_param("i", $pcId);
                            $updatePcStmt->execute();
                        }
                    }
                }
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Get updated remaining sessions for the student
            $remainingSessionsQuery = "SELECT session FROM student_session WHERE idno = ?";
            $stmt = $conn->prepare($remainingSessionsQuery);
            $stmt->bind_param("s", $idno);
            $stmt->execute();
            $result = $stmt->get_result();
            $remainingSessions = $result->num_rows > 0 ? $result->fetch_assoc()['session'] : 0;

            echo json_encode([
                'status' => 'success',
                'message' => 'Sitting session recorded successfully.',
                'remaining_sessions' => $remainingSessions,
                'sit_in_id' => $sitInId
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>
