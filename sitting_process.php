<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // Insert new sit-in record, including in_time (current time) and sit_date (current date)
        $insertQuery = "INSERT INTO sit_in (idno, full_name, lab, reason, in_time, sit_date) VALUES (?, ?, ?, ?, NOW(), CURDATE())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssss", $idno, $full_name, $lab, $reason);

        if ($stmt->execute()) {
            // Successfully inserted the session
            // Get the last inserted session ID
            $sitInId = $stmt->insert_id;

            // Retrieve the sit-in session details, excluding out_time
            $selectQuery = "SELECT sit_in_id, idno, full_name, lab, reason, in_time, sit_date FROM sit_in WHERE sit_in_id = ?";
            $stmt = $conn->prepare($selectQuery);
            $stmt->bind_param("i", $sitInId);
            $stmt->execute();
            $result = $stmt->get_result();
            $sessionDetails = $result->fetch_assoc();

            echo json_encode([
                'status' => 'success',
                'message' => 'Sitting session recorded successfully.',
                'session' => $sessionDetails
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
