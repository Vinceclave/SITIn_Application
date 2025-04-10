<?php
require_once '../config/config.php';

header('Content-Type: application/json'); // Ensure the response is JSON

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idno'])) {
    $idno = $_POST['idno'];

    // Fetch current inTime and outTime for the active session
    $query = "SELECT in_time, out_time FROM sit_in WHERE idno = ? AND status = 1 ORDER BY sit_in_id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    $sit_in_record = $result->fetch_assoc();

    if ($sit_in_record) {
        $inTime = $sit_in_record['in_time'];
        $outTime = $sit_in_record['out_time'];

        if ($inTime && !$outTime) {
            // End active sit-in: set out_time and status from 1 to 0
            $query = "UPDATE sit_in SET out_time = NOW(), status = 0 WHERE idno = ? AND out_time IS NULL";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $idno);
            if ($stmt->execute()) {
                // Deduct one session from student_session table
                $query2 = "UPDATE student_session SET session = session - 1 WHERE idno = ?";
                $stmt2 = $conn->prepare($query2);
                $stmt2->bind_param("s", $idno);
                $stmt2->execute();
                echo json_encode(["success" => true, "message" => "Sit-in ended successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error updating out_time."]);
            }
        } elseif (!$inTime && !$outTime) {
            // Start and end sit-in simultaneously: set both times and status from 1 to 0
            $query = "UPDATE sit_in SET in_time = NOW(), out_time = NOW(), status = 0 WHERE idno = ? AND in_time IS NULL AND out_time IS NULL";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $idno);
            if ($stmt->execute()) {
                // Deduct one session from student_session table
                $query2 = "UPDATE student_session SET session = session - 1 WHERE idno = ?";
                $stmt2 = $conn->prepare($query2);
                $stmt2->bind_param("s", $idno);
                $stmt2->execute();
                echo json_encode(["success" => true, "message" => "Sit-in started and ended at the same time."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error setting in_time and out_time."]);
            }
        } else {
            // Handle cases where the sit-in has already ended or is in an invalid state.
            echo json_encode(["success" => false, "message" => "Sit-in already ended or invalid state."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Sit-in record not found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
