<?php
require_once '../config/config.php';

// Set content type to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $sit_in_id = isset($_POST['sit_in_id']) ? intval($_POST['sit_in_id']) : 0;
    $idno = isset($_POST['idno']) ? $_POST['idno'] : '';
    $points = isset($_POST['points']) ? intval($_POST['points']) : 0;
    
    // Validate inputs
    if ($sit_in_id <= 0 || empty($idno) || $points <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid parameters. Please provide sit_in_id, idno, and points.'
        ]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if sit_in_id exists and is completed (status = 0)
        $check_query = "SELECT idno, points_given FROM sit_in WHERE sit_in_id = ? AND status = 0";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $sit_in_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows == 0) {
            throw new Exception("Invalid sit-in record or the session is still active.");
        }
        
        // Check if points already given for this sit-in
        $sit_in_data = $check_result->fetch_assoc();
        if ($sit_in_data['points_given'] > 0) {
            throw new Exception("Points have already been awarded for this session.");
        }
        
        // Insert into lab_points
        $insert_query = "INSERT INTO lab_points (sit_in_id, points, assigned_at) VALUES (?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ii", $sit_in_id, $points);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to save points: " . $insert_stmt->error);
        }
        
        // Update the sit_in record with points_given
        $update_sit_in_query = "UPDATE sit_in SET points_given = ? WHERE sit_in_id = ?";
        $update_sit_in_stmt = $conn->prepare($update_sit_in_query);
        $update_sit_in_stmt->bind_param("ii", $points, $sit_in_id);
        
        if (!$update_sit_in_stmt->execute()) {
            throw new Exception("Failed to update sit-in record: " . $update_sit_in_stmt->error);
        }
        
        // Get total points from sit_in records
        $total_points_query = "SELECT SUM(points_given) as total_points FROM sit_in WHERE idno = ? AND status = 0";
        $total_points_stmt = $conn->prepare($total_points_query);
        $total_points_stmt->bind_param("s", $idno);
        $total_points_stmt->execute();
        $total_points_result = $total_points_stmt->get_result();
        $total_points_row = $total_points_result->fetch_assoc();
        $total_points = $total_points_row['total_points'];
        
        // Check if total points is divisible by 3
        $sessions_to_add = 0;
        if ($total_points >= 3) {
            $sessions_to_add = floor($total_points / 3);
            $remaining_points = $total_points % 3;
            
            // Reset points in sit_in records
            if ($sessions_to_add > 0) {
                // Update student_session to add sessions
                $update_sessions_query = "UPDATE student_session SET session = session + ? WHERE idno = ?";
                $update_sessions_stmt = $conn->prepare($update_sessions_query);
                $update_sessions_stmt->bind_param("is", $sessions_to_add, $idno);
                
                if (!$update_sessions_stmt->execute()) {
                    throw new Exception("Failed to update sessions: " . $update_sessions_stmt->error);
                }
                
                // Reset points in sit_in records
                $reset_points_query = "UPDATE sit_in SET points_given = 0 WHERE idno = ? AND status = 0";
                $reset_points_stmt = $conn->prepare($reset_points_query);
                $reset_points_stmt->bind_param("s", $idno);
                
                if (!$reset_points_stmt->execute()) {
                    throw new Exception("Failed to reset points: " . $reset_points_stmt->error);
                }
                
                // Update the current sit-in record with the remaining points
                if ($remaining_points > 0) {
                    $update_remainder_query = "UPDATE sit_in SET points_given = ? WHERE sit_in_id = ?";
                    $update_remainder_stmt = $conn->prepare($update_remainder_query);
                    $update_remainder_stmt->bind_param("ii", $remaining_points, $sit_in_id);
                    
                    if (!$update_remainder_stmt->execute()) {
                        throw new Exception("Failed to update remaining points: " . $update_remainder_stmt->error);
                    }
                }
            }
        }
        
        // Get updated session count
        $session_query = "SELECT session FROM student_session WHERE idno = ?";
        $session_stmt = $conn->prepare($session_query);
        $session_stmt->bind_param("s", $idno);
        $session_stmt->execute();
        $session_result = $session_stmt->get_result();
        $current_sessions = ($session_result->num_rows > 0) ? $session_result->fetch_assoc()['session'] : 0;
        
        // Commit transaction
        $conn->commit();
        
        // Return success response with additional info if sessions were added
        $response = [
            'success' => true,
            'message' => 'Points awarded successfully!',
            'points' => $points,
            'total_points' => ($sessions_to_add > 0) ? $remaining_points : $total_points
        ];
        
        // Add sessions_added to response if applicable
        if ($sessions_to_add > 0) {
            $response['sessions_added'] = $sessions_to_add;
            $response['current_sessions'] = $current_sessions;
            $response['message'] = "Points awarded successfully! " . ($sessions_to_add * 3) . " points converted into " . $sessions_to_add . " session(s).";
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
} else {
    // Handle the case where the script is accessed without a POST request
    echo json_encode([
        'success' => false,
        'message' => "Invalid request method."
    ]);
}
?>
