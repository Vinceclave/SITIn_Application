<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idno']) && isset($_POST['sit_in_id']) && isset($_POST['points'])) {
    $idno = $_POST['idno'];
    $sit_in_id = $_POST['sit_in_id'];
    $points = intval($_POST['points']);
    
    // Validate input
    if (empty($idno) || empty($sit_in_id) || $points < 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Student ID, Sit-in ID, and valid points value are required.'
        ]);
        exit;
    }

    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Check if points column exists in student_session
        $checkColumn = "SHOW COLUMNS FROM student_session LIKE 'points'";
        $columnResult = $conn->query($checkColumn);
        
        if ($columnResult->num_rows == 0) {
            // Points column doesn't exist, create it
            $addColumn = "ALTER TABLE student_session ADD COLUMN points INT NOT NULL DEFAULT 0";
            $conn->query($addColumn);
        }
        
        // Check if points have already been assigned in the lab_points table
        $checkQuery = "SELECT * FROM lab_points WHERE sit_in_id = ? LIMIT 1";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $sit_in_id);
        $stmt->execute();
        $pointsResult = $stmt->get_result();

        if ($pointsResult->num_rows == 0) {
            // Check current session count
            $sessionQuery = "SELECT session FROM student_session WHERE idno = ?";
            $stmt = $conn->prepare($sessionQuery);
            $stmt->bind_param("s", $idno);
            $stmt->execute();
            $sessionResult = $stmt->get_result();
            $sessionRow = $sessionResult->fetch_assoc();
            $currentSession = $sessionRow ? intval($sessionRow['session']) : 0;
            
            // Check if session count is already 30
            if ($currentSession >= 30) {
                $conn->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot give points: Student has already reached the maximum session count of 30.'
                ]);
                exit;
            }
            
            // Add points to student_session (variable points based on selection)
            // Increment session only if points awarded are exactly 3
            $sessionIncrement = ($points === 3) ? 1 : 0;
            $pointsQuery = "UPDATE student_session SET points = COALESCE(points, 0) + ?, session = session + ? WHERE idno = ?";
            $sessionMessage = " Session count incremented by 1.";
            $stmt = $conn->prepare($pointsQuery);
            $stmt->bind_param("iis", $points, $sessionIncrement, $idno);
            
            if ($stmt->execute()) {
                // Record the points in lab_points table
                $recordPointsQuery = "INSERT INTO lab_points (sit_in_id, points, assigned_at) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($recordPointsQuery);
                $stmt->bind_param("ii", $sit_in_id, $points);
                
                if ($stmt->execute()) {
                    $conn->commit();
                    echo json_encode([
                        'success' => true,
                        'message' => $points . ' point(s) added successfully!' . $sessionMessage
                    ]);
                } else {
                    $conn->rollback();
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error recording points: ' . $stmt->error
                    ]);
                }
            } else {
                $conn->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => 'Error adding points to student: ' . $stmt->error
                ]);
            }
        } else {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Points already given for this session.'
            ]);
        }
    } catch (Exception $e) {
        if ($conn->ping()) {
            $conn->rollback();
        }
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request or missing parameters.'
    ]);
}
?> 