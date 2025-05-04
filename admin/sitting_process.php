<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // New branch to end an active session without updating sit_date
    if (isset($_POST['action']) && $_POST['action'] === 'end') {
        $idno = isset($_POST['idno']) ? $_POST['idno'] : '';
        
        // 1. Check if student has an active session
        $checkQuery = "SELECT sit_in_id FROM sit_in WHERE idno = ? AND out_time IS NULL AND status = 1";
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
            
            // 4. Get the updated remaining sessions count
            $sessionQuery = "SELECT session FROM student_session WHERE idno = ?";
            $stmt = $conn->prepare($sessionQuery);
            $stmt->bind_param("s", $idno);
            $stmt->execute();
            $result = $stmt->get_result();
            $remainingSessions = $result->num_rows > 0 ? $result->fetch_assoc()['session'] : 0;
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Session ended successfully.',
                'remaining_sessions' => $remainingSessions
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to end the session. Please try again.'
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
        ]);
    } else {
        // Insert new sit-in record with sit_date
        $insertQuery = "INSERT INTO sit_in (idno, full_name, lab, reason, in_time, out_time, status, sit_date) VALUES (?, ?, ?, ?, NOW(), NULL, 1, CURDATE())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssss", $idno, $full_name, $lab, $reason);
        if ($stmt->execute()) {
            // Successfully inserted
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
                'remaining_sessions' => $remainingSessions
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while recording the sitting session.'
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
